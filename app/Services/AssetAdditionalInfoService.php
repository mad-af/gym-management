<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAdditionalInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetAdditionalInfoService
{
    public function show(Asset $asset): ?AssetAdditionalInfo
    {
        return $asset->additionalInfo;
    }

    public function store(Asset $asset, array $data): AssetAdditionalInfo
    {
        return DB::transaction(function () use ($asset, $data) {
            $existing = $asset->additionalInfo;

            if ($existing) {
                $existing->update($this->prepareData($asset, $data, $existing->created_by));

                return $existing->refresh();
            }

            $payload = $this->prepareData($asset, $data, Auth::id());

            $info = AssetAdditionalInfo::create($payload);

            return $info->refresh();
        });
    }

    public function update(AssetAdditionalInfo $info, array $data): AssetAdditionalInfo
    {
        return DB::transaction(function () use ($info, $data) {
            $info->update($this->prepareData($info->asset, $data, $info->created_by));

            return $info->refresh();
        });
    }

    protected function prepareData(Asset $asset, array $data, ?string $createdBy): array
    {
        $payload = [
            'asset_id' => $asset->id,
            'manufacturer' => $data['manufacturer'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'extra_notes' => $data['extra_notes'] ?? null,
        ];

        if ($createdBy) {
            $payload['created_by'] = $createdBy;
        }

        return $payload;
    }
}
