<?php

namespace App\Services\Notifications;

use App\Models\AssetMaintenance;

interface NotificationService
{
    public function sendMaintenanceReminder(AssetMaintenance $maintenance): void;
}
