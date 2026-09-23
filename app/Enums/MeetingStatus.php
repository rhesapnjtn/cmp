<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case SCHEDULED = 'scheduled';
    case DONE = 'done';
    case MISSED = 'missed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}