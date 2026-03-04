<?php

namespace App\Enums;

enum MaintenanceStatusEnum: string
{
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Dijadwalkan',
            self::IN_PROGRESS => 'Sedang Berlangsung',
            self::COMPLETED => 'Selesai',
            self::CANCELED => 'Dibatalkan',
            self::OVERDUE => 'Terlambat',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SCHEDULED => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::IN_PROGRESS => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-500',
            self::COMPLETED => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::CANCELED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            self::OVERDUE => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (MaintenanceStatusEnum $status) {
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
