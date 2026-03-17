<?php

namespace App\Services;

use App\Services\Fonnte\FonnteService;

class WhatsappConfigService
{
    public function __construct(
        protected FonnteService $fonnteService,
        protected AppSettingService $appSettingService,
    ) {}

    public function getConfig(): ?array
    {
        $config = $this->appSettingService->getWhatsappConfigData();

        return $config['token'] === '' ? null : $config;
    }

    public function saveConfig(array $data): array
    {
        $token = trim((string) ($data['token'] ?? ''));
        $currentConfig = $this->appSettingService->getWhatsappConfigData();
        $tokenChanged = $token !== $currentConfig['token'];

        $payload = [
            'token' => $token,
        ];

        if ($tokenChanged) {
            $payload['is_connected'] = false;
            $payload['connected_at'] = null;
            $payload['phone'] = null;
        }

        try {
            $response = $this->fonnteService->getDevice($token);
            $result = $response->json();

            if (isset($result['status']) && $result['status']) {
                $name = trim((string) ($result['name'] ?? ''));
                $device = trim((string) ($result['device'] ?? ''));
                $deviceStatus = trim((string) ($result['device_status'] ?? ''));

                $isConnected = $deviceStatus === 'connect';

                $payload['name'] = $name !== '' ? $name : 'Main Device';
                $payload['phone'] = $device !== '' ? $device : null;
                $payload['is_connected'] = $isConnected;
                $payload['connected_at'] = $isConnected ? now()->toIso8601String() : null;
                $payload['quota'] = trim((string) ($result['quota'] ?? '')) ?: null;
                $payload['expired'] = trim((string) ($result['expired'] ?? '')) ?: null;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->appSettingService->setWhatsappConfigData($payload);
    }

    public function resetConfig(): void
    {
        $this->appSettingService->setWhatsappConfigData([
            'token' => '',
            'name' => 'Main Device',
            'phone' => null,
            'is_connected' => false,
            'connected_at' => null,
            'quota' => null,
            'expired' => null,
        ]);
    }

    public function sendMessage(string $target, string $message, array $options = []): array
    {
        $config = $this->getConfig();

        if (! $config || ! $config['token']) {
            throw new \Exception('WhatsApp token is not configured.');
        }

        $response = $this->fonnteService->sendMessage($target, $message, $options, $config['token']);
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

        if (! $config || ! $config['token']) {
            throw new \Exception('WhatsApp token is not configured.');
        }

        $response = $this->fonnteService->getQr('qr', null, $config['token']);
        $data = $response->json();

        if (isset($data['status']) && ! $data['status']) {
            $reason = strtolower($data['reason'] ?? '');
            if (str_contains($reason, 'already') && str_contains($reason, 'connect')) {
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

        if (! $config || ! $config['token']) {
            return [
                'status' => false,
                'message' => 'WhatsApp token is not configured.',
                'connected' => false,
            ];
        }

        try {
            $response = $this->fonnteService->getDevice($config['token']);
            $result = $response->json();

            if (isset($result['status']) && $result['status']) {
                $name = trim((string) ($result['name'] ?? ''));
                $device = trim((string) ($result['device'] ?? ''));
                $deviceStatus = trim((string) ($result['device_status'] ?? ''));

                $isConnected = $deviceStatus === 'connect';

                $updatedConfig = $this->appSettingService->setWhatsappConfigData([
                    'name' => $name !== '' ? $name : ($config['name'] ?? 'Main Device'),
                    'phone' => $device !== '' ? $device : null,
                    'is_connected' => $isConnected,
                    'connected_at' => $isConnected
                        ? ($config['connected_at'] ?? now()->toIso8601String())
                        : null,
                    'quota' => trim((string) ($result['quota'] ?? '')) ?: null,
                    'expired' => trim((string) ($result['expired'] ?? '')) ?: null,
                ]);

                return [
                    'status' => true,
                    'message' => 'Connection checked successfully.',
                    'connected' => $isConnected,
                    'data' => $updatedConfig,
                ];
            }

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
            $this->appSettingService->setWhatsappConfigData([
                'is_connected' => $isConnected,
                'connected_at' => $isConnected ? now()->toIso8601String() : null,
                'phone' => $phone ?? $config['phone'],
            ]);
        }
    }
}
