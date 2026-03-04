<?php

namespace App\Enums;

enum AssetStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case DISPOSED = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::UNDER_MAINTENANCE => 'Dalam Perawatan',
            self::DISPOSED => 'Dibuang',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::INACTIVE => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            self::UNDER_MAINTENANCE => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::DISPOSED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (AssetStatusEnum $status) {
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
