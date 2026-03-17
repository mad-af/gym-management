<?php

namespace App\Services\Fonnte;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->baseUrl = Config::get('services.fonnte.base_url', 'https://api.fonnte.com');
        $this->token = Config::get('services.fonnte.token');
    }

    /**
     * Set the API token dynamically.
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Add new device.
     * Note: Requires Account Token.
     */
    public function addDevice(string $name, string $device, bool $autoread = false, bool $personal = false, bool $group = false, ?string $accountToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $accountToken ?? $this->token,
        ])->post("{$this->baseUrl}/add-device", [
            'name' => $name,
            'device' => $device,
            'autoread' => $autoread,
            'personal' => $personal,
            'group' => $group,
        ]);
    }

    /**
     * Update existing device.
     */
    public function updateDevice(array $data, ?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/update-device", $data);
    }

    /**
     * Delete device.
     */
    public function deleteDevice(string $otp, ?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/delete-device", [
            'otp' => $otp,
        ]);
    }

    /**
     * Create order for device plan.
     */
    public function createOrder(array $data, ?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/order", $data);
    }

    /**
     * Get QR or pairing code to connect device.
     */
    public function getQr(string $type = 'qr', ?string $whatsapp = null, ?string $deviceToken = null): Response
    {
        $payload = ['type' => $type];
        if ($whatsapp && $type === 'code') {
            $payload['whatsapp'] = $whatsapp;
        }

        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/qr", $payload);
    }

    /**
     * Send WhatsApp message.
     */
    public function sendMessage(string $target, string $message, array $options = [], ?string $deviceToken = null): Response
    {
        $payload = array_merge([
            'target' => $target,
            'message' => $message,
        ], $options);

        $filePath = $payload['file_path'] ?? null;
        unset($payload['file_path']);

        $request = Http::withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ]);

        if (is_string($filePath) && $filePath !== '') {
            if (! is_file($filePath) || ! is_readable($filePath)) {
                throw new \RuntimeException('Unable to read WhatsApp attachment file.');
            }

            unset($payload['url']);

            $stream = fopen($filePath, 'rb');
            if ($stream === false) {
                throw new \RuntimeException('Unable to open WhatsApp attachment file.');
            }

            $uploadName = is_string($payload['filename'] ?? null) && ($payload['filename'] ?? '') !== ''
                ? (string) $payload['filename']
                : basename($filePath);

            $mimeType = $this->detectMimeType($filePath);

            try {
                return $request
                    ->attach('file', $stream, $uploadName, ['Content-Type' => $mimeType])
                    ->post("{$this->baseUrl}/send", $payload);
            } finally {
                fclose($stream);
            }
        }

        return $request->asForm()->post("{$this->baseUrl}/send", $payload);
    }

    private function detectMimeType(string $filePath): string
    {
        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            default => 'application/octet-stream',
        };
    }

    /**
     * Validate phone numbers.
     */
    public function validate(string $target, int $countryCode = 62, ?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/validate", [
            'target' => $target,
            'countryCode' => $countryCode,
        ]);
    }

    /**
     * Delete message.
     */
    public function deleteMessage(int $id, ?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/delete-message", [
            'id' => $id,
        ]);
    }

    /**
     * Send or stop typing indicator.
     */
    public function setTyping(string $target, ?int $duration = null, bool $stop = false, ?string $deviceToken = null): Response
    {
        $payload = ['target' => $target];

        if ($stop) {
            $payload['stop'] = true;
        } else {
            $payload['duration'] = $duration;
        }

        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/typing", $payload);
    }

    /**
     * Get device information.
     */
    public function getDevice(?string $deviceToken = null): Response
    {
        return Http::asForm()->withHeaders([
            'Authorization' => $deviceToken ?? $this->token,
        ])->post("{$this->baseUrl}/device");
    }
}
