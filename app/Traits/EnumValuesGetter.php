<?php

declare(strict_types=1);

namespace DevMadeIt\Traits;

trait EnumValuesGetter
{
    public static function values(): array
    {
        return array_column(self::cases(), "value");
    }
}
