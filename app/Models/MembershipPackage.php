<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPackage extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'duration_days',
        'price',
        'description',
        'is_active',
        'created_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:0',
        'created_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MembershipPackageItem::class, 'package_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class, 'package_id');
    }
}
