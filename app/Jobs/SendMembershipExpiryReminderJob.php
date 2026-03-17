<?php

namespace App\Jobs;

use App\Models\MembershipTransaction;
use App\Services\WhatsappConfigService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMembershipExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $membershipTransactionId,
        public int $daysBefore
    ) {}

    public function handle(WhatsappConfigService $whatsappConfigService): void
    {
        $transaction = MembershipTransaction::query()
            ->with(['customer:id,name,phone,code', 'package:id,name'])
            ->find($this->membershipTransactionId);

        if (! $transaction || ! $transaction->customer?->phone) {
            return;
        }

        $status = strtolower((string) $transaction->status);
        if ($status !== 'active') {
            return;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($transaction->end_date)->startOfDay();
        $daysLeft = (int) $today->diffInDays($endDate, false);

        if ($this->daysBefore !== $daysLeft) {
            return;
        }

        $customerName = $transaction->customer->name;
        $customerCode = $transaction->customer->code ?? '-';
        $packageName = $transaction->package?->name ?? 'Membership';
        $formattedEndDate = $endDate->translatedFormat('d F Y');
        $daysText = $this->daysBefore === 0 ? 'hari ini' : 'dalam '.$this->daysBefore.' hari';

        $message = "Halo {$customerName},\n\n";
        $message .= "Membership Anda ({$packageName}) akan berakhir {$daysText}.\n";
        $message .= "Kode Member: {$customerCode}\n";
        $message .= "Tanggal Berakhir: {$formattedEndDate}\n\n";
        $message .= 'Silakan lakukan perpanjangan sebelum masa aktif berakhir. Terima kasih.';

        $whatsappConfigService->sendMessage($transaction->customer->phone, $message);
    }
}
