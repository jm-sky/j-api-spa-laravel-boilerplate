<?php

declare(strict_types=1);

namespace DevMadeIt\Enums;

use DevMadeIt\Traits\EnumValuesGetter;

enum ProjectPriority: string
{
    use EnumValuesGetter;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
