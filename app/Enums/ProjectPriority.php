<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumValuesGetter;

enum ProjectPriority: string
{
    use EnumValuesGetter;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
