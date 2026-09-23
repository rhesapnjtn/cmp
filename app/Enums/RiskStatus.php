<?php

namespace App\Enums;

enum RiskStatus: string
{
    case IDENTIFIED = 'identified';
    case ASSESSED = 'assessed';
    case MITIGATED = 'mitigated';
    case MONITORED = 'monitored';
    case CLOSED = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}