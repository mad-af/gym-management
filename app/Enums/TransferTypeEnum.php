<?php

namespace App\Enums;

enum TransferTypeEnum: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public function label(): string
    {
        return match ($this) {
            self::INTERNAL => 'Internal',
            self::EXTERNAL => 'Eksternal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INTERNAL => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
            self::EXTERNAL => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        };
    }

    public function isInternal(): bool
    {
        return $this === self::INTERNAL;
    }

    public function isExternal(): bool
    {
        return $this === self::EXTERNAL;
    }

    public function requiresApproval(): bool
    {
        return $this === self::EXTERNAL;
    }

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->map(function (TransferTypeEnum $type) {
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'class' => $type->badgeClass(),
                ];
            })
            ->values()
            ->toArray();
    }
}
