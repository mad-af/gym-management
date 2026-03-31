<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipTransaction extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'package_id',
        'start_date',
        'end_date',
        'price',
        'status',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'days_remaining',
        'is_expiring_soon',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        $endDate = $this->end_date instanceof Carbon
            ? $this->end_date
            : Carbon::parse($this->end_date);

        return (int) Carbon::today()->diffInDays($endDate, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_remaining;

        return $this->status === 'active' && $days !== null && $days >= 0 && $days <= 7;
    }
}
