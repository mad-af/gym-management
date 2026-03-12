<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\MediaService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service, protected MediaService $mediaService)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_PRODUCTS->value)->only(['index', 'show', 'selection', 'stats']);
        $this->middleware('permission:'.Permission::CREATE_PRODUCTS->value)->only(['store']);
        $this->middleware('permission:'.Permission::EDIT_PRODUCTS->value)->only(['update', 'activate']);
        $this->middleware('permission:'.Permission::DELETE_PRODUCTS->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : null;

        $products = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
            $isActive,
        );

        return ApiResponse::success('Products retrieved successfully.', $products);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $request->has('is_active') ? $request->boolean('is_active') : true,
        );

        return ApiResponse::success('Products selection retrieved successfully.', $items);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if (! array_key_exists('stock', $data) || $data['stock'] === null) {
            unset($data['stock']);
        }

        unset($data['cover']);
        $product = $this->service->create($data);

        if ($request->hasFile('cover')) {
            $this->mediaService->upload($request->file('cover'), $product, 'cover');
            $product->load(['media']);
        }

        return ApiResponse::success('Product created successfully.', $product, 201);
    }

    public function show(Product $product)
    {
        return ApiResponse::success('Product details retrieved successfully.', $product->load(['media']));
    }

    public function stats(Request $request)
    {
        $threshold = (int) $request->input('low_stock_threshold', 5);
        $stats = $this->service->getStats($threshold);

        return ApiResponse::success('Product statistics retrieved successfully.', $stats);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        unset($data['cover']);

        $updated = $this->service->update($product, $data);

        if ($request->hasFile('cover')) {
            $this->mediaService->upload($request->file('cover'), $updated, 'cover');
            $updated->load(['media']);
        }

        return ApiResponse::success('Product updated successfully.', $updated);
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return ApiResponse::success('Product deactivated successfully.');
    }

    public function activate(Product $product)
    {
        $this->service->activate($product);

        return ApiResponse::success('Product activated successfully.');
    }
}
