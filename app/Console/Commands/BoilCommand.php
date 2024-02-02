<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Console\Commands\Boil\BoilColumnDefinition;
use Illuminate\Support\Stringable;

class BoilCommand extends Command
{
    public string $model;
    public string $modelsNamespace = 'App\\Models\\';
    public string $modelClassName;
    protected string $tsIndent = "  ";

    /** @var Collection<BoilColumnDefinition> */
    protected Collection $columns;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boil {model} {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Boil models, types, resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->argument('table')) $this->handleTable();
        if ($this->argument('model')) $this->handleModel();
    }

    public function handleTable()
    {
        $table = $this->argument('table');
        $migrationPattern = "create_{$table}_table.php";
        $migrations = collect(File::glob(database_path("migrations/*_{$migrationPattern}")));

        if ($migrations->count() === 0) {
            $this->error("No '...{$migrationPattern}' migration found");
        }

        $migration = basename($migrations->first());

        $this->info("Boiling '{$table}' table");
        $this->info("- using '{$migration}'");
    }

    public function handleModel()
    {
        $this->configureModel();
        $this->getTsModels();
    }

    protected function getQuery(string $table): string
    {
        $driver = DB::getDriverName();
        $schema = DB::getDatabaseName();

        if ($driver == 'mysql') {
            return <<<SQL
            SELECT t.table_name as "table_name"
                    , c.column_name as "column_name"
                    , IF(c.is_nullable='YES', true, false) as "is_nullable"
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
                SELECT referenced_table_name , referenced_column_name
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
                    , c.column_name as "column_name"
                    , IF(c.is_nullable='YES', true, false) as "is_nullable"
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
                SELECT referenced_table_name , referenced_column_name
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

        throw new Exception("Not supported driver - {$driver}");
    }

    /**
     * @throws Exception
     */
    protected function configureModel(): void
    {
        $this->model = Str::of($this->argument('model'))->studly()->toString();

        $this->modelClassName = Str::contains($this->model, "\\") ? $this->model :"{$this->modelsNamespace}{$this->model}";

        if (!class_exists($this->modelClassName)) {
            throw new Exception("No model found '{$this->modelClassName}'");
        }

        $this->info("Boiling '{$this->model}'");
        $this->info("- using '{$this->modelClassName}' model");

        $modelInstance = new $this->modelClassName;
        $table = $modelInstance->getTable();
        $query = $this->getQuery($table);

        $this->columns = collect(DB::select($query));
    }

    protected function columnDbToTsType(string $dbType): string
    {
        return match ($dbType) {
            'big_int', 'bigint', 'tinyint', 'int', 'float', 'decimal', 'double' => 'number',
            'datetime', 'date' => 'string',
            'big_int', 'int' => 'number',
            default => 'string',
        };
    }

    protected function getTsModels(): void
    {
        $imports = collect([]);

        $attrs = $this->columns->map(function ($column) use (&$imports) {
            /** @var BoilColumnDefinition */
            $column = $column;
            $name = Str::of($column->column_name)->camel()->toString();
            $nullable = $column->is_nullable ? '?' : '';
            $type = $this->columnDbToTsType($column->data_type);

            $text = "{$name}{$nullable}: $type";

            if ($column->referenced_table_name) {
                $relation = Str::of($column->column_name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
                $relationClass = Str::of($column->referenced_table_name)->singular()->studly()->toString();
                $text = "{$text}\n{$this->tsIndent}{$relation}{$nullable}: {$relationClass}";

                $relationFilename = Str::camel($relationClass);
                $imports->push("import { $relationClass } from './{$relationFilename}.draft'");
            }

            return $text;
        });

        $content = collect([]);
        if ($imports->count()) $content->push($imports->join("\n"), "");
        $content->push("export interface {$this->model} {");
        $content->push($attrs->map(fn ($line) => "{$this->tsIndent}{$line}")->join("\n"));
        $content->push("}");
        $content->push("");

        $model = Str::of($this->model)->camel()->toString();
        $filename = "{$model}.draft.ts";
        $folder = "src/models";
        $path = "{$folder}/{$filename}";

        if (!File::isDirectory(base_path($folder))) {
            File::makeDirectory(base_path($folder));
        }

        File::put(base_path($path), $content->join("\n"));
    }

}
