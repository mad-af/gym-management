<?php

namespace App\Http\Middleware;

use App\Models\Opd;
use App\Services\CurrentOpdService;
use Illuminate\Http\Request;
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

        $availableOpds = [];
        $currentOpd = null;

        if ($user) {
            $currentOpdService = app(CurrentOpdService::class);

            // Get available OPDs based on user permissions
            $query = $user->has_all_opds
                ? Opd::query()
                : $user->opds();

            $availableOpds = $query
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();

            // Get current OPD from service
            $currentOpd = $currentOpdService->getCurrentOpd($user);

            // Only set default OPD if user has never made a choice (session is empty)
            // and there are available OPDs
            $hasMadeChoice = session()->has('current_opd_id') || session()->has('current_opd_id_cleared');
            if (! $currentOpd && ! $hasMadeChoice && $availableOpds->count() > 0) {
                $currentOpd = $currentOpdService->setDefaultOpd($user);
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'permissions' => $user
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [],
                'opds' => $availableOpds,
                'current_opd' => $currentOpd,
                'has_all_opds' => $user?->has_all_opds ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
