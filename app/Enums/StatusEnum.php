<?php

namespace App\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::EXPIRED => 'Expired',
        };
    }
}
