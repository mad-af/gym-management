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
        Schema::create('disposal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disposal_document_id')
                ->constrained('disposal_documents')
                ->cascadeOnDelete();
            $table->foreignUuid('asset_id')
                ->constrained('assets')
                ->cascadeOnDelete();
            $table->text('reason');
            $table->string('condition_at_disposal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_items');
    }
};
