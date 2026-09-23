<?php

namespace App\Enums;

enum RiskCategory: string
{
    case OPERATIONAL = 'operational';
    case STRATEGIC = 'strategic';
    case FINANCIAL = 'financial';
    case COMPLIANCE = 'compliance';
    case ENVIRONMENTAL = 'environmental';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}