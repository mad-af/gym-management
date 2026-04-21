<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case CASH = 'CASH';
    case QRIS = 'QRIS';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::QRIS => 'QRIS',
        };
    }
}
