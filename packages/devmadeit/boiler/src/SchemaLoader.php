<?php

namespace DevMadeIt\Boiler;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use DevMadeIt\Boiler\ColumnSchema;
use DevMadeIt\Boiler\ModelSchemaCollection;
use DevMadeIt\Boiler\Exceptions\BoilerException;

class SchemaLoader
{
    public ModelSchemaCollection $columns;

    public function __construct(
        public string $modelClassName,
    )
    {}

    /**
     * @return Collection<ColumnSchema>
     */
    public function getColumns(): Collection
    {
        $query = $this->getQuery();
        $columns = ModelSchemaCollection::fromArray(DB::select($query));

        return $columns;
    }

    protected function getTable(): string
    {
        $modelInstance = new $this->modelClassName;

        return $modelInstance->getTable();
    }

    /**
     * @throws BoilerException
     */
    protected function getQuery(): string
    {
        $table = $this->getTable();
        $driver = DB::getDriverName();
        $schema = DB::getDatabaseName();

        if ($driver == 'mysql') {
            return <<<SQL
            SELECT t.table_name as "table_name"
                    , c.column_name as "name"
                    , c.is_nullable as "is_nullable"
                    , c.data_type as "data_type"
                    , c.character_maximum_length as "character_maximum_length"
                    , c.character_octet_length as "character_octet_length"
                    , c.numeric_precision as "numeric_precision"
                    , null as "numeric_precision_radix"
                    , c.numeric_scale as "numeric_scale"
                    , fk.referenced_table_name as "referenced_table_name"
                    , fk.referenced_column_name as "referenced_column_name"
            FROM information_schema.tables t
            JOIN information_schema.columns c ON c.table_name = t.table_name AND c.table_catalog = t.table_catalog
            LEFT JOIN LATERAL (
                SELECT referenced_table_name, referenced_column_name
                FROM information_schema.key_column_usage kcu
                WHERE kcu.table_schema = t.table_schema
                    AND kcu.table_name = t.table_name
                    AND kcu.column_name = c.column_name
                LIMIT 1
            ) fk ON true
            WHERE t.table_schema = '$schema'
                AND t.table_name = '$table'
            ORDER BY c.ORDINAL_POSITION
            SQL;
        }

        if ($driver == 'pgsql') {
            return <<<SQL
            SELECT t.table_name as "table_name"
                    , c.column_name as "name"
                    , c.is_nullable as "is_nullable"
                    , c.data_type as "data_type"
                    , c.character_maximum_length as "character_maximum_length"
                    , c.character_octet_length as "character_octet_length"
                    , c.numeric_precision as "numeric_precision"
                    , c.numeric_precision_radix as "numeric_precision_radix"
                    , c.numeric_scale as "numeric_scale"
                    , fk.referenced_table_name as "referenced_table_name"
                    , fk.referenced_column_name as "referenced_column_name"
            FROM information_schema.tables t
            JOIN information_schema.columns c ON c.table_name = t.table_name AND c.table_catalog = t.table_catalog
            LEFT JOIN LATERAL (
                SELECT referenced_table_name, referenced_column_name
                FROM information_schema.key_column_usage kcu
                WHERE kcu.table_schema = t.table_schema
                    AND kcu.table_name = t.table_name
                    AND kcu.column_name = c.column_name
                LIMIT 1
            ) fk ON true
            WHERE t.table_schema = '$schema'
                AND t.table_name = '$table'
            ORDER BY c.ORDINAL_POSITION
            SQL;
        }

        throw new BoilerException("Not supported driver - {$driver}");
    }
}
