<?php

namespace App\Enums;

enum HsseLevel: int
{
    case ONE = 1;
    case TWO = 2;
    case THREE = 3;

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}