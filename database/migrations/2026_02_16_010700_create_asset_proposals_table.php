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
        Schema::create('asset_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('proposal_number')->unique();
            $table->foreignUuid('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignUuid('proposed_by')->constrained('users');
            $table->date('proposal_date');
            $table->foreignUuid('category_id')->constrained('asset_categories');
            $table->string('item_name');
            $table->text('specification')->nullable();
            $table->unsignedInteger('qty');
            $table->decimal('estimated_price', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->decimal('total_estimation', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('opd_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('proposal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_proposals');
    }
};
