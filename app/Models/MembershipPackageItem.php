<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPackageItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'package_id',
        'item_name',
        'quantity',
        'unit',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'package_id');
    }
}
