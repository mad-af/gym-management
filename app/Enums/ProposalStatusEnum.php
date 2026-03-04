<?php

namespace App\Enums;

enum ProposalStatusEnum: string
{
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Diajukan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CANCELED => 'Dibatalkan',
            self::COMPLETED => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SUBMITTED => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::APPROVED => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::REJECTED => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            self::CANCELED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            self::COMPLETED => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-500',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (ProposalStatusEnum $status) {
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
