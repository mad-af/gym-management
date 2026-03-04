<?php

namespace App\Console;

use App\Console\Commands\CheckMaintenanceReminderCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(CheckMaintenanceReminderCommand::class)->hourly();
    }
}
