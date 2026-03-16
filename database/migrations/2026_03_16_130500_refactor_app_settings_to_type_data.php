<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        if (! Schema::hasColumn('app_settings', 'type')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->string('type')->nullable();
            });
        }

        if (! Schema::hasColumn('app_settings', 'data')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->json('data')->nullable();
            });
        }

        if (Schema::hasColumn('app_settings', 'app_name')) {
            $latest = DB::table('app_settings')->orderByDesc('updated_at')->first();

            if ($latest) {
                DB::table('app_settings')
                    ->where('id', $latest->id)
                    ->update([
                        'type' => 'app_name',
                        'data' => json_encode(['value' => $latest->app_name]),
                    ]);

                DB::table('app_settings')->where('id', '!=', $latest->id)->delete();
            }

            try {
                Schema::table('app_settings', function (Blueprint $table) {
                    $table->dropColumn('app_name');
                });
            } catch (Throwable) {
            }
        }

        try {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->unique('type');
            });
        } catch (Throwable) {
        }
    }

    public function down(): void {}
};
