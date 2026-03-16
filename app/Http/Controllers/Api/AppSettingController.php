<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Requests\AppSettingRequest;
use App\Services\AppSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AppSettingController extends Controller
{
    public function __construct(protected AppSettingService $appSettingService) {}

    public function index(): JsonResponse
    {
        $settings = $this->appSettingService->getAppSettings();

        return ApiResponse::success('App setting retrieved successfully', [
            'id' => $settings['id'],
            'app_name' => $settings['app_name'],
            'app_description' => $settings['app_description'],
            'daily_visit_price' => $settings['daily_visit_price'],
            'logo' => $settings['logo'],
        ]);
    }

    public function update(AppSettingRequest $request): JsonResponse
    {
        $settings = $this->appSettingService->saveAppSettings($request->validated());

        return ApiResponse::success('App setting updated successfully', [
            'id' => $settings['id'],
            'app_name' => $settings['app_name'],
            'app_description' => $settings['app_description'],
            'daily_visit_price' => $settings['daily_visit_price'],
            'logo' => $settings['logo'],
        ]);
    }
}
