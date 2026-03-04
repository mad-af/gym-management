<?php

namespace App\Jobs;

use App\Models\AssetMaintenance;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMaintenanceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $maintenanceId) {}

    public function handle(NotificationService $notificationService): void
    {
        $maintenance = AssetMaintenance::with('asset')->find($this->maintenanceId);

        if (! $maintenance) {
            return;
        }

        if ($maintenance->notification_sent_at !== null) {
            return;
        }

        if ($maintenance->status !== \App\Enums\MaintenanceStatusEnum::SCHEDULED) {
            return;
        }

        $notificationService->sendMaintenanceReminder($maintenance);

        $maintenance->notification_sent_at = now();
        $maintenance->save();
    }
}
