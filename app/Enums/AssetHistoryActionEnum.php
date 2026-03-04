<?php

namespace App\Enums;

enum AssetHistoryActionEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Dibuat',
            self::UPDATED => 'Diperbarui',
            self::DELETED => 'Dihapus',
            self::RESTORED => 'Dipulihkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CREATED => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::UPDATED => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
            self::DELETED => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            self::RESTORED => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-500',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (AssetHistoryActionEnum $action) {
                return [
                    'value' => $action->value,
                    'label' => $action->label(),
                    'class' => $action->badgeClass(),
                ];
            })
            ->values()
            ->toArray();
    }
}
