<?php

namespace App\Enums;

enum ContractStatus: string
{
    case ACTIVE = 'active';
    case EXPIRING = 'expiring';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';
    case TERMINATED = 'terminated';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}