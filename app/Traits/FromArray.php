<?php

declare(strict_types=1);

namespace DevMadeIt\Traits;

trait FromArray
{
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }
}
