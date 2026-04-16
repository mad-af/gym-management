<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case CASH = 'cash';
    case DEBIT_CARD = 'debit_card';
    case CREDIT_CARD = 'credit_card';
    case E_WALLET = 'e_wallet';
    case QRIS = 'qris';
    case TRANSFER = 'transfer';

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
