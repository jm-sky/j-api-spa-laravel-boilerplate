<?php

namespace DevMadeIt\Boiler;

use Illuminate\Support\Str;
use DevMadeIt\Boiler\SchemaLoader;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\File;
use DevMadeIt\Boiler\ModelSchemaCollection;
use DevMadeIt\Boiler\Exceptions\BoilerException;
use Illuminate\Console\Command;

class Generator
{
    public string $model;
    public string $modelClassName;

    protected SchemaLoader $schemaLoader;
    protected ModelSchemaCollection $columns;

    protected string $modelsNamespace = '\\App\\Models\\';
    protected string $tsIndent = "  ";

    public function __construct(
        protected Command $command,
    )
    {
        $this->modelsNamespace = config('boiler.models_namespace', $this->modelsNamespace);
        $this->tsIndent = config('boiler.ts.indent', $this->tsIndent);
    }

    /**
     * @throws BoilerException
     */
    public function initSchema(string $model): void
    {
        $this->model = Str::of($model)->studly()->toString();
        $this->modelClassName = Str::contains($this->model, "\\") ? $this->model :"{$this->modelsNamespace}{$this->model}";

        if (!class_exists($this->modelClassName)) {
            throw new BoilerException("No model found '{$this->modelClassName}'");
        }

        $this->command->info("Boiling '{$this->model}'");
        $this->command->info("- using '{$this->modelClassName}' model");

        $this->schemaLoader = new SchemaLoader($this->getTable());
        $this->columns = $this->schemaLoader->getColumns();
    }

    protected function getTable(): string
    {
        $modelInstance = new $this->modelClassName;

        return $modelInstance->getTable();;
    }

    protected function getTsModels(): void
    {
        $imports = collect([]);

        $attrs = $this->columns->map(function ($column) use (&$imports) {
            /** @var ColumnSchema */
            $column = $column;
            $name = Str::of($column->name)->camel()->toString();
            $nullable = $column->is_nullable ? '?' : '';
            $type = $column->getTsType();

            $text = "{$name}{$nullable}: $type";

            if ($column->referenced_table_name) {
                $relation = Str::of($column->name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
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
