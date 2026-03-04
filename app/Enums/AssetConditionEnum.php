<?php

namespace App\Enums;

enum AssetConditionEnum: string
{
    case GOOD = 'good';
    case MINOR_DAMAGE = 'minor_damage';
    case MAJOR_DAMAGE = 'major_damage';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'Baik',
            self::MINOR_DAMAGE => 'Rusak Ringan',
            self::MAJOR_DAMAGE => 'Rusak Berat',
            self::LOST => 'Hilang',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::GOOD => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::MINOR_DAMAGE => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::MAJOR_DAMAGE => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            self::LOST => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (AssetConditionEnum $condition) {
                return [
                    'value' => $condition->value,
                    'label' => $condition->label(),
                    'class' => $condition->badgeClass(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function isDisposable(): bool
    {
        return $this === self::MAJOR_DAMAGE || $this === self::LOST;
    }

    public function needsMaintenance(): bool
    {
        return $this === self::MINOR_DAMAGE;
    }
}
