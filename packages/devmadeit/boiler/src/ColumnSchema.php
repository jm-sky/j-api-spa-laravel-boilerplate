<?php

namespace DevMadeIt\Boiler;

/**
 * @property string $name
 * @property string $table_name
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
class ColumnSchema
{
    function __construct(
        public string $name,
        public string $table_name,
        public bool $is_nullable,
        public string $data_type,
        public ?int $character_maximum_length,
        public ?int $character_octet_length,
        public ?int $numeric_precision,
        public ?int $numeric_precision_radix,
        public ?int $numeric_scale,
        public ?string $referenced_table_name,
        public ?string $referenced_column_name,
    )
    {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            table_name: $data['table_name'],
            is_nullable: self::boolean($data['is_nullable']),
            data_type: $data['data_type'],
            character_maximum_length: $data['character_maximum_length'],
            character_octet_length: $data['character_octet_length'],
            numeric_precision: $data['numeric_precision'],
            numeric_precision_radix: $data['numeric_precision_radix'],
            numeric_scale: $data['numeric_scale'],
            referenced_table_name: $data['referenced_table_name'],
            referenced_column_name: $data['referenced_column_name'],
        );
    }

    public function getPhpType(): string
    {
        return match ($this->data_type) {
            'big_int', 'bigint', 'tinyint', 'int' => 'int',
            'float', 'decimal', 'double' => 'float',
            'datetime', 'date' => 'string',
            default => 'string',
        };
    }

    public function getTsType(): string
    {
        return match ($this->data_type) {
            'big_int', 'bigint', 'tinyint', 'int', 'float', 'decimal', 'double' => 'number',
            'datetime', 'date' => 'string',
            default => 'string',
        };
    }

    protected static function boolean(mixed $value): bool
    {
        return match ($value) {
            true, 'true', 'yes', 'YES' => true,
            true, 'true', 'no', 'NO' => false,
            default => false,
        };
    }
}
