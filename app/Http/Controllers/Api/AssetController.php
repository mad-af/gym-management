<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatusEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Services\AssetService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    public function __construct(protected AssetService $service, protected MediaService $mediaService)
    {
        $this->middleware('permission:view_assets')->only('index', 'show', 'selection', 'stats', 'labelPdf');
        $this->middleware('permission:create_assets')->only('store');
        $this->middleware('permission:edit_assets')->only('update');
        $this->middleware('permission:delete_assets')->only('destroy');
        $this->middleware('permission:activate_assets')->only('activate');
    }

    public function index(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $result = $this->service->getAll(
            $request->input('per_page', 10),
            $request->input('search', ''),
            $request->input('page', 1),
            $currentOpd,
            $request->input('status'),
            $request->input('category_id'),
            $request->input('room_id'),
        );

        return ApiResponse::success('Assets retrieved successfully.', $result);
    }

    public function selection(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $items = $this->service->getSelection(
            $request->input('per_page', 20),
            $request->input('search'),
            $request->input('page', 1),
            $currentOpd,
            $request->boolean('only_disposable', false),
            $request->boolean('only_maintainable', false),
        );

        return ApiResponse::success('Assets selection retrieved successfully', $items);
    }

    public function store(StoreAssetRequest $request)
    {
        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $asset = $this->service->create($validated);

        if ($request->hasFile('photo')) {
            $this->mediaService->upload($request->file('photo'), $asset, 'photo');
        }

        return ApiResponse::success('Asset created successfully.', $asset, 201);
    }

    public function show(Asset $asset)
    {
        return ApiResponse::success('Asset details retrieved successfully.', $asset->load(['category', 'opd', 'room', 'fundingSource', 'creator', 'employee']));
    }

    public function update(UpdateAssetRequest $request, Asset $asset)
    {
        if ($asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah data aset yang sudah dibuang.', 422);
        }

        $validated = $request->validated();
        $currentOpd = $request->session()->get('current_opd_id');

        // Override opd_id with current session OPD for consistency
        if ($currentOpd) {
            $validated['opd_id'] = $currentOpd;
        }

        $updatedAsset = $this->service->update($asset, $validated);

        if ($request->hasFile('photo')) {
            $this->mediaService->upload($request->file('photo'), $updatedAsset, 'photo');
        }

        $updatedAsset->load(['category', 'opd', 'room', 'fundingSource', 'creator']);

        return ApiResponse::success('Asset updated successfully.', $updatedAsset);
    }

    public function updateStatus(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'status' => ['required', new \Illuminate\Validation\Rules\Enum(AssetStatusEnum::class)],
        ]);

        if ($asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah status aset yang sudah dibuang.', 422);
        }

        try {
            $updated = $this->service->updateStatus($asset, $validated['status']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success('Asset status updated successfully.', $updated);
    }

    public function updateCondition(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'condition' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\AssetConditionEnum::class)],
        ]);

        if ($asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah kondisi aset yang sudah dibuang.', 422);
        }

        $updated = $this->service->updateCondition($asset, $validated['condition']);

        return ApiResponse::success('Asset condition updated successfully.', $updated);
    }

    public function destroy(Asset $asset)
    {
        $this->service->delete($asset);

        return ApiResponse::success('Asset deleted successfully.');
    }

    public function activate(Asset $asset)
    {
        $this->service->activate($asset);

        return ApiResponse::success('Asset activated successfully.');
    }

    public function stats(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success('Asset statistics retrieved successfully.', $stats);
    }

    public function labelPdf(Request $request, Asset $asset)
    {
        $paperSize = $request->input('paper_size', 'label');

        return $this->service->generateLabelPdf($asset, $paperSize);
    }

    public function bulkLabelPdf(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
            'paper_size' => 'nullable|string|in:label,a4',
        ]);

        $assetIds = $request->input('asset_ids');
        $paperSize = $request->input('paper_size', 'label');

        return $this->service->generateBulkLabelPdf($assetIds, $paperSize);
    }
}
