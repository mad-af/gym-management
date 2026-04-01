<?php

namespace App\Console\Commands;

use App\Enums\StatusEnum;
use App\Jobs\SendMembershipExpiredNotificationJob;
use App\Models\MembershipTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExpireMembershipTransactionsCommand extends Command
{
    protected $signature = 'membership:expire';

    protected $description = 'Expire membership transactions that have passed their end_date and send WhatsApp notifications';

    public function handle(): int
    {
        $today = Carbon::today();

        $expiredTransactions = MembershipTransaction::query()
            ->with(['customer:id,name,phone', 'package:id,name'])
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status', 'ACTIVE');
            })
            ->whereDate('end_date', '<', $today)
            ->whereNull('cancelled_at')
            ->get();

        if ($expiredTransactions->isEmpty()) {
            $this->info('No membership transactions to expire.');

            return self::SUCCESS;
        }

        $updated = 0;
        $notified = 0;

        foreach ($expiredTransactions as $transaction) {
            $wasActive = strtolower((string) $transaction->status) === 'active';

            if ($wasActive) {
                $transaction->update(['status' => StatusEnum::INACTIVE->value]);
                $updated++;
            }

            if ($transaction->customer?->phone) {
                $lockKey = sprintf(
                    'membership-expired-notification:%s:%s',
                    $transaction->id,
                    $today->toDateString()
                );

                $acquired = Cache::add($lockKey, true, now()->addDays(14));
                if ($acquired) {
                    SendMembershipExpiredNotificationJob::dispatch($transaction->id);
                    $notified++;
                }
            }
        }

        $this->info("Expired {$updated} membership transactions and sent {$notified} notifications.");

        return self::SUCCESS;
    }
}
