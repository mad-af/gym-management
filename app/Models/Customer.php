<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'qr_code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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
}
