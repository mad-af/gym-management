<?php

namespace App\Console\Commands;

use App\Jobs\SendMembershipExpiryReminderJob;
use App\Models\MembershipTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckMembershipExpiryReminderCommand extends Command
{
    protected $signature = 'membership:check-expiry-reminders';

    protected $description = 'Detect membership transactions expiring at H-3 and H-day, then dispatch WhatsApp reminder jobs';

    public function handle(): int
    {
        $today = Carbon::today();
        $targets = [
            3 => $today->copy()->addDays(3),
            0 => $today,
        ];

        $totalDispatched = 0;

        foreach ($targets as $daysBefore => $targetDate) {
            $transactions = MembershipTransaction::query()
                ->with(['customer:id,name,phone', 'package:id,name'])
                ->where(function ($query) {
                    $query->where('status', 'active')
                        ->orWhere('status', 'ACTIVE');
                })
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', $targetDate)
                ->get();

            foreach ($transactions as $transaction) {
                if (! $transaction->customer?->phone) {
                    continue;
                }

                $lockKey = sprintf(
                    'membership-expiry-reminder:%s:%d:%s',
                    $transaction->id,
                    $daysBefore,
                    $targetDate->toDateString()
                );

                $acquired = Cache::add($lockKey, true, now()->addDays(14));
                if (! $acquired) {
                    continue;
                }

                SendMembershipExpiryReminderJob::dispatch($transaction->id, $daysBefore);
                $totalDispatched++;
            }
        }

        $this->info("Membership expiry reminder jobs dispatched: {$totalDispatched}");

        return self::SUCCESS;
    }
}
