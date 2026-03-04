<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAdditionalInfo;
use App\Models\AssetHistory;

class PublicAssetService
{
    /**
     * Get asset details for public view.
     */
    public function getAssetDetail(string $id): ?Asset
    {
        return Asset::with(['category', 'opd', 'room', 'fundingSource', 'creator', 'employee'])
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Get asset additional info for public view.
     */
    public function getAssetAdditionalInfo(string $assetId): ?AssetAdditionalInfo
    {
        return AssetAdditionalInfo::where('asset_id', $assetId)->first();
    }

    /**
     * Get asset history for public view.
     */
    public function getAssetHistory(string $assetId, int $limit = 10)
    {
        return AssetHistory::with(['performedBy', 'asset'])
            ->where('asset_id', $assetId)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
