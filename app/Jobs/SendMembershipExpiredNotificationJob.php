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

class SendMembershipExpiredNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $membershipTransactionId
    ) {}

    public function handle(WhatsappConfigService $whatsappConfigService): void
    {
        $transaction = MembershipTransaction::query()
            ->with(['customer:id,name,phone,code', 'package:id,name'])
            ->find($this->membershipTransactionId);

        if (! $transaction || ! $transaction->customer?->phone) {
            return;
        }

        $customerName = $transaction->customer->name;
        $customerCode = $transaction->customer->code ?? '-';
        $packageName = $transaction->package?->name ?? 'Membership';
        $endDate = Carbon::parse($transaction->end_date)->startOfDay();
        $formattedEndDate = $endDate->translatedFormat('d F Y');

        $message = "Halo {$customerName},\n\n";
        $message .= "Membership Anda ({$packageName}) telah berakhir pada {$formattedEndDate}.\n";
        $message .= "Kode Member: {$customerCode}\n\n";
        $message .= 'Terima kasih telah menjadi member kami. Untuk perpanjangan, silakan hubungi kami.';

        $whatsappConfigService->sendMessage($transaction->customer->phone, $message);
    }
}
