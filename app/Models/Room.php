<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'opd_id',
        'name',
        'code',
        'status',
        'qr_id',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StatusEnum::ACTIVE);
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        });
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
