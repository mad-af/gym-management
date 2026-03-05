<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_package_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('package_id')->constrained('membership_packages')->cascadeOnDelete();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->index('package_id');
            $table->index('item_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_package_items');
    }
};
