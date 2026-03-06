<?php

namespace App\Services;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'total_assets' => 0,
            'total_valued_assets' => 0,
            'assets_with_price' => 0,
            'assets_without_price' => 0,
            'active_assets' => 0,
            'under_maintenance_assets' => 0,
            'disposed_assets' => 0,
            'major_damage_assets' => 0,
        ];
    }

    public function getCategoryBudgetStats(): array
    {
        return [
            'parents' => [],
            'children' => [],
            'values' => [],
            'total_value' => 0,
            'items' => [],
        ];
    }

    public function getAssetConditionStats(): array
    {
        return [
            'labels' => ['Baik', 'Rusak Ringan', 'Rusak Berat'],
            'series' => [0, 0, 0],
        ];
    }

    public function getProposalSummary(): array
    {
        return [
            'recent_proposals' => [],
            'status_counts' => [],
        ];
    }

    public function getMaintenanceSummary(): array
    {
        return [
            'recent_maintenances' => [],
            'upcoming_maintenances' => [],
        ];
    }
}
