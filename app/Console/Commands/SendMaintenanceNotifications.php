<?php

namespace App\Console\Commands;

use App\Models\AssetMaintenance;
use App\Services\WhatsappConfigService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMaintenanceNotifications extends Command
{
    protected $signature = 'maintenance:send-notifications';

    protected $description = 'Send WhatsApp notifications for scheduled asset maintenance (H-3 and H-0)';

    public function __construct(protected WhatsappConfigService $whatsappService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting maintenance notifications check...');

        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);

        // Get maintenances for H-0 and H-3
        $maintenances = AssetMaintenance::with(['asset', 'asset.employee.user', 'creator'])
            ->where('status', \App\Enums\MaintenanceStatusEnum::SCHEDULED)
            ->where(function ($query) use ($today, $threeDaysFromNow) {
                $query->whereDate('scheduled_date', $today)
                    ->orWhereDate('scheduled_date', $threeDaysFromNow);
            })
            ->get();

        $this->info("Found {$maintenances->count()} maintenances to process.");

        foreach ($maintenances as $maintenance) {
            $scheduledDate = Carbon::parse($maintenance->scheduled_date);
            $isToday = $scheduledDate->isSameDay($today);
            $daysText = $isToday ? 'HARI INI' : 'dalam 3 hari';

            $message = "🔔 *Pemberitahuan Jadwal Maintenance Aset*\n\n";
            $message .= "Maintenance aset berikut dijadwalkan *{$daysText}*:\n\n";
            $message .= "📦 Aset: {$maintenance->asset->name} ({$maintenance->asset->asset_code})\n";
            $message .= "📅 Tanggal: {$scheduledDate->format('d/m/Y')}\n";
            $message .= "🔧 Tipe: {$maintenance->maintenance_type}\n";
            if ($maintenance->description) {
                $message .= "📝 Deskripsi: {$maintenance->description}\n";
            }
            $message .= "\nMohon persiapkan aset untuk proses maintenance.";

            // Collect recipients
            $recipients = collect();

            // 1. Creator
            if ($maintenance->creator && $maintenance->creator->phone) {
                $recipients->push($maintenance->creator);
            }

            // 2. Asset Responsible Person (if linked to user)
            if ($maintenance->asset && $maintenance->asset->employee && $maintenance->asset->employee->user && $maintenance->asset->employee->user->phone) {
                $recipients->push($maintenance->asset->employee->user);
            }

            // Unique recipients by ID to avoid duplicate messages
            $recipients = $recipients->unique('id');

            foreach ($recipients as $recipient) {
                try {
                    $this->whatsappService->sendMessage($recipient->phone, $message);
                    $this->info("Notification sent to {$recipient->name} ({$recipient->phone}) for maintenance ID {$maintenance->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to send notification to {$recipient->name}: ".$e->getMessage());
                    Log::error('Maintenance Notification Error: '.$e->getMessage());
                }
            }
        }

        $this->info('Maintenance notifications check completed.');
    }
}
