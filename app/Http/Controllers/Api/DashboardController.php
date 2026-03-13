<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\Visit;
use App\Models\Sale;
use App\Models\MembershipTransaction;
use App\Models\StockMovement;
use Carbon\Carbon;
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

    public function operationsToday(Request $request)
    {
        $today = Carbon::today();

        $visitsToday = Visit::query()
            ->whereDate('checkin_time', $today)
            ->count();

        $memberVisitsToday = Visit::query()
            ->where('visit_type', 'MEMBERSHIP')
            ->whereDate('checkin_time', $today)
            ->count();

        $dailyRevenueToday = (float) Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', $today)
            ->sum('price');

        $salesCountToday = Sale::query()
            ->whereDate('created_at', $today)
            ->count();

        $salesRevenueToday = (float) Sale::query()
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $membershipCountToday = MembershipTransaction::query()
            ->whereDate('created_at', $today)
            ->count();

        $membershipRevenueToday = (float) MembershipTransaction::query()
            ->whereDate('created_at', $today)
            ->sum('price');

        $stockInToday = (int) StockMovement::query()
            ->where('type', 'IN')
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $stockOutToday = (int) StockMovement::query()
            ->where('type', 'OUT')
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $payload = [
            'visits' => [
                'count' => $visitsToday,
                'memberCount' => $memberVisitsToday,
                'dailyRevenue' => $dailyRevenueToday,
            ],
            'sales' => [
                'count' => $salesCountToday,
                'revenue' => $salesRevenueToday,
            ],
            'memberships' => [
                'count' => $membershipCountToday,
                'revenue' => $membershipRevenueToday,
            ],
            'stockMovements' => [
                'inQuantity' => $stockInToday,
                'outQuantity' => $stockOutToday,
            ],
        ];

        return ApiResponse::success('Operations stats for today retrieved successfully.', $payload);
    }
}
