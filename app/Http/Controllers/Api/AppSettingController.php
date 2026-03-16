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
        $setting = $this->appSettingService->getSettings();

        return ApiResponse::success('App setting retrieved successfully', [
            'id' => $setting->id,
            'app_name' => $setting->app_name,
            'logo' => $setting->logo,
        ]);
    }

    public function update(AppSettingRequest $request): JsonResponse
    {
        $setting = $this->appSettingService->saveSettings($request->validated());

        return ApiResponse::success('App setting updated successfully', [
            'id' => $setting->id,
            'app_name' => $setting->app_name,
            'logo' => $setting->logo,
        ]);
    }
}
