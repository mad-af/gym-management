<?php

namespace App\Http\Middleware;

use App\Services\AppSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $appSettings = null;
        if (Schema::hasTable('app_settings')) {
            $service = app(AppSettingService::class);
            $service->normalizeLegacyRows();
            $appSettings = $service->getAppSettings();
        }
        $appName = $appSettings['app_name'] ?? config('app.name');
        $appDescription = $appSettings['app_description'] ?? 'Kelola operasional gym Anda dengan lebih efisien.';

        return [
            ...parent::share($request),
            'name' => $appName,
            'app' => [
                'name' => $appName,
                'description' => $appDescription,
                'logo' => $appSettings['logo'] ?? null,
                'daily_visit_price' => $appSettings['daily_visit_price'] ?? 0,
            ],
            'auth' => [
                'user' => $user,
                'permissions' => $user
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
