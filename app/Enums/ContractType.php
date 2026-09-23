<?php

namespace App\Enums;

enum ContractType: string
{
    case ILJ = 'ilj';
    case MAINTENANCE = 'maintenance';
    case GENERAL = 'general';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}