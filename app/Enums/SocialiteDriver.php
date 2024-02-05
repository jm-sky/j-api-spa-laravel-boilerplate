<?php

declare(strict_types=1);

namespace DevMadeIt\Enums;

enum SocialiteDriver: string
{
    case Github = 'github';
    case Google = 'google';
}
