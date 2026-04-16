<?php

namespace App\Models;

use App\Enums\PaymentTypeEnum;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory, HasMedia, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'membership_transaction_id',
        'visit_type',
        'price',
        'payment_type',
        'checkin_method',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'created_by',
        'checkin_time',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'payment_type' => PaymentTypeEnum::class,
        'checkin_time' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'is_cancelled',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function membershipTransaction(): BelongsTo
    {
        return $this->belongsTo(MembershipTransaction::class, 'membership_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function scopeNotCancelled($query)
    {
        return $query->whereNull('cancelled_at');
    }
}
