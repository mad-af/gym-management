<?php

namespace App\Services;

use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MembershipCardService
{
    public function __construct(private readonly WhatsappConfigService $whatsappConfigService) {}

    public function generatePdfBinary(Customer $customer): string
    {
        $payload = $this->buildPayload($customer);

        return Pdf::loadView('pdf.membership-card', $payload)
            ->setPaper('a6', 'landscape')
            ->output();
    }

    public function createPublicPdf(Customer $customer): array
    {
        $binary = $this->generatePdfBinary($customer);
        $timestamp = now();
        $fileName = sprintf(
            'membership-card-%s-%s.pdf',
            $customer->id,
            $timestamp->format('YmdHis')
        );
        $path = sprintf('membership-cards/%s/%s', $timestamp->format('Ymd'), $fileName);

        Storage::disk('public')->put($path, $binary);

        return [
            'path' => $path,
            'file_name' => $fileName,
            'url' => asset('storage/'.$path),
        ];
    }

    public function sendViaWhatsapp(Customer $customer, ?string $target = null): array
    {
        $destination = $this->normalizePhone($target ?? $customer->phone);
        if ($destination === null) {
            throw new \RuntimeException('Nomor WhatsApp customer belum tersedia.');
        }

        $file = $this->createPublicPdf($customer);
        $payload = $this->buildPayload($customer);

        $message = sprintf(
            'Halo %s, berikut kartu membership Anda dari %s.',
            $payload['customer_name'],
            config('app.name')
        );

        $result = $this->whatsappConfigService->sendMessage($destination, $message, [
            'url' => $file['url'],
            'filename' => $file['file_name'],
        ]);

        return [
            'target' => $destination,
            'file_url' => $file['url'],
            'fonnte' => $result,
        ];
    }

    private function buildPayload(Customer $customer): array
    {
        $customer->loadMissing('membershipTransactions.package');

        $activeTransaction = $customer->membershipTransactions
            ->filter(function ($transaction) {
                if (! $transaction->start_date || ! $transaction->end_date) {
                    return false;
                }

                $today = Carbon::today();

                return $transaction->start_date->startOfDay() <= $today
                    && $transaction->end_date->startOfDay() >= $today;
            })
            ->sortByDesc('end_date')
            ->first();

        return [
            'customer_name' => $customer->name,
            'member_code' => $customer->code ?: $customer->qr_code,
            'phone' => $customer->phone,
            'package_name' => $activeTransaction?->package?->name,
            'active_until' => $activeTransaction?->end_date?->format('d M Y'),
            'generated_at' => now()->format('d M Y H:i'),
            'app_name' => config('app.name'),
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (! str_starts_with($digits, '62')) {
            return '62'.$digits;
        }

        return $digits;
    }
}
