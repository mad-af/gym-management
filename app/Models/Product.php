<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasMedia, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'is_active',
        'created_at',
    ];

    protected $appends = [
        'cover',
    ];

    protected $hidden = [
        'media',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:0',
        'created_at' => 'datetime',
    ];

    /**
     * @return array{url: string, placeholder: ?string}|null
     */
    public function getCoverAttribute(): ?array
    {
        return $this->getFirstMedia('cover');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
