<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AppSettingService
{
    protected const SETTINGS_ID = '00000000-0000-0000-0000-000000000001';

    public function __construct(protected MediaService $mediaService) {}

    public function getSettings(): AppSetting
    {
        $setting = AppSetting::query()->firstOrCreate(['id' => self::SETTINGS_ID], [
            'app_name' => config('app.name'),
        ]);

        return $setting->load('media');
    }

    public function saveSettings(array $data): AppSetting
    {
        return DB::transaction(function () use ($data) {
            $setting = $this->getSettings();

            $setting->fill(Arr::only($data, ['app_name']));
            $setting->save();

            $logo = $data['logo'] ?? null;
            if ($logo instanceof UploadedFile) {
                $newLogo = $this->mediaService->upload($logo, $setting, 'logo');

                $existingMedia = $setting->media()
                    ->where('collection', 'logo')
                    ->where('id', '!=', $newLogo->id)
                    ->get();

                foreach ($existingMedia as $media) {
                    $this->mediaService->forceDelete($media);
                }
            }

            $freshSetting = $setting->fresh();

            return $freshSetting->load('media');
        });
    }
}
