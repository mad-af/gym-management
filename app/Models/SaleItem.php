<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'capital_price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'capital_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getProfitAttribute(): int
    {
        if ($this->capital_price === null) {
            return (int) $this->subtotal;
        }

        return (int) (($this->price - $this->capital_price) * $this->quantity);
    }
}
