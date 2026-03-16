<?php

namespace Database\Seeders;

use App\Services\AppSettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $service = app(AppSettingService::class);

            $defaults = [
                AppSettingService::TYPE_APP_NAME => [
                    'value' => config('app.name'),
                ],
                AppSettingService::TYPE_APP_DESCRIPTION => [
                    'value' => 'Kelola operasional gym Anda dengan lebih efisien.',
                ],
                AppSettingService::TYPE_APP_LOGO => [],
                AppSettingService::TYPE_WHATSAPP_CONFIG => [
                    'token' => '',
                    'name' => 'Main Device',
                    'phone' => null,
                    'is_connected' => false,
                    'connected_at' => null,
                    'quota' => null,
                    'expired' => null,
                ],
            ];

            foreach ($defaults as $type => $data) {
                $service->getByType($type, $data);
            }
        });
    }
}
