<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\CancelVisitRequest;
use App\Http\Requests\ExportDateRangeRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Models\Visit;
use App\Services\MediaService;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class VisitController extends Controller
{
    public function __construct(protected VisitService $service, protected MediaService $mediaService)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_VISITS->value)->only(['index', 'show', 'stats', 'export']);
        $this->middleware('permission:'.Permission::CREATE_VISITS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_VISITS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_VISITS->value)->only(['destroy']);
        $this->middleware('permission:'.Permission::CANCEL_VISITS->value)->only(['cancel']);
    }

    public function index(Request $request)
    {
        $visits = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
            $request->input('visit_type'),
            $request->input('created_by'),
            $request->input('start_date'),
            $request->input('end_date'),
            filter_var($request->input('last_24_hours', false), FILTER_VALIDATE_BOOLEAN),
            $request->input('payment_type'),
        );

        return ApiResponse::success('Visits retrieved successfully.', $visits);
    }

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Visit statistics retrieved successfully.', $stats);
    }

    public function store(StoreVisitRequest $request)
    {
        $data = $request->validated();

        if (empty($data['checkin_time'])) {
            unset($data['checkin_time']);
        }

        $visit = $this->service->create($data, $request->user()?->id);

        if ($request->hasFile('payment_proof')) {
            $this->mediaService->upload($request->file('payment_proof'), $visit, 'payment_proof');
        }

        return ApiResponse::success('Visit created successfully.', $visit, 201);
    }

    public function show(Visit $visit)
    {
        return ApiResponse::success('Visit details retrieved successfully.', $visit->load(['customer', 'membershipTransaction', 'creator']));
    }

    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        $updated = $this->service->update($visit, $request->validated());

        return ApiResponse::success('Visit updated successfully.', $updated);
    }

    public function destroy(Visit $visit)
    {
        $this->service->delete($visit);

        return ApiResponse::success('Visit deleted successfully.');
    }

    public function cancel(CancelVisitRequest $request, Visit $visit)
    {
        $cancelled = $this->service->cancel($visit, $request->validated('cancellation_reason'), $request->user());

        return ApiResponse::success('Visit cancelled successfully.', $cancelled);
    }

    public function export(ExportDateRangeRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $rows = $this->service->getExportData($startDate, $endDate);
        $normalRows = $rows->filter(fn ($v) => $v->cancelled_at === null);
        $cancelledRows = $rows->filter(fn ($v) => $v->cancelled_at !== null);
        $filename = sprintf('visits_%s_to_%s.csv', $startDate, $endDate);

        return response()->streamDownload(function () use ($normalRows, $cancelledRows): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['Tanggal Check-In', 'Pelanggan', 'Jenis', 'Harga', 'Metode Pembayaran', 'Petugas']);
            foreach ($normalRows as $visit) {
                fputcsv($output, [
                    optional($visit->checkin_time)->format('Y-m-d H:i:s') ?? '-',
                    $visit->customer?->name ?? '-',
                    $visit->visit_type ?? '-',
                    $visit->price ?? 0,
                    $visit->payment_type?->label() ?? '-',
                    $visit->creator?->name ?? '-',
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['Status Transaksi', 'Tanggal Dibatalkan', 'Dibatalkan Oleh', 'Alasan Dibatalkan']);
            foreach ($cancelledRows as $visit) {
                fputcsv($output, [
                    optional($visit->checkin_time)->format('Y-m-d H:i:s') ?? '-',
                    $visit->customer?->name ?? '-',
                    $visit->visit_type ?? '-',
                    $visit->price ?? 0,
                    $visit->payment_type?->label() ?? '-',
                    $visit->creator?->name ?? '-',
                    'DIBATALKAN',
                    optional($visit->cancelled_at)->format('Y-m-d H:i:s') ?? '-',
                    $visit->cancelledBy?->name ?? '-',
                    $visit->cancellation_reason ?? '-',
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function exportPdf(ExportDateRangeRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $rows = $this->service->getExportData($startDate, $endDate);
        $normalRows = $rows->filter(fn ($v) => $v->cancelled_at === null);
        $cancelledRows = $rows->filter(fn ($v) => $v->cancelled_at !== null);

        $totalRecords = $normalRows->count();
        $totalCancelled = $cancelledRows->count();
        $totalKunjungan = $totalRecords;
        $totalRevenue = (float) $normalRows->where('visit_type', 'DAILY')->sum('price');
        $totalCancelledRevenue = (float) $cancelledRows->where('visit_type', 'DAILY')->sum('price');

        $html = view('pdf.visits', [
            'rows' => $normalRows,
            'cancelledRows' => $cancelledRows,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_records' => $totalRecords,
            'total_kunjungan' => $totalKunjungan,
            'total_revenue' => $totalRevenue,
            'total_cancelled' => $totalCancelled,
            'total_cancelled_revenue' => $totalCancelledRevenue,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $filename = sprintf('visits_%s_to_%s.pdf', $startDate, $endDate);

        return response()->streamDownload(
            fn () => print ($mpdf->Output('', Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
