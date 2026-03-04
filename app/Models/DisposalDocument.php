<?php

namespace App\Models;

use App\Enums\DisposalTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisposalDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'disposal_number',
        'opd_id',
        'disposal_type',
        'disposal_date',
        'created_by',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'disposal_type' => DisposalTypeEnum::class,
        'disposal_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DisposalItem::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
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
}
