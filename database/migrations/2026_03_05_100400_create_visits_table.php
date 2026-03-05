<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('membership_transaction_id')->nullable()->constrained('membership_transactions')->nullOnDelete();
            $table->string('visit_type')->default('DAILY');
            $table->decimal('price', 15, 2)->nullable();
            $table->string('checkin_method')->default('MANUAL');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamp('checkin_time')->useCurrent();

            $table->index('customer_id');
            $table->index('membership_transaction_id');
            $table->index('visit_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
