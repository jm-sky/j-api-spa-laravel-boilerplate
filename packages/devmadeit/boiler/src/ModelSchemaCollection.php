<?php

namespace DevMadeIt\Boiler;

use DevMadeIt\Boiler\ColumnSchema;
use Illuminate\Support\Collection;

class ModelSchemaCollection extends Collection
{
    function __construct(array $items = [])
    {
        $this->items = collect($items)->map(fn ($item): ColumnSchema => ColumnSchema::fromArray((array) $item))->toArray();
    }

    public static function fromArray(array $items = []): static
    {
        return new static($items);
    }
}
