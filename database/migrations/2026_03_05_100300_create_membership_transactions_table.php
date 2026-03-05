<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('package_id')->constrained('membership_packages')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 15, 2);
            $table->string('status')->default('ACTIVE');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index('customer_id');
            $table->index('package_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
    }
};
