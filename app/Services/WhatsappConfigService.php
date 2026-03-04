<?php

namespace App\Services;

use App\Models\WhatsappConfig;
use App\Services\Fonnte\FonnteService;
use Illuminate\Support\Facades\DB;

class WhatsappConfigService
{
    public function __construct(protected FonnteService $fonnteService) {}

    public function getConfig(): ?WhatsappConfig
    {
        return WhatsappConfig::first();
    }

    public function saveConfig(array $data): WhatsappConfig
    {
        return DB::transaction(function () use ($data) {
            $config = WhatsappConfig::firstOrNew();

            $config->token = $data['token'];

            try {
                $response = $this->fonnteService->getDevice($config->token);
                $result = $response->json();

                if (isset($result['status']) && $result['status']) {
                    $name = trim($result['name'] ?? '');
                    $device = trim($result['device'] ?? '');
                    $deviceStatus = trim($result['device_status'] ?? '');

                    $config->name = ! empty($name) ? $name : 'Main Device';
                    $config->phone = ! empty($device) ? $device : null;

                    $isConnected = $deviceStatus === 'connect';
                    $config->is_connected = $isConnected;
                    $config->connected_at = $isConnected ? now() : null;

                    $config->quota = trim($result['quota'] ?? '');
                    $config->expired = trim($result['expired'] ?? '');
                }
            } catch (\Exception $e) {
                // If API fails, fallback to default behavior
            }

            // Fallback for name if not set from API
            if (! $config->name) {
                $config->name = 'Main Device';
            }

            // Reset status if token changed and API didn't confirm connection
            if ($config->isDirty('token') && ! $config->isDirty('is_connected')) {
                $config->is_connected = false;
                $config->connected_at = null;
                $config->phone = null;
            }

            $config->save();

            return $config;
        });
    }

    public function resetConfig(): void
    {
        WhatsappConfig::truncate();
    }

    public function sendMessage(string $target, string $message): array
    {
        $config = $this->getConfig();

        if (! $config || ! $config->token) {
            throw new \Exception('WhatsApp token is not configured.');
        }

        $response = $this->fonnteService->sendMessage($target, $message, [], $config->token);
        $data = $response->json();

        if (isset($data['status']) && ! $data['status']) {
            throw new \Exception($data['reason'] ?? 'Failed to send message.');
        }

        return $data;
    }

    public function sendTestMessage(string $target, string $message): array
    {
        return $this->sendMessage($target, $message);
    }

    public function getQr(): array
    {
        $config = $this->getConfig();

        if (! $config || ! $config->token) {
            throw new \Exception('WhatsApp token is not configured.');
        }

        $response = $this->fonnteService->getQr('qr', null, $config->token);
        $data = $response->json();

        if (isset($data['status']) && ! $data['status']) {
            $reason = strtolower($data['reason'] ?? '');
            if (str_contains($reason, 'connected')) {
                // If already connected, trigger check to update status in DB
                $this->checkConnection();

                return [
                    'status' => true,
                    'message' => 'Device already connected.',
                    'already_connected' => true,
                ];
            }

            throw new \Exception($data['reason'] ?? 'Failed to get QR code.');
        }

        return $data;
    }

    public function checkConnection(): array
    {
        $config = $this->getConfig();

        if (! $config || ! $config->token) {
            return [
                'status' => false,
                'message' => 'WhatsApp token is not configured.',
                'connected' => false,
            ];
        }

        try {
            $response = $this->fonnteService->getDevice($config->token);
            $result = $response->json();

            if (isset($result['status']) && $result['status']) {
                $name = trim($result['name'] ?? '');
                $device = trim($result['device'] ?? '');
                $deviceStatus = trim($result['device_status'] ?? '');

                $config->name = ! empty($name) ? $name : ($config->name ?? 'Main Device');
                $config->phone = ! empty($device) ? $device : null;

                $isConnected = $deviceStatus === 'connect';

                $config->is_connected = $isConnected;
                $config->connected_at = $isConnected ? ($config->connected_at ?? now()) : null;

                $config->quota = trim($result['quota'] ?? '');
                $config->expired = trim($result['expired'] ?? '');

                $config->save();

                return [
                    'status' => true,
                    'message' => 'Connection checked successfully.',
                    'connected' => $isConnected,
                    'data' => $config,
                ];
            }

            // If API call success but data invalid or status false
            return [
                'status' => false,
                'message' => 'Failed to retrieve device status.',
                'connected' => false,
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error checking connection: '.$e->getMessage(),
                'connected' => false,
            ];
        }
    }

    public function updateStatus(bool $isConnected, ?string $phone = null): void
    {
        $config = $this->getConfig();

        if ($config) {
            $config->update([
                'is_connected' => $isConnected,
                'connected_at' => $isConnected ? now() : null,
                'phone' => $phone ?? $config->phone,
            ]);
        }
    }
}
