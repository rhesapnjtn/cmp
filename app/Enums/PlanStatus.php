<?php

namespace App\Enums;

enum PlanStatus: string
{
    case PLAN = 'plan';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}