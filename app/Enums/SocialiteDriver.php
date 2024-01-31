<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialiteDriver: string
{
    case Github = 'github';
    case Google = 'google';
}
