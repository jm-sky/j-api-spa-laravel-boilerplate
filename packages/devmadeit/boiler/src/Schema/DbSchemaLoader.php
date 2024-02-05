<?php

declare(strict_types=1);

namespace DevMadeIt\Boiler\Schema;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class DbSchemaLoader
{
    /** @var Collection<Column> */
    public Collection $columns;

    /** @var Collection<ForeignKeyConstraint> */
    public Collection $foreignKeys;

    public function __construct(
        protected Model $model,
    )
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    public function loadSchema()
    {
        $this->columns = collect(
            $this->model->getConnection()
                ->getDoctrineSchemaManager()
                ->listTableColumns($this->getPrefixedTableName())
        );

        $this->foreignKeys = collect(
            $this->model->getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys($this->getPrefixedTableName())
        );

        $schema = $this->columns->map(function (Column $column, string $columnName) {
            /** @var ForeignKeyConstraint | null */
            $foreignKey = $this->foreignKeys->first(fn (ForeignKeyConstraint $foreignKey) => in_array($columnName, $foreignKey->getLocalColumns()));

            return [
                'column' => $column,
                'foreignKey' => $foreignKey
            ];
        });
    }

    protected function getPrefixedTableName(): string
    {
        return $this->model->getConnection()->getTablePrefix() . $this->model->getTable();
    }
}
