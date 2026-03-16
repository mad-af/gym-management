<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppSettingService
{
    public const TYPE_APP_NAME = 'app_name';

    public const TYPE_APP_DESCRIPTION = 'app_description';

    public const TYPE_APP_LOGO = 'app_logo';

    public const TYPE_DAILY_VISIT_PRICE = 'daily_visit_price';

    public const TYPE_WHATSAPP_CONFIG = 'whatsapp_config';

    private const DEFAULT_APP_DESCRIPTION = 'Kelola operasional gym Anda dengan lebih efisien.';

    private const DEFAULT_DAILY_VISIT_PRICE = 50000;

    private const WHATSAPP_CONFIG_DEFAULTS = [
        'token' => '',
        'name' => 'Main Device',
        'phone' => null,
        'is_connected' => false,
        'connected_at' => null,
        'quota' => null,
        'expired' => null,
    ];

    public function __construct(protected MediaService $mediaService) {}

    public function getByType(string $type, array $defaultData = []): AppSetting
    {
        $setting = AppSetting::findByType($type);
        if ($setting) {
            return $setting->load('media');
        }

        $setting = AppSetting::query()->create([
            'type' => $type,
            'data' => $defaultData,
        ]);

        return $setting->load('media');
    }

    public function setByType(string $type, array $data): AppSetting
    {
        return DB::transaction(function () use ($type, $data) {
            $setting = $this->getByType($type);
            $setting->data = $data;
            $setting->save();

            $freshSetting = $setting->fresh();

            return $freshSetting?->load('media') ?? $setting->load('media');
        });
    }

    public function getAppSettings(): array
    {
        $nameSetting = $this->getByType(self::TYPE_APP_NAME, [
            'value' => config('app.name'),
        ]);
        $descriptionSetting = $this->getByType(self::TYPE_APP_DESCRIPTION, [
            'value' => self::DEFAULT_APP_DESCRIPTION,
        ]);

        $logoSetting = $this->getByType(self::TYPE_APP_LOGO);
        $dailyVisitPriceSetting = $this->getByType(self::TYPE_DAILY_VISIT_PRICE, [
            'value' => self::DEFAULT_DAILY_VISIT_PRICE,
        ]);
        $appName = $this->normalizeNullableString($nameSetting->getValue('value')) ?? (string) config('app.name');
        $appDescription = $this->normalizeNullableString($descriptionSetting->getValue('value'))
            ?? self::DEFAULT_APP_DESCRIPTION;
        $dailyVisitPrice = $this->normalizeNonNegativeNumber(
            $dailyVisitPriceSetting->getValue('value'),
            self::DEFAULT_DAILY_VISIT_PRICE,
        );

        return [
            'id' => $nameSetting->id,
            'app_name' => $appName,
            'app_description' => $appDescription,
            'logo' => $logoSetting->getLogo(),
            'daily_visit_price' => $dailyVisitPrice,
        ];
    }

    public function saveAppSettings(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $appDescription = $this->normalizeNullableString($data['app_description'] ?? null)
                ?? self::DEFAULT_APP_DESCRIPTION;

            $nameSetting = $this->setByType(self::TYPE_APP_NAME, [
                'value' => $data['app_name'],
            ]);

            $descriptionSetting = $this->setByType(self::TYPE_APP_DESCRIPTION, [
                'value' => $appDescription,
            ]);

            $dailyVisitPrice = $this->normalizeNonNegativeNumber(
                $data['daily_visit_price'] ?? null,
                self::DEFAULT_DAILY_VISIT_PRICE,
            );
            $dailyVisitPriceSetting = $this->setByType(self::TYPE_DAILY_VISIT_PRICE, [
                'value' => $dailyVisitPrice,
            ]);

            $logoSetting = $this->getByType(self::TYPE_APP_LOGO);

            $logo = $data['logo'] ?? null;
            if ($logo instanceof UploadedFile) {
                $newLogo = $this->mediaService->upload($logo, $logoSetting, 'logo');

                $existingMedia = $logoSetting->media()
                    ->where('collection', 'logo')
                    ->where('id', '!=', $newLogo->id)
                    ->get();

                foreach ($existingMedia as $media) {
                    $this->mediaService->forceDelete($media);
                }
            }

            $nameSetting->load('media');
            $descriptionSetting->load('media');
            $dailyVisitPriceSetting->load('media');
            $logoSetting->load('media');

            return [
                'id' => $nameSetting->id,
                'app_name' => $this->normalizeNullableString($nameSetting->getValue('value')) ?? (string) config('app.name'),
                'app_description' => $this->normalizeNullableString($descriptionSetting->getValue('value'))
                    ?? self::DEFAULT_APP_DESCRIPTION,
                'logo' => $logoSetting->getLogo(),
                'daily_visit_price' => $this->normalizeNonNegativeNumber(
                    $dailyVisitPriceSetting->getValue('value'),
                    self::DEFAULT_DAILY_VISIT_PRICE,
                ),
            ];
        });
    }

    public function getDailyVisitPrice(): float
    {
        $setting = $this->getByType(self::TYPE_DAILY_VISIT_PRICE, [
            'value' => self::DEFAULT_DAILY_VISIT_PRICE,
        ]);

        return $this->normalizeNonNegativeNumber(
            $setting->getValue('value'),
            self::DEFAULT_DAILY_VISIT_PRICE,
        );
    }

    public function getWhatsappConfigData(): array
    {
        $setting = $this->getByType(self::TYPE_WHATSAPP_CONFIG, self::WHATSAPP_CONFIG_DEFAULTS);
        $data = is_array($setting->data) ? $setting->data : [];

        return $this->normalizeWhatsappConfigData($data);
    }

    public function setWhatsappConfigData(array $data): array
    {
        $current = $this->getWhatsappConfigData();
        $next = $this->normalizeWhatsappConfigData(array_merge($current, $data));

        $this->setByType(self::TYPE_WHATSAPP_CONFIG, $next);

        return $next;
    }

    private function normalizeWhatsappConfigData(array $data): array
    {
        $merged = array_merge(self::WHATSAPP_CONFIG_DEFAULTS, $data);

        $connectedAt = $merged['connected_at'];
        if ($connectedAt instanceof \DateTimeInterface) {
            $connectedAt = $connectedAt->format(DATE_ATOM);
        }

        return [
            'token' => trim((string) ($merged['token'] ?? '')),
            'name' => $this->normalizeNullableString($merged['name'] ?? null) ?? 'Main Device',
            'phone' => $this->normalizeNullableString($merged['phone'] ?? null),
            'is_connected' => (bool) ($merged['is_connected'] ?? false),
            'connected_at' => is_string($connectedAt) && trim($connectedAt) !== '' ? $connectedAt : null,
            'quota' => $this->normalizeNullableString($merged['quota'] ?? null),
            'expired' => $this->normalizeNullableString($merged['expired'] ?? null),
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function normalizeNonNegativeNumber(mixed $value, float|int $fallback = 0): float
    {
        if (! is_numeric($value)) {
            return (float) $fallback;
        }

        $number = (float) $value;

        return $number < 0 ? (float) $fallback : $number;
    }

    public function normalizeLegacyRows(): void
    {
        DB::transaction(function () {
            if (! Schema::hasColumn('app_settings', 'app_name')) {
                return;
            }

            $latestLegacy = AppSetting::query()
                ->whereNull('type')
                ->latest('updated_at')
                ->first();

            if ($latestLegacy) {
                $nameSetting = $this->getByType(self::TYPE_APP_NAME, [
                    'value' => config('app.name'),
                ]);

                $nameSetting->data = ['value' => $latestLegacy->app_name];
                $nameSetting->save();

                $logoSetting = $this->getByType(self::TYPE_APP_LOGO);

                Media::query()
                    ->where('mediable_type', AppSetting::class)
                    ->where('mediable_id', $latestLegacy->id)
                    ->where('collection', 'logo')
                    ->update(['mediable_id' => $logoSetting->id]);
            }

            $legacyIds = AppSetting::query()
                ->whereNull('type')
                ->pluck('id');

            if ($legacyIds->isNotEmpty()) {
                $legacyMedia = Media::query()
                    ->where('mediable_type', AppSetting::class)
                    ->whereIn('mediable_id', $legacyIds)
                    ->get();

                foreach ($legacyMedia as $media) {
                    $this->mediaService->forceDelete($media);
                }

                AppSetting::query()->whereIn('id', $legacyIds)->delete();
            }
        });
    }
}
