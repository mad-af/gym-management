<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_maintenances', 'condition_before')) {
                $table->string('condition_before')->nullable()->after('status');
            }

            if (! Schema::hasColumn('asset_maintenances', 'condition_after')) {
                $table->string('condition_after')->nullable()->after('condition_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('asset_maintenances', 'condition_after')) {
                $table->dropColumn('condition_after');
            }

            if (Schema::hasColumn('asset_maintenances', 'condition_before')) {
                $table->dropColumn('condition_before');
            }
        });
    }
};
