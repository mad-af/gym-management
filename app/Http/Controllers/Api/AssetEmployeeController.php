<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatusEnum;
use App\Enums\Permission;
use App\Helpers\ApiResponse;
use App\Http\Requests\AssetEmployee\UpdateAssetEmployeeRequest;
use App\Models\Asset;
use App\Services\AssetEmployeeService;
use Illuminate\Routing\Controller;

class AssetEmployeeController extends Controller
{
    public function __construct(protected AssetEmployeeService $service)
    {
        $this->middleware(['auth:web']);
        $this->middleware('permission:'.Permission::VIEW_ASSETS->value)->only(['show']);
        $this->middleware('permission:'.Permission::EDIT_ASSETS->value)->only(['update']);
    }

    public function show(Asset $asset)
    {
        $asset->load('employee');

        return ApiResponse::success('Asset employee retrieved successfully.', [
            'asset_id' => $asset->id,
            'employee' => $asset->employee,
        ]);
    }

    public function update(UpdateAssetEmployeeRequest $request, Asset $asset)
    {
        if ($asset->status === AssetStatusEnum::DISPOSED) {
            return ApiResponse::error('Tidak dapat mengubah pegawai penanggung jawab pada aset yang sudah dibuang.', 422);
        }

        $validated = $request->validated();

        $employeeId = $validated['employee_id'] ?? null;

        $updated = $this->service->update($asset, $employeeId);

        return ApiResponse::success('Asset employee updated successfully.', [
            'asset_id' => $updated->id,
            'employee' => $updated->employee,
        ]);
    }
}
