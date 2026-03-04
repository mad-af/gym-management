<?php

namespace App\Models;

use App\Enums\AssetHistoryActionEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHistory extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'asset_id',
        'action',
        'data_before',
        'data_after',
        'performed_by',
    ];

    protected $casts = [
        'data_before' => 'array',
        'data_after' => 'array',
        'action' => AssetHistoryActionEnum::class,
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
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
