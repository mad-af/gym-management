<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('checkin_method');
            $table->foreignUuid('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
