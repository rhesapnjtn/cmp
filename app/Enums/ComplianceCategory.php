<?php

namespace App\Enums;

enum ComplianceCategory: string
{
    case LEGAL = 'legal';
    case REGULATORY = 'regulatory';
    case INTERNAL = 'internal';
    case AUDIT = 'audit';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}