<?php

namespace App\Models;

use App\Enums\TransferStatusEnum;
use App\Enums\TransferTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'type',
        'from_opd_id',
        'to_opd_id',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'type' => TransferTypeEnum::class,
        'status' => TransferStatusEnum::class,
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_badge_class',
        'type_label',
        'type_badge_class',
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status->badgeClass();
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type->badgeClass();
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function fromOpd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'from_opd_id');
    }

    public function toOpd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'to_opd_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->where(function ($subQuery) use ($opdId) {
                $subQuery->where('from_opd_id', $opdId)
                    ->orWhere('to_opd_id', $opdId);
            });
        });
    }
}
