<?php

namespace App\Enums;

enum UnitType: string
{
    case FUNCTION = 'function';
    case DEPARTMENT = 'department';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}