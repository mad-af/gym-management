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
        $setting = $this->appSettingService->getSettings();

        return Inertia::render('settings/Application', [
            'settings' => [
                'id' => $setting->id,
                'app_name' => $setting->app_name,
                'logo' => $setting->logo,
            ],
        ]);
    }
}
