<?php

namespace App\Console\Commands\Boil;

/**
 * @property string $table_name
 * @property string $column_name
 * @property bool $is_nullable
 * @property string $data_type
 * @property int $character_maximum_length
 * @property int $character_octet_length
 * @property int $numeric_precision
 * @property int $numeric_precision_radix
 * @property int $numeric_scale
 * @property string $referenced_table_name
 * @property string $referenced_column_name
 */
class BoilColumnDefinition
{
    function __construct(
        public string $table_name,
        public string $column_name,
        public bool $is_nullable,
        public string $data_type,
        public int $character_maximum_length,
        public int $character_octet_length,
        public int $numeric_precision,
        public int $numeric_precision_radix,
        public int $numeric_scale,
        public string $referenced_table_name,
        public string $referenced_column_name,
    )
    {}
}
