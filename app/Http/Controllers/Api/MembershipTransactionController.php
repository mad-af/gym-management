<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\CancelMembershipTransactionRequest;
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
        $this->middleware('permission:'.Permission::CANCEL_MEMBERSHIP_TRANSACTIONS->value)->only(['cancel']);
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
            $request->input('expiring_within_days') ? (int) $request->input('expiring_within_days') : null,
            filter_var($request->input('last_24_hours', false), FILTER_VALIDATE_BOOLEAN),
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
        if ($membershipTransaction->is_cancelled) {
            return ApiResponse::error('Membership transaction not found.', 404);
        }

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

    public function cancel(CancelMembershipTransactionRequest $request, MembershipTransaction $membershipTransaction)
    {
        $cancelled = $this->service->cancel(
            $membershipTransaction,
            $request->validated('cancellation_reason'),
            $request->user()
        );

        return ApiResponse::success('Membership transaction cancelled successfully.', $cancelled);
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
            fputcsv($output, ['Tanggal Transaksi', 'Pelanggan', 'Paket', 'Harga', 'Mulai', 'Selesai', 'Status', 'Petugas', 'Status Transaksi', 'Tanggal Dibatalkan', 'Alasan Dibatalkan']);

            foreach ($rows as $transaction) {
                $statusTransaksi = $transaction->is_cancelled ? 'Dibatalkan' : 'Normal';
                $tanggalDibatalkan = $transaction->is_cancelled ? optional($transaction->cancelled_at)->format('Y-m-d H:i:s') ?? '-' : '-';
                $alasanDibatalkan = $transaction->is_cancelled ? ($transaction->cancellation_reason ?? '-') : '-';

                fputcsv($output, [
                    optional($transaction->created_at)->format('Y-m-d H:i:s') ?? '-',
                    $transaction->customer?->name ?? '-',
                    $transaction->package?->name ?? '-',
                    $transaction->price ?? 0,
                    optional($transaction->start_date)->format('Y-m-d') ?? '-',
                    optional($transaction->end_date)->format('Y-m-d') ?? '-',
                    $transaction->status ?? '-',
                    $transaction->creator?->name ?? '-',
                    $statusTransaksi,
                    $tanggalDibatalkan,
                    $alasanDibatalkan,
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

        $normalTransactions = $rows->filter(fn ($t) => is_null($t->cancelled_at));
        $cancelledTransactions = $rows->filter(fn ($t) => ! is_null($t->cancelled_at));

        $totalRecords = $normalTransactions->count();
        $totalPendapatan = $normalTransactions->sum('price');

        $totalCancelled = $cancelledTransactions->count();
        $totalCancelledRevenue = $cancelledTransactions->sum('price');

        $cancelledTransactions->load('cancelledBy');

        $html = view('pdf.membership_transactions', [
            'rows' => $normalTransactions,
            'cancelled_rows' => $cancelledTransactions,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_records' => $totalRecords,
            'total_pendapatan' => $totalPendapatan,
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

        $filename = sprintf('membership_transactions_%s_to_%s.pdf', $startDate, $endDate);

        return response()->streamDownload(
            fn () => print ($mpdf->Output('', Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
