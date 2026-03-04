<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('qr_id', 21)->unique()->nullable()->after('status');
        });

        DB::table('rooms')
            ->whereNull('qr_id')
            ->orderBy('id')
            ->chunkById(100, function ($rooms) {
                foreach ($rooms as $room) {
                    DB::table('rooms')
                        ->where('id', $room->id)
                        ->update(['qr_id' => Str::random(12)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('qr_id');
        });
    }
};
