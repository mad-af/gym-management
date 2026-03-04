<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\PublicAssetService;
use Inertia\Inertia;
use Inertia\Response;

class PublicAssetController extends Controller
{
    public function __construct(protected PublicAssetService $service)
    {
        // No auth middleware needed for public access
    }

    public function show(string $id): Response
    {
        $asset = $this->service->getAssetDetail($id);

        if (! $asset) {
            abort(404);
        }

        $additionalInfo = $this->service->getAssetAdditionalInfo($id);
        $history = $this->service->getAssetHistory($id);

        return Inertia::render('Public/Asset/Detail', [
            'asset' => $asset,
            'additionalInfo' => $additionalInfo,
            'history' => $history,
        ]);
    }

    public function detail(string $id)
    {
        $asset = $this->service->getAssetDetail($id);

        if (! $asset) {
            return ApiResponse::error('Asset not found', 404);
        }

        $additionalInfo = $this->service->getAssetAdditionalInfo($id);
        // We can add history here if needed, but for now just asset and additional info

        return ApiResponse::success('Asset detail retrieved successfully.', [
            'asset' => $asset,
            'additional_info' => $additionalInfo,
        ]);
    }
}
