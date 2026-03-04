<?php

namespace App\Models;

use App\Enums\ProposalStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetProposal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'proposal_number',
        'opd_id',
        'proposed_by',
        'proposal_date',
        'category_id',
        'item_name',
        'specification',
        'qty',
        'estimated_price',
        'status',
        'total_estimation',
        'notes',
    ];

    protected $casts = [
        'proposal_date' => 'date',
        'estimated_price' => 'decimal:2',
        'total_estimation' => 'decimal:2',
        'status' => ProposalStatusEnum::class,
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function scopeOpd(Builder $query, ?string $opdId = null): Builder
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            $q->where('opd_id', $opdId);
        });
    }
}
