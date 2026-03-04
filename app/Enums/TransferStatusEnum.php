<?php

namespace App\Enums;

enum TransferStatusEnum: string
{
    case PENDING = 'pending';
    case CANCEL = 'cancel';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CANCEL => 'Dibatalkan',
            self::REJECTED => 'Ditolak',
            self::COMPLETED => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::CANCEL => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            self::REJECTED => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            self::COMPLETED => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
        };
    }

    public function isFinal(): bool
    {
        return $this === self::REJECTED || $this === self::COMPLETED || $this === self::CANCEL;
    }

    public function canBeApproved(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeRejected(): bool
    {
        return $this === self::PENDING;
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (TransferStatusEnum $status) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'class' => $status->badgeClass(),
                ];
            })
            ->values()
            ->toArray();
    }
}
