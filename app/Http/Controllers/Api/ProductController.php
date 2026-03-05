<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service)
    {
        $this->middleware(['auth:web']);
    }

    public function index(Request $request)
    {
        $products = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search'),
            $request->input('page', 1),
        );

        return ApiResponse::success('Products retrieved successfully.', $products);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
        );

        return ApiResponse::success('Products selection retrieved successfully.', $items);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if (! array_key_exists('stock', $data) || $data['stock'] === null) {
            unset($data['stock']);
        }

        $product = $this->service->create($data);

        return ApiResponse::success('Product created successfully.', $product, 201);
    }

    public function show(Product $product)
    {
        return ApiResponse::success('Product details retrieved successfully.', $product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $updated = $this->service->update($product, $request->validated());

        return ApiResponse::success('Product updated successfully.', $updated);
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return ApiResponse::success('Product deleted successfully.');
    }
}
