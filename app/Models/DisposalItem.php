<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposalItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'disposal_document_id',
        'asset_id',
        'reason',
        'condition_at_disposal',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DisposalDocument::class, 'disposal_document_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
