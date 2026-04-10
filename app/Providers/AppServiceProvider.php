<?php

namespace App\Providers;

use App\Services\Notifications\NotificationService;
use App\Services\Notifications\WhatsAppNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NotificationService::class, WhatsAppNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDatabaseTimezone();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureDatabaseTimezone(): void
    {
        $driver = config('database.default');
        if ($driver !== 'mysql') {
            return;
        }

        $appTz = config('app.timezone');
        $now = CarbonImmutable::now($appTz);
        $offset = $now->format('P');

        DB::statement("SET time_zone = '{$offset}'");
    }
}
