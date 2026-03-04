<?php

namespace App\Console\Commands;

use App\Jobs\SendMaintenanceReminderJob;
use App\Services\AssetMaintenanceService;
use Illuminate\Console\Command;

class CheckMaintenanceReminderCommand extends Command
{
    protected $signature = 'maintenance:check-reminders';

    protected $description = 'Detect overdue maintenance records and dispatch reminder jobs';

    public function handle(AssetMaintenanceService $service): int
    {
        $overdueCandidates = $service->findOverdueCandidates(now());

        foreach ($overdueCandidates as $maintenance) {
            $service->markOverdue($maintenance);
        }

        $reminderDaysBefore = 3;

        $reminders = $service->findReminderCandidates($reminderDaysBefore);

        foreach ($reminders as $maintenance) {
            SendMaintenanceReminderJob::dispatch($maintenance->id);
        }

        return self::SUCCESS;
    }
}
