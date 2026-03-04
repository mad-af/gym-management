<?php

namespace App\Models;

use App\Enums\MaintenanceStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetMaintenance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'scheduled_date',
        'completed_date',
        'cost',
        'vendor',
        'description',
        'status',
        'created_by',
        'notification_sent_at',
        'condition_before',
        'condition_after',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
        'status' => MaintenanceStatusEnum::class,
        'notification_sent_at' => 'datetime',
        'condition_before' => \App\Enums\AssetConditionEnum::class,
        'condition_after' => \App\Enums\AssetConditionEnum::class,
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssetMaintenanceLog::class, 'maintenance_id');
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->whereHas('asset', function ($assetQuery) use ($opdId) {
                $assetQuery->where('opd_id', $opdId);
            });
        });
    }
}
