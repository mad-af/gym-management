<?php

namespace App\Services;

use App\Helpers\QrCodeHelper;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MembershipCardService
{
    private const CARD_WIDTH_MM = 85.6;

    private const CARD_HEIGHT_MM = 54;

    public function __construct(private readonly WhatsappConfigService $whatsappConfigService) {}

    public function generatePdfBinary(Customer $customer): string
    {
        $payload = $this->buildPayload($customer);
        $html = view('pdf.membership_card', $payload)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $this->getCardPaperSizeMm(),
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
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
        $memberCode = trim((string) ($customer->code ?: $customer->qr_code ?: ''));
        $qrImage = $memberCode !== ''
            ? QrCodeHelper::generateBase64Svg($memberCode, 256)
            : null;

        return [
            'customer_name' => $customer->name,
            'member_code' => $memberCode !== '' ? $memberCode : '-',
            'phone' => $customer->phone,
            'qr_image' => $qrImage,
            'app_name' => config('app.name'),
        ];
    }

    private function getCardPaperSizeMm(): array
    {
        return [self::CARD_WIDTH_MM, self::CARD_HEIGHT_MM];
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
