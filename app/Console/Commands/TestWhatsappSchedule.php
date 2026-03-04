<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Fonnte\FonnteService;
use Illuminate\Console\Command;

class TestWhatsappSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:whatsapp-schedule {phone? : The phone number to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test scheduled WhatsApp notification via Fonnte';

    /**
     * Execute the console command.
     */
    public function handle(FonnteService $fonnteService)
    {
        $phone = $this->argument('phone');

        if (! $phone) {
            $users = User::all(['name', 'phone']);

            if ($users->isEmpty()) {
                $this->error('No users found in database.');

                return 1;
            }

            $usersWithPhone = $users->filter(fn ($u) => ! empty($u->phone));

            if ($usersWithPhone->isEmpty()) {
                $this->info('Found users, but none have a phone number:');
                foreach ($users as $user) {
                    $this->line("- {$user->name} (No phone)");
                }
                $this->error('Please provide a specific phone number as an argument.');

                return 1;
            }

            if ($usersWithPhone->count() > 1) {
                $this->info('Found multiple users with phone numbers:');
                foreach ($usersWithPhone as $user) {
                    $this->line("- {$user->name}: {$user->phone}");
                }
                $this->error('Please provide a specific phone number as an argument.');

                return 1;
            }

            $user = $usersWithPhone->first();
            $phone = $user->phone;
            $this->info("Found single user {$user->name} with phone: {$phone}");
        }

        // Fonnte usually expects 628... or 08... (it will be converted)
        // Let's assume input is correct or basic cleaning.

        $scheduleTime = now()->addMinutes(5);
        $scheduleTimestamp = $scheduleTime->timestamp;

        $this->info("Scheduling message to {$phone} at {$scheduleTime->toDateTimeString()} (Timestamp: {$scheduleTimestamp})");

        try {
            $response = $fonnteService->sendMessage(
                $phone,
                'Test scheduled message from Asset Management System. Sent at '.now()->toDateTimeString().'. Scheduled for '.$scheduleTime->toDateTimeString(),
                ['schedule' => $scheduleTimestamp]
            );

            if ($response->successful()) {
                $this->info('Message scheduled successfully.');
                $this->info('Response: '.$response->body());
            } else {
                $this->error('Failed to schedule message.');
                $this->error('Response: '.$response->body());

                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Exception: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
