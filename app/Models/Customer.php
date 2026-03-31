<?php

namespace App\Models;

use App\Traits\HasMedia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasMedia, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'avatar',
        'is_active_member',
        'active_membership_until',
        'active_membership_package_name',
        'active_membership_package_id',
        'days_remaining',
        'is_expiring_soon',
    ];

    protected $hidden = [
        'media',
    ];

    public function membershipTransactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getAvatarAttribute(): ?array
    {
        return $this->getFirstMedia('avatar');
    }

    public function getIsActiveMemberAttribute(): bool
    {
        $now = Carbon::now()->startOfDay();

        if ($this->relationLoaded('membershipTransactions')) {
            return $this->membershipTransactions
                ->contains(fn ($t) => $t->start_date && $t->end_date && $t->start_date->startOfDay() <= $now && $t->end_date->startOfDay() >= $now);
        }

        return $this->membershipTransactions()
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->exists();
    }

    public function getActiveMembershipUntilAttribute(): ?string
    {
        $now = Carbon::now()->startOfDay();
        $active = null;

        if ($this->relationLoaded('membershipTransactions')) {
            $active = $this->membershipTransactions
                ->filter(fn ($t) => $t->start_date && $t->end_date && $t->start_date->startOfDay() <= $now && $t->end_date->startOfDay() >= $now)
                ->sortByDesc('end_date')
                ->first();
        } else {
            $active = $this->membershipTransactions()
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now)
                ->orderByDesc('end_date')
                ->first();
        }

        return $active?->end_date?->toDateString();
    }

    public function getActiveMembershipPackageNameAttribute(): ?string
    {
        $now = Carbon::now()->startOfDay();
        $active = null;

        if ($this->relationLoaded('membershipTransactions')) {
            $active = $this->membershipTransactions
                ->filter(fn ($t) => $t->start_date && $t->end_date && $t->start_date->startOfDay() <= $now && $t->end_date->startOfDay() >= $now)
                ->sortByDesc('end_date')
                ->first();
        } else {
            $active = $this->membershipTransactions()
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now)
                ->with('package')
                ->orderByDesc('end_date')
                ->first();
        }

        return $active?->package?->name;
    }

    public function getActiveMembershipPackageIdAttribute(): ?string
    {
        $now = Carbon::now()->startOfDay();
        $active = null;

        if ($this->relationLoaded('membershipTransactions')) {
            $active = $this->membershipTransactions
                ->filter(fn ($t) => $t->start_date && $t->end_date && $t->start_date->startOfDay() <= $now && $t->end_date->startOfDay() >= $now)
                ->sortByDesc('end_date')
                ->first();
        } else {
            $active = $this->membershipTransactions()
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now)
                ->orderByDesc('end_date')
                ->first();
        }

        return $active?->package_id;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->active_membership_until) {
            return null;
        }

        $endDate = Carbon::parse($this->active_membership_until);
        $today = Carbon::today();

        return (int) $today->diffInDays($endDate, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_remaining;

        return $this->is_active_member && $days !== null && $days >= 0 && $days <= 7;
    }
}
