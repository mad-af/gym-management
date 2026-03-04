<?php

namespace App\Services\Notifications;

use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class WhatsAppNotificationService implements NotificationService
{
    public function sendMaintenanceReminder(AssetMaintenance $maintenance): void
    {
        $asset = $maintenance->asset()->with(['opd'])->first();

        if (! $asset || ! $asset->opd || ! $asset->opd->phone) {
            return;
        }

        $apiUrl = Config::get('services.whatsapp.endpoint');
        $token = Config::get('services.whatsapp.token');

        if (! $apiUrl || ! $token) {
            return;
        }

        $message = $this->buildMessage($maintenance);

        $response = Http::withToken($token)->post($apiUrl, [
            'to' => $asset->opd->phone,
            'message' => $message,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to send WhatsApp maintenance reminder.');
        }
    }

    protected function buildMessage(AssetMaintenance $maintenance): string
    {
        $asset = $maintenance->asset;

        return sprintf(
            'Pengingat maintenance aset %s (%s) pada %s. Jenis: %s.',
            $asset->name,
            $asset->asset_code,
            optional($maintenance->scheduled_date)->format('d-m-Y'),
            $maintenance->maintenance_type
        );
    }
}
