<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_type')->default('CASH')->after('total_amount');
        });

        Schema::table('membership_transactions', function (Blueprint $table) {
            $table->string('payment_type')->default('CASH')->after('price');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });

        Schema::table('membership_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
