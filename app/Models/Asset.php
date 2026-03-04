<?php

namespace App\Models;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetHistoryActionEnum;
use App\Enums\AssetStatusEnum;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Asset extends Model
{
    use HasFactory, HasMedia, HasUuids, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'category_id',
        'opd_id',
        'room_id',
        'employee_id',
        'funding_source_id',
        'condition',
        'purchase_date',
        'purchase_price',
        'status',
        'qr_id',
        'notes',
        'created_by',
    ];

    protected $appends = [
        'photo',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'status' => AssetStatusEnum::class,
        'condition' => AssetConditionEnum::class,
    ];

    protected static function booted(): void
    {
        static::created(function (self $asset): void {
            $asset->recordHistory(AssetHistoryActionEnum::CREATED, null, $asset->toArray());

            $asset->additionalInfo()->create([]);
        });

        static::updated(function (self $asset): void {
            $before = $asset->getOriginal();
            $after = $asset->getAttributes();

            if (! empty($asset->getChanges())) {
                $asset->recordHistory(AssetHistoryActionEnum::UPDATED, $before, $after);
            }
        });

        static::deleted(function (self $asset): void {
            $before = $asset->getOriginal();

            $asset->recordHistory(AssetHistoryActionEnum::DELETED, $before, null);
        });

        static::restored(function (self $asset): void {
            $before = $asset->getOriginal();
            $after = $asset->getAttributes();

            $asset->recordHistory(AssetHistoryActionEnum::RESTORED, $before, $after);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        });
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function additionalInfo(): HasOne
    {
        return $this->hasOne(AssetAdditionalInfo::class);
    }

    public function getPhotoAttribute(): ?array
    {
        return $this->getFirstMedia('photo');
    }

    protected function recordHistory(AssetHistoryActionEnum $action, ?array $before, ?array $after): void
    {
        AssetHistory::create([
            'asset_id' => $this->id,
            'action' => $action->value,
            'data_before' => $before,
            'data_after' => $after,
            'performed_by' => Auth::id(),
        ]);
    }
}
