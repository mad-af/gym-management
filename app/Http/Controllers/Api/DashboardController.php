<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function stats(Request $request)
    {
        $stats = $this->service->getStats();

        return ApiResponse::success('Dashboard statistics retrieved successfully.', $stats);
    }

    public function categoryBudget(Request $request)
    {
        $stats = $this->service->getCategoryBudgetStats();

        return ApiResponse::success('Category budget statistics retrieved successfully.', $stats);
    }

    public function assetCondition(Request $request)
    {
        $stats = $this->service->getAssetConditionStats();

        return ApiResponse::success('Asset condition statistics retrieved successfully.', $stats);
    }

    public function proposals(Request $request)
    {
        $data = $this->service->getProposalSummary();

        return ApiResponse::success('Dashboard proposal summary retrieved successfully.', $data);
    }

    public function maintenances(Request $request)
    {
        $data = $this->service->getMaintenanceSummary();

        return ApiResponse::success('Dashboard maintenance summary retrieved successfully.', $data);
    }
}
