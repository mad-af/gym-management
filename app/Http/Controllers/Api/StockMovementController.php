<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\ExportDateRangeRequest;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class StockMovementController extends Controller
{
    public function __construct(protected StockMovementService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_STOCK_MOVEMENTS->value)->only(['index', 'show', 'stats', 'export']);
        $this->middleware('permission:'.Permission::CREATE_STOCK_MOVEMENTS->value)->only(['store']);
        $this->middleware('permission:'.Permission::DELETE_STOCK_MOVEMENTS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $movements = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('product_id'),
            $request->input('type'),
            $request->input('created_by'),
            $request->input('start_date'),
            $request->input('end_date'),
        );

        return ApiResponse::success('Stock movements retrieved successfully.', $movements);
    }

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Stock movement statistics retrieved successfully.', $stats);
    }

    public function store(StoreStockMovementRequest $request)
    {
        $movement = $this->service->create($request->validated(), $request->user()?->id);

        return ApiResponse::success('Stock movement created successfully.', $movement, 201);
    }

    public function show(StockMovement $stockMovement)
    {
        return ApiResponse::success('Stock movement details retrieved successfully.', $stockMovement->load(['product', 'creator']));
    }

    public function destroy(StockMovement $stockMovement)
    {
        $this->service->delete($stockMovement);

        return ApiResponse::success('Stock movement deleted successfully.');
    }

    public function export(ExportDateRangeRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $rows = $this->service->getExportData($startDate, $endDate);
        $filename = sprintf('stock_movements_%s_to_%s.csv', $startDate, $endDate);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Tanggal', 'Produk', 'Jenis', 'Jumlah', 'Petugas']);

            foreach ($rows as $movement) {
                fputcsv($output, [
                    optional($movement->created_at)->format('Y-m-d H:i:s') ?? '-',
                    $movement->product?->name ?? '-',
                    $movement->type ?? '-',
                    $movement->quantity ?? 0,
                    $movement->creator?->name ?? '-',
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

        $totalIn = $rows->where('type', 'in')->sum('quantity');
        $totalOut = $rows->where('type', 'out')->sum('quantity');

        $html = view('pdf.stock_movements', [
            'rows' => $rows,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
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

        $filename = sprintf('stock_movements_%s_to_%s.pdf', $startDate, $endDate);

        return response()->streamDownload(
            fn () => print ($mpdf->Output('', Destination::STRING_RETURN)),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
