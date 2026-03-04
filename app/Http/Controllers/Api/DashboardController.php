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
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getStats($currentOpd);

        return ApiResponse::success('Dashboard statistics retrieved successfully.', $stats);
    }

    public function categoryBudget(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getCategoryBudgetStats($currentOpd);

        return ApiResponse::success('Category budget statistics retrieved successfully.', $stats);
    }

    public function assetCondition(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $stats = $this->service->getAssetConditionStats($currentOpd);

        return ApiResponse::success('Asset condition statistics retrieved successfully.', $stats);
    }

    public function proposals(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $data = $this->service->getProposalSummary($currentOpd);

        return ApiResponse::success('Dashboard proposal summary retrieved successfully.', $data);
    }

    public function maintenances(Request $request)
    {
        $currentOpd = $request->session()->get('current_opd_id');

        $data = $this->service->getMaintenanceSummary($currentOpd);

        return ApiResponse::success('Dashboard maintenance summary retrieved successfully.', $data);
    }
}
