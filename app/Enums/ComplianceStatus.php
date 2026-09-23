<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case COMPLIANT = 'compliant';
    case PARTIAL = 'partial';
    case NON_COMPLIANT = 'non_compliant';
    case IN_PROGRESS = 'in_progress';
    case NA = 'na';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}