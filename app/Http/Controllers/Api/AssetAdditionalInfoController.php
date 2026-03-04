<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatusEnum;
use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreAssetAdditionalInfoRequest;
use App\Http\Requests\UpdateAssetAdditionalInfoRequest;
use App\Models\Asset;
use App\Models\AssetAdditionalInfo;
use App\Services\AssetAdditionalInfoService;
use Illuminate\Routing\Controller;

class AssetAdditionalInfoController extends Controller
{
    public function __construct(protected AssetAdditionalInfoService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSETS->value)->only(['show']);
        $this->middleware('permission:'.Permission::EDIT_ASSETS->value)->only(['store', 'update']);
    }

    public function show(Asset $asset)
    {
        $info = $this->service->show($asset);

        return ApiResponse::success('Asset additional info retrieved successfully.', $info);
    }

    public function store(StoreAssetAdditionalInfoRequest $request, Asset $asset)
    {
        if ($asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah informasi tambahan aset yang sudah dibuang.', 422);
        }

        $info = $this->service->store($asset, $request->validated());

        return ApiResponse::success('Asset additional info saved successfully.', $info, 201);
    }

    public function update(UpdateAssetAdditionalInfoRequest $request, AssetAdditionalInfo $assetAdditionalInfo)
    {
        if ($assetAdditionalInfo->asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah informasi tambahan aset yang sudah dibuang.', 422);
        }

        $info = $this->service->update($assetAdditionalInfo, $request->validated());

        return ApiResponse::success('Asset additional info updated successfully.', $info);
    }
}
