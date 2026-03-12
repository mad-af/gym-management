<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StockMovementController extends Controller
{
    public function __construct(protected StockMovementService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_STOCK_MOVEMENTS->value)->only(['index', 'show']);
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
        );

        return ApiResponse::success('Stock movements retrieved successfully.', $movements);
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
}
