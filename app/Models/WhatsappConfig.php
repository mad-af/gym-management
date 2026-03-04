<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappConfig extends Model
{
    protected $fillable = [
        'name',
        'token',
        'phone',
        'is_connected',
        'connected_at',
        'quota',
        'expired',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'connected_at' => 'datetime',
    ];
}
