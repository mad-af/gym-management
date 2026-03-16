<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function stats()
    {
        $stats = $this->service->getGymAnalytics();

        return ApiResponse::success('Dashboard statistics retrieved successfully.', $stats);
    }

    public function operationsToday()
    {
        $payload = $this->service->getOperationsToday();

        return ApiResponse::success('Operations stats for today retrieved successfully.', $payload);
    }
}
