<?php

namespace App\Enums;

enum DisposalTypeEnum: string
{
    case DESTRUCTION = 'destruction';
    case AUCTION = 'auction';
    case GRANT = 'grant';
    case WRITE_OFF = 'write_off';

    public function label(): string
    {
        return match ($this) {
            self::DESTRUCTION => 'Pemusnahan',
            self::AUCTION => 'Lelang',
            self::GRANT => 'Hibah',
            self::WRITE_OFF => 'Penghapusan',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
