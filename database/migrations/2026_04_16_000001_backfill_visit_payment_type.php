<?php

use App\Models\Visit;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereNotNull('price')
            ->whereNull('payment_type')
            ->update(['payment_type' => 'cash']);
    }

    public function down(): void
    {
        Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereNotNull('price')
            ->where('payment_type', 'cash')
            ->update(['payment_type' => null]);
    }
};
