<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\CancelSaleRequest;
use App\Http\Requests\ExportDateRangeRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class SaleController extends Controller
{
    public function __construct(protected SaleService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_SALES->value)->only(['index', 'show', 'stats', 'export']);
        $this->middleware('permission:'.Permission::CREATE_SALES->value)->only(['store']);
        $this->middleware('permission:'.Permission::DELETE_SALES->value)->only(['destroy']);
        $this->middleware('permission:'.Permission::CANCEL_SALES->value)->only(['cancel']);
    }

    public function index(Request $request)
    {
        $sales = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
            $request->input('created_by'),
            $request->input('start_date'),
            $request->input('end_date'),
            filter_var($request->input('last_24_hours', false), FILTER_VALIDATE_BOOLEAN),
        );

        return ApiResponse::success('Sales retrieved successfully.', $sales);
    }

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Sales statistics retrieved successfully.', $stats);
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->service->create($request->validated(), $request->user()?->id);

        return ApiResponse::success('Sale created successfully.', $sale, 201);
    }

    public function show(Sale $sale)
    {
        if ($sale->is_cancelled) {
            return ApiResponse::error('Sale not found.', 404);
        }

        return ApiResponse::success('Sale details retrieved successfully.', $sale->load(['customer', 'creator', 'items.product']));
    }

    public function destroy(Sale $sale)
    {
        $this->service->delete($sale);

        return ApiResponse::success('Sale deleted successfully.');
    }

    public function cancel(CancelSaleRequest $request, Sale $sale)
    {
        $cancelled = $this->service->cancel($sale, $request->validated('cancellation_reason'), $request->user());

        return ApiResponse::success('Sale cancelled successfully.', $cancelled);
    }

    public function export(ExportDateRangeRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $rows = $this->service->getExportData($startDate, $endDate);
        $filename = sprintf('sales_%s_to_%s.csv', $startDate, $endDate);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Tanggal', 'Pelanggan', 'Status Member', 'Produk', 'Qty', 'Harga', 'Harga Modal', 'Profit', 'Subtotal', 'Total Penjualan', 'Metode Pembayaran', 'Petugas', 'Status Transaksi', 'Tanggal Dibatalkan', 'Alasan Dibatalkan']);

            foreach ($rows as $sale) {
                $items = $sale->items;
                $statusTransaksi = $sale->is_cancelled ? 'Dibatalkan' : 'Normal';
                $tanggalDibatalkan = $sale->is_cancelled ? optional($sale->cancelled_at)->format('Y-m-d H:i:s') ?? '-' : '-';
                $alasanDibatalkan = $sale->is_cancelled ? ($sale->cancellation_reason ?? '-') : '-';

                if ($items->isEmpty()) {
                    fputcsv($output, [
                        optional($sale->created_at)->format('Y-m-d H:i:s') ?? '-',
                        $sale->customer?->name ?? '-',
                        $sale->customer?->is_active_member ? 'Ya' : 'Tidak',
                        '-',
                        '-',
                        '-',
                        '-',
                        '-',
                        '-',
                        $sale->total_amount ?? 0,
                        $sale->payment_type?->label() ?? '-',
                        $sale->creator?->name ?? '-',
                        $statusTransaksi,
                        $tanggalDibatalkan,
                        $alasanDibatalkan,
                    ]);

                    continue;
                }

                foreach ($items as $item) {
                    $capitalPrice = $item->capital_price;
                    $profit = $capitalPrice ? ($item->price - $capitalPrice) * $item->quantity : null;
                    fputcsv($output, [
                        optional($sale->created_at)->format('Y-m-d H:i:s') ?? '-',
                        $sale->customer?->name ?? '-',
                        $sale->customer?->is_active_member ? 'Ya' : 'Tidak',
                        $item->product?->name ?? '-',
                        $item->quantity ?? 0,
                        $item->price ?? 0,
                        $capitalPrice ?? '-',
                        $profit ?? '-',
                        $item->subtotal ?? 0,
                        $sale->total_amount ?? 0,
                        $sale->payment_type?->label() ?? '-',
                        $sale->creator?->name ?? '-',
                        $statusTransaksi,
                        $tanggalDibatalkan,
                        $alasanDibatalkan,
                    ]);
                }
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

        $normalSales = $rows->filter(fn ($sale) => is_null($sale->cancelled_at));
        $cancelledSales = $rows->filter(fn ($sale) => ! is_null($sale->cancelled_at));

        $totalRecords = $normalSales->count();
        $totalOmzet = $normalSales->sum('total_amount');

        $totalProfit = $normalSales->loadMissing('items')
            ->flatMap(fn ($sale) => $sale->items)
            ->whereNotNull('capital_price')
            ->sum(fn ($item) => ($item->price - $item->capital_price) * $item->quantity);

        $totalCancelled = $cancelledSales->count();
        $totalCancelledRevenue = $cancelledSales->sum('total_amount');

        $cancelledSales->load('cancelledBy');

        $html = view('pdf.sales', [
            'rows' => $normalSales,
            'cancelled_rows' => $cancelledSales,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_records' => $totalRecords,
            'total_omzet' => $totalOmzet,
            'total_profit' => $totalProfit,
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

        $filename = sprintf('sales_%s_to_%s.pdf', $startDate, $endDate);

        return response()->streamDownload(
            fn () => print ($mpdf->Output('', Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
