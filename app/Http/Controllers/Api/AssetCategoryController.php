<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use App\Services\AssetCategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AssetCategoryController extends Controller
{
    public function __construct(protected AssetCategoryService $service)
    {
        $this->middleware('permission:view_asset_categories')->only('index', 'show', 'selection');
        $this->middleware('permission:create_asset_categories')->only('store');
        $this->middleware('permission:edit_asset_categories')->only('update');
        $this->middleware('permission:delete_asset_categories')->only('destroy');
    }

    public function index(Request $request)
    {
        $categories = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $request->input('parent_id')
        );

        return ApiResponse::success('Asset categories retrieved successfully.', $categories);
    }

    public function selection(Request $request)
    {
        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $request->input('parent_id'),
            $request->boolean('only_parents', false),
            $request->input('level')
        );

        return ApiResponse::success('Asset categories selection retrieved successfully.', $items);
    }

    public function store(StoreAssetCategoryRequest $request)
    {
        $category = $this->service->create($request->validated());

        return ApiResponse::success('Asset category created successfully.', $category, 201);
    }

    public function show(AssetCategory $assetCategory)
    {
        return ApiResponse::success('Asset category details retrieved successfully.', $assetCategory->load('parent'));
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory)
    {
        $updated = $this->service->update($assetCategory, $request->validated());

        return ApiResponse::success('Asset category updated successfully.', $updated);
    }

    public function destroy(AssetCategory $assetCategory)
    {
        $this->service->delete($assetCategory);

        return ApiResponse::success('Asset category deleted successfully.');
    }
}
