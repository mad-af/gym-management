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
        Schema::create('disposal_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('disposal_number')->unique();
            $table->foreignUuid('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->string('disposal_type');
            $table->date('disposal_date');
            $table->foreignUuid('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('opd_id');
            $table->index('disposal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_documents');
    }
};
