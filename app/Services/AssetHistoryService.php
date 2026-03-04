<?php

namespace App\Services;

use App\Enums\AssetHistoryActionEnum;
use App\Models\AssetHistory;

class AssetHistoryService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $action = null,
        ?string $assetId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $opdId = null
    ): array {
        $query = AssetHistory::query()
            ->with([
                'asset.category',
                'asset.opd',
                'asset.room',
                'performedBy',
            ])
            ->latest('created_at');

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        if ($assetId) {
            $query->where('asset_id', $assetId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($assetQuery) use ($search) {
                    $assetQuery->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })->orWhereHas('performedBy', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (AssetHistory $history) {
                $before = $history->data_before ?? [];
                $after = $history->data_after ?? [];

                if (! is_array($before)) {
                    $before = (array) $before;
                }

                if (! is_array($after)) {
                    $after = (array) $after;
                }

                $keys = array_unique(array_merge(array_keys($before), array_keys($after)));

                $changedBefore = [];
                $changedAfter = [];

                foreach ($keys as $key) {
                    $beforeExists = array_key_exists($key, $before);
                    $afterExists = array_key_exists($key, $after);

                    $beforeValue = $beforeExists ? $before[$key] : null;
                    $afterValue = $afterExists ? $after[$key] : null;

                    if (! $beforeExists && ! $afterExists) {
                        continue;
                    }

                    if ($beforeValue === $afterValue) {
                        continue;
                    }

                    if ($beforeExists) {
                        $changedBefore[$key] = $beforeValue;
                    }

                    if ($afterExists) {
                        $changedAfter[$key] = $afterValue;
                    }
                }

                $history->data_before = $changedBefore ?: null;
                $history->data_after = $changedAfter ?: null;

                return $history;
            })
        );

        $actionOptions = AssetHistoryActionEnum::toOptions();

        return [
            'items' => $paginator,
            'action_options' => $actionOptions,
        ];
    }
}
