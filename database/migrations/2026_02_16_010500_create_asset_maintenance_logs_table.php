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
        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_id')->constrained('asset_maintenances')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignUuid('performed_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index('maintenance_id');
            $table->index('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
    }
};
