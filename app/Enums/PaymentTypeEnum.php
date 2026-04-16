<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case CASH = 'CASH';
    case DEBIT_CARD = 'DEBIT_CARD';
    case CREDIT_CARD = 'CREDIT_CARD';
    case E_WALLET = 'E_WALLET';
    case QRIS = 'QRIS';
    case TRANSFER = 'TRANSFER';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::DEBIT_CARD => 'Debit Card',
            self::CREDIT_CARD => 'Credit Card',
            self::E_WALLET => 'E-Wallet',
            self::QRIS => 'QRIS',
            self::TRANSFER => 'Transfer',
        };
    }
}
