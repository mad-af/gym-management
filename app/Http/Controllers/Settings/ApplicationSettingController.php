<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\AppSettingService;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationSettingController extends Controller
{
    public function __construct(protected AppSettingService $appSettingService) {}

    public function edit(): Response
    {
        $settings = $this->appSettingService->getAppSettings();

        return Inertia::render('settings/Application', [
            'settings' => [
                'id' => $settings['id'],
                'app_name' => $settings['app_name'],
                'app_description' => $settings['app_description'],
                'daily_visit_price' => $settings['daily_visit_price'],
                'logo' => $settings['logo'],
            ],
        ]);
    }
}
