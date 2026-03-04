<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory, HasMedia, HasUuids;

    protected $fillable = [
        'nip',
        'name',
        'opd_id',
        'position',
        'status',
    ];

    protected $appends = [
        'avatar',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        });
    }

    /**
     * Get the employee's avatar URL and Placeholder.
     *
     * @return array{url: string, placeholder: ?string}|null
     */
    public function getAvatarAttribute(): ?array
    {
        return $this->getFirstMedia('avatar');
    }
}
