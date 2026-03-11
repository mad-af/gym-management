<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPackage extends Model
{
    use HasFactory, HasMedia, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'duration_days',
        'price',
        'description',
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

    public function items(): HasMany
    {
        return $this->hasMany(MembershipPackageItem::class, 'package_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class, 'package_id');
    }
}
