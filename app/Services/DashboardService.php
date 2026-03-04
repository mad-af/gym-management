<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\MaintenanceStatusEnum;
use App\Enums\ProposalStatusEnum;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function getStats(?string $currentOpdId): array
    {
        $baseQuery = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            });

        // Query untuk menghitung total aset yang belum dibuang
        $totalAssets = (clone $baseQuery)
            ->where('status', '!=', AssetStatusEnum::DISPOSED)
            ->count();

        // Base query untuk aset yang belum dibuang
        $activeBaseQuery = (clone $baseQuery)
            ->where('status', '!=', AssetStatusEnum::DISPOSED);

        $assetsWithPriceQuery = (clone $activeBaseQuery)
            ->whereNotNull('purchase_price');

        $assetsWithPrice = (clone $assetsWithPriceQuery)->count();

        $assetsWithoutPrice = max($totalAssets - $assetsWithPrice, 0);

        $totalValuedAssets = (clone $assetsWithPriceQuery)->sum('purchase_price');

        $activeAssets = (clone $baseQuery)
            ->where('status', AssetStatusEnum::ACTIVE)
            ->count();

        $underMaintenanceAssets = (clone $baseQuery)
            ->where('status', AssetStatusEnum::UNDER_MAINTENANCE)
            ->count();

        $disposedAssets = (clone $baseQuery)
            ->where('status', AssetStatusEnum::DISPOSED)
            ->count();

        $majorDamageAssets = (clone $activeBaseQuery)
            ->where('condition', AssetConditionEnum::MAJOR_DAMAGE)
            ->count();

        return [
            'total_assets' => $totalAssets,
            'total_valued_assets' => $totalValuedAssets,
            'assets_with_price' => $assetsWithPrice,
            'assets_without_price' => $assetsWithoutPrice,
            'active_assets' => $activeAssets,
            'under_maintenance_assets' => $underMaintenanceAssets,
            'disposed_assets' => $disposedAssets,
            'major_damage_assets' => $majorDamageAssets,
        ];
    }

    public function getCategoryBudgetStats(?string $currentOpdId): array
    {
        $rows = Asset::query()
            ->whereNotNull('purchase_price')
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->join('asset_categories as child', 'assets.category_id', '=', 'child.id')
            ->join('asset_categories as parent', 'child.parent_id', '=', 'parent.id')
            ->where('child.level', 2)
            ->where('parent.level', 1)
            ->groupBy('parent.id', 'parent.name', 'child.id', 'child.name')
            ->selectRaw(
                'parent.id as parent_id, parent.name as parent_name, child.id as child_id, child.name as child_name, COALESCE(SUM(purchase_price), 0) as total_value'
            )
            ->orderBy('parent.name')
            ->orderByDesc('total_value')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'parents' => [],
                'children' => [],
                'values' => [],
                'total_value' => 0,
                'items' => [],
            ];
        }

        $parents = [];
        $children = [];
        $matrix = [];
        $totalValue = 0;

        foreach ($rows as $row) {
            $parentId = $row->parent_id;
            $parentName = $row->parent_name ?? 'Tanpa Induk';
            $childId = $row->child_id;
            $childName = $row->child_name ?? 'Tanpa Subkategori';
            $value = (float) $row->total_value;

            if (! isset($parents[$parentId])) {
                $parents[$parentId] = [
                    'id' => $parentId,
                    'name' => $parentName,
                    'total' => 0.0,
                ];
            }

            if (! isset($children[$childId])) {
                $children[$childId] = [
                    'id' => $childId,
                    'name' => $childName,
                ];
            }

            $parents[$parentId]['total'] += $value;
            $matrix[$parentId][$childId] = ($matrix[$parentId][$childId] ?? 0) + $value;
            $totalValue += $value;
        }

        $parentList = array_values($parents);
        $childList = array_values($children);

        usort($parentList, function (array $a, array $b) {
            return $b['total'] <=> $a['total'];
        });

        $parentIndexMap = [];

        foreach ($parentList as $index => $parent) {
            $parentIndexMap[$parent['id']] = $index;
        }

        usort($childList, function (array $a, array $b) {
            return strcmp($a['name'], $b['name']);
        });

        $childIndexMap = [];

        foreach ($childList as $index => $child) {
            $childIndexMap[$child['id']] = $index;
        }

        $values = [];

        foreach ($parentList as $pIndex => $parent) {
            $values[$pIndex] = [];

            foreach ($childList as $cIndex => $child) {
                $parentId = $parent['id'];
                $childId = $child['id'];

                $values[$pIndex][$cIndex] = $matrix[$parentId][$childId] ?? 0;
            }
        }

        $items = [];

        foreach ($parentList as $index => $parent) {
            $percentage = $totalValue > 0 ? round(($parent['total'] / $totalValue) * 100, 1) : 0;

            $items[] = [
                'category' => $parent['name'],
                'total_value' => $parent['total'],
                'percentage' => $percentage,
                'rank' => $index + 1,
            ];
        }

        return [
            'parents' => array_map(function (array $parent) {
                return [
                    'id' => $parent['id'],
                    'name' => $parent['name'],
                    'total_value' => $parent['total'],
                ];
            }, $parentList),
            'children' => array_map(function (array $child) {
                return [
                    'id' => $child['id'],
                    'name' => $child['name'],
                ];
            }, $childList),
            'values' => $values,
            'total_value' => $totalValue,
            'items' => $items,
        ];
    }

    public function getAssetConditionStats(?string $currentOpdId): array
    {
        $baseQuery = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            });

        $totalAssets = (clone $baseQuery)->count();

        $conditions = [
            AssetConditionEnum::GOOD,
            AssetConditionEnum::MINOR_DAMAGE,
            AssetConditionEnum::MAJOR_DAMAGE,
            AssetConditionEnum::LOST,
        ];

        $items = [];
        $labels = [];
        $series = [];

        foreach ($conditions as $conditionEnum) {
            $count = (clone $baseQuery)
                ->where('condition', $conditionEnum)
                ->count();

            $labels[] = $conditionEnum->label();
            $series[] = $count;

            $percentage = $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0;

            $items[] = [
                'key' => $conditionEnum->value,
                'label' => $conditionEnum->label(),
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return [
            'labels' => $labels,
            'series' => $series,
            'items' => $items,
            'total_assets' => $totalAssets,
        ];
    }

    public function getProposalSummary(?string $currentOpdId): array
    {
        $query = \App\Models\AssetProposal::query()
            ->with(['opd', 'category'])
            ->when($currentOpdId, function ($q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->whereIn('status', [
                ProposalStatusEnum::SUBMITTED->value,
                ProposalStatusEnum::APPROVED->value,
            ])
            ->latest('proposal_date')
            ->limit(5);

        $proposals = $query->get();

        $items = $proposals->map(function ($proposal) {
            $qty = (int) $proposal->qty;
            $estimatedPrice = (float) $proposal->estimated_price;
            $totalEstimation = $qty * $estimatedPrice;

            $statusEnum = $proposal->status instanceof ProposalStatusEnum
                ? $proposal->status
                : ProposalStatusEnum::from($proposal->status);

            return [
                'id' => $proposal->id,
                'proposal_number' => $proposal->proposal_number,
                'proposal_date' => $proposal->proposal_date,
                'opd_name' => $proposal->opd->name ?? '-',
                'item_name' => $proposal->item_name,
                'category_name' => $proposal->category->name ?? '-',
                'qty' => $qty,
                'estimated_price' => $estimatedPrice,
                'total_estimation' => $totalEstimation,
                'status' => $proposal->status,
                'status_label' => $statusEnum->label(),
                'status_class' => $statusEnum->badgeClass(),
            ];
        })->values();

        return [
            'items' => $items,
            'status_options' => ProposalStatusEnum::toOptions(),
        ];
    }

    public function getMaintenanceSummary(?string $currentOpdId): array
    {
        $query = AssetMaintenance::query()
            ->with(['asset', 'asset.opd'])
            ->when($currentOpdId, function ($q) use ($currentOpdId) {
                $q->whereHas('asset', function ($assetQuery) use ($currentOpdId) {
                    $assetQuery->where('opd_id', $currentOpdId);
                });
            })
            ->whereIn('status', [
                MaintenanceStatusEnum::SCHEDULED->value,
                MaintenanceStatusEnum::IN_PROGRESS->value,
                MaintenanceStatusEnum::OVERDUE->value,
            ])
            ->latest('scheduled_date')
            ->limit(5);

        $maintenances = $query->get();

        $items = $maintenances->map(function (AssetMaintenance $maintenance) {
            $asset = $maintenance->asset;
            $statusEnum = $maintenance->status instanceof MaintenanceStatusEnum
                ? $maintenance->status
                : MaintenanceStatusEnum::from($maintenance->status);

            return [
                'id' => $maintenance->id,
                'maintenance_type' => $maintenance->maintenance_type,
                'scheduled_date' => $maintenance->scheduled_date,
                'status' => $maintenance->status,
                'status_label' => $statusEnum->label(),
                'status_class' => $statusEnum->badgeClass(),
                'asset_name' => $asset?->name ?? '-',
                'asset_code' => $asset?->asset_code ?? '-',
                'opd_name' => $asset?->opd?->name ?? '-',
                'photo' => $asset?->photo,
            ];
        })->values();

        return [
            'items' => $items,
            'status_options' => MaintenanceStatusEnum::toOptions(),
        ];
    }
}
