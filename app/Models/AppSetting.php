<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory, HasMedia, HasUuids;

    protected $fillable = [
        'app_name',
    ];

    protected $appends = [
        'logo',
    ];

    protected $hidden = [
        'media',
    ];

    public function getLogoAttribute(): ?array
    {
        return $this->getFirstMedia('logo');
    }
}
