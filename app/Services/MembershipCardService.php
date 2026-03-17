<?php

namespace App\Services;

use App\Helpers\QrCodeHelper;
use App\Models\Customer;
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

    public function getPublicCardLink(Customer $customer): array
    {
        $fileName = sprintf('membership-card-%s.pdf', $customer->id);
        $url = $this->buildPublicCardUrl($customer);
        $this->assertPublicHttpUrl($url);

        return [
            'file_name' => $fileName,
            'url' => $url,
        ];
    }

    public function sendViaWhatsapp(Customer $customer, ?string $target = null): array
    {
        $destination = $this->normalizePhone($target ?? $customer->phone);
        if ($destination === null) {
            throw new \RuntimeException('Nomor WhatsApp customer belum tersedia.');
        }

        $file = $this->getPublicCardLink($customer);
        $payload = $this->buildPayload($customer);

        $message = sprintf(
            "Halo %s, berikut kartu membership Anda dari %s.\n%s",
            $payload['customer_name'],
            config('app.name'),
            $file['url']
        );

        $result = $this->whatsappConfigService->sendMessage($destination, $message);

        return [
            'target' => $destination,
            'file_url' => $file['url'],
            'fonnte' => $result,
        ];
    }

    private function buildPayload(Customer $customer): array
    {
        $memberCode = trim((string) ($customer->code ?: ''));
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

    private function buildPublicCardUrl(Customer $customer): string
    {
        $routePath = ltrim((string) route('public.membership-cards.show', ['customer' => $customer->id], false), '/');
        $baseUrl = trim((string) config('services.fonnte.public_file_base_url', ''));

        if ($baseUrl !== '') {
            return $this->combineBaseUrlAndPath($baseUrl, $routePath);
        }

        $candidates = [];

        $forwardedHostRaw = trim((string) request()->headers->get('x-forwarded-host', ''));
        if ($forwardedHostRaw !== '') {
            $forwardedHost = trim(explode(',', $forwardedHostRaw)[0]);
            $forwardedProtoRaw = trim((string) request()->headers->get('x-forwarded-proto', ''));
            $forwardedProto = trim(explode(',', $forwardedProtoRaw !== '' ? $forwardedProtoRaw : request()->getScheme())[0]);

            if ($forwardedHost !== '') {
                $candidates[] = sprintf('%s://%s', $forwardedProto !== '' ? $forwardedProto : 'https', $forwardedHost);
            }
        }

        $requestRoot = trim((string) request()->getSchemeAndHttpHost());
        if ($requestRoot !== '') {
            $candidates[] = $requestRoot;
        }

        $appUrl = trim((string) config('app.url', ''));
        if ($appUrl !== '') {
            $candidates[] = $appUrl;
        }

        foreach ($candidates as $candidate) {
            if ($this->isHttpUrlWithPublicHost($candidate)) {
                return $this->combineBaseUrlAndPath($candidate, $routePath);
            }
        }

        foreach ($candidates as $candidate) {
            if ($this->isHttpUrl($candidate)) {
                return $this->combineBaseUrlAndPath($candidate, $routePath);
            }
        }

        return route('public.membership-cards.show', ['customer' => $customer->id]);
    }

    private function assertPublicHttpUrl(string $url): void
    {
        if (! $this->isHttpUrl($url)) {
            throw new \RuntimeException('URL kartu membership tidak valid untuk pengiriman WhatsApp.');
        }
    }

    private function combineBaseUrlAndPath(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
    }

    private function isHttpUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        return in_array($scheme, ['http', 'https'], true) && $host !== '';
    }

    private function isHttpUrlWithPublicHost(string $url): bool
    {
        if (! $this->isHttpUrl($url)) {
            return false;
        }

        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        if ($host === 'localhost' || str_ends_with($host, '.local')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return true;
    }
}
