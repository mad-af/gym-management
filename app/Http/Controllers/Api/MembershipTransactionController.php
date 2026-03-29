<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\ExportDateRangeRequest;
use App\Http\Requests\StoreMembershipTransactionRequest;
use App\Http\Requests\UpdateMembershipTransactionRequest;
use App\Models\MembershipTransaction;
use App\Services\MembershipTransactionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MembershipTransactionController extends Controller
{
    public function __construct(protected MembershipTransactionService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_MEMBERSHIP_TRANSACTIONS->value)->only(['index', 'show', 'stats', 'export']);
        $this->middleware('permission:'.Permission::CREATE_MEMBERSHIP_TRANSACTIONS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_MEMBERSHIP_TRANSACTIONS->value)->only(['update']);
        $this->middleware('permission:'.Permission::DELETE_MEMBERSHIP_TRANSACTIONS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $transactions = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
            $request->input('status'),
            $request->input('created_by'),
            $request->input('start_date'),
            $request->input('end_date'),
        );

        return ApiResponse::success('Membership transactions retrieved successfully.', $transactions);
    }

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Membership transaction statistics retrieved successfully.', $stats);
    }

    public function store(StoreMembershipTransactionRequest $request)
    {
        $transaction = $this->service->create($request->validated(), $request->user()?->id);

        return ApiResponse::success('Membership transaction created successfully.', $transaction, 201);
    }

    public function show(MembershipTransaction $membershipTransaction)
    {
        return ApiResponse::success('Membership transaction details retrieved successfully.', $membershipTransaction->load(['customer', 'package', 'creator']));
    }

    public function update(UpdateMembershipTransactionRequest $request, MembershipTransaction $membershipTransaction)
    {
        $updated = $this->service->update($membershipTransaction, $request->validated());

        return ApiResponse::success('Membership transaction updated successfully.', $updated);
    }

    public function destroy(MembershipTransaction $membershipTransaction)
    {
        $this->service->delete($membershipTransaction);

        return ApiResponse::success('Membership transaction deleted successfully.');
    }

    public function export(ExportDateRangeRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $rows = $this->service->getExportData($startDate, $endDate);
        $filename = sprintf('membership_transactions_%s_to_%s.csv', $startDate, $endDate);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Tanggal Transaksi', 'Pelanggan', 'Paket', 'Harga', 'Mulai', 'Selesai', 'Status', 'Petugas']);

            foreach ($rows as $transaction) {
                fputcsv($output, [
                    optional($transaction->created_at)->format('Y-m-d H:i:s') ?? '-',
                    $transaction->customer?->name ?? '-',
                    $transaction->package?->name ?? '-',
                    $transaction->price ?? 0,
                    optional($transaction->start_date)->format('Y-m-d') ?? '-',
                    optional($transaction->end_date)->format('Y-m-d') ?? '-',
                    $transaction->status ?? '-',
                    $transaction->creator?->name ?? '-',
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

        $totalRecords = $rows->count();
        $totalPendapatan = $rows->sum('price');

        $html = view('pdf.membership_transactions', [
            'rows' => $rows,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_records' => $totalRecords,
            'total_pendapatan' => $totalPendapatan,
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

        $filename = sprintf('membership_transactions_%s_to_%s.pdf', $startDate, $endDate);

        return response()->streamDownload(
            fn () => print ($mpdf->Output('', Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
