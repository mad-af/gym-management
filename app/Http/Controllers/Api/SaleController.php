<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SaleController extends Controller
{
    public function __construct(protected SaleService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_SALES->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_SALES->value)->only(['store']);
        $this->middleware('permission:'.Permission::DELETE_SALES->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $sales = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('customer_id'),
        );

        return ApiResponse::success('Sales retrieved successfully.', $sales);
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->service->create($request->validated(), $request->user()?->id);

        return ApiResponse::success('Sale created successfully.', $sale, 201);
    }

    public function show(Sale $sale)
    {
        return ApiResponse::success('Sale details retrieved successfully.', $sale->load(['customer', 'creator', 'items.product']));
    }

    public function destroy(Sale $sale)
    {
        $this->service->delete($sale);

        return ApiResponse::success('Sale deleted successfully.');
    }
}
