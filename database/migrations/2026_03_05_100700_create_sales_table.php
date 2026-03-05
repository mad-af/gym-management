<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
