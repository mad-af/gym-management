<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type'); // IN | OUT | ADJUSTMENT
            $table->integer('quantity');
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
