<?php

declare(strict_types=1);

namespace DevMadeIt\Boiler\Generators;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\File;
use DevMadeIt\Boiler\Schema\ColumnSchema;
use DevMadeIt\Boiler\Schema\ModelSchemaCollection;

class TypescriptGenerator
{
    public string $content = "";

    protected string $indent = "  ";
    protected string $interfacePath = "src/models";
    protected string $modelsPath = "src/models";
    protected bool $genInterface = true;
    protected bool $genModel = true;
    protected string $interfacePrefix = "I";
    protected string $interfaceFileSuffix = "model";
    protected string $modelFileSuffix = "model";

    protected array $files = [];

    public function __construct(
        protected string $model,
        protected Command $command,
        protected ModelSchemaCollection $columns,
    ) {
        $this->indent = config('boiler.ts.indent', $this->indent);
        $this->modelsPath = config('boiler.ts.models.path', $this->modelsPath);
        $this->interfacePath = config('boiler.ts.interfaces.path', $this->interfacePath);
        $this->genInterface = config('boiler.ts.interfaces.generate', $this->genInterface);
        $this->genModel = config('boiler.ts.models.generate', $this->genModel);
        $this->interfacePrefix = config('boiler.ts.interfaces.prefix', $this->interfacePrefix);
    }

    public function run()
    {
        if ($this->genInterface) {
            $this->generateInterface();
        }

        if ($this->genModel) {
            $this->generateModel();
        }

        $this->save();
    }

    protected function save(): void
    {
        foreach ($this->files as $filename => $elements) {

            $pathinfo = pathinfo(base_path($filename));


            if (!File::isDirectory($pathinfo['dirname'])) {
                File::makeDirectory($pathinfo['dirname']);
            }

            $content = [];

            if (sizeof($elements['imports'])) {
                foreach ($elements['imports'] as $path => $types) {
                    $types = implode(', ', $types);
                    $content[] = "import { $types } from '{$path}';";
                }

                $content[] = "";
            }

            $content[] = implode("\n", $elements['parts']);

            $this->command->info("- writing: {$filename}");

            File::put(base_path($filename), implode("\n", $content));
        }
    }

    protected function generateInterface(): void
    {
        $filename = $this->getInterfaceFilenamePath();
        $imports = $this->files[$filename]['imports'] ?? [];

        $attrs = collect([]);

        $this->columns->each(function (ColumnSchema $column) use (&$attrs, &$imports) {
            $line = $this->getInterfaceLine($column);
            $attrs->push($line);

            if ($column->referenced_table_name) {
                $line = $this->getInterfaceRelation($column, $imports);
                $attrs->push($line);
            }
        });

        $content = collect([]);
        $content->push("export interface {$this->getInterfaceName($this->model)} {");
        $content->push($attrs->map(fn ($line) => "{$this->indent}{$line}")->join("\n"));
        $content->push("}");
        $content->push("");

        $this->files[$filename] ??= [];
        $this->files[$filename]['imports'] = $imports;
        $this->files[$filename]['parts'] ??= [];
        $this->files[$filename]['parts'][] = $content->join("\n");
    }

    protected function getInterfaceFilename(string $name): string
    {
        $model = Str::of($name)->camel()->toString();
        $filename = "{$model}.{$this->interfaceFileSuffix}.ts";

        return $filename;
    }

    protected function getInterfaceFilenamePath(): string
    {
        $filename = $this->getInterfaceFilename($this->model);
        $path = "{$this->interfacePath}/{$filename}";

        return $path;
    }

    protected function getModelFilename(string $model): string
    {
        $model = Str::of($model)->camel()->toString();
        $filename = "{$model}.{$this->modelFileSuffix}.ts";

        return $filename;
    }

    protected function getModelFilenamePath(): string
    {
        $filename = $this->getModelFilename($this->model);
        $path = "{$this->modelsPath}/{$filename}";

        return $path;
    }

    protected function getInterfaceLine(ColumnSchema $column): string
    {
        $name = Str::of($column->name)->camel()->toString();
        $nullable = $column->is_nullable ? '?' : '';
        $type = $column->getTsType();

        return "{$name}{$nullable}: $type";
    }

    protected function getInterfaceRelation(ColumnSchema $column, array &$imports): string
    {
        $nullable = $column->is_nullable ? '?' : '';
        $relation = Str::of($column->name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
        $relationClass = Str::of($column->referenced_table_name)->singular()->studly()->toString();

        $relationFilename = $this->getInterfaceFilename(Str::camel($relationClass));
        $interface = $this->getInterfaceName($relationClass);

        $imports["./{$relationFilename}"] ??= [];
        $imports["./{$relationFilename}"][$interface] = $interface;

        return "{$relation}{$nullable}: {$interface}";
    }

    protected function getInterfaceName(string $model): string
    {
        return "{$this->interfacePrefix}{$model}";
    }


    protected function generateModel(): void
    {
        $filename = $this->getModelFilenamePath();
        $imports = $this->files[$filename]['imports'] ?? [];

        $attrs = collect([]);

        $this->columns->each(function (ColumnSchema $column) use (&$attrs, &$imports) {
            $line = $this->getModelLine($column);
            $attrs->push($line);

            if ($column->referenced_table_name) {
                $line = $this->getModelRelation($column, $imports);
                $attrs->push($line);
            }
        });

        $inits = collect([]);
        $this->columns->each(function ($column) use (&$inits) {
            $name = Str::of($column->name)->camel()->toString();
            $inits->push("{$this->indent}{$this->indent}this.{$name} = payload.{$name}");
        });

        $content = collect([]);
        $content->push("export class {$this->getModelName($this->model)} {");
        $content->push($attrs->map(fn ($line) => "{$this->indent}{$line}")->join("\n"));
        $content->push("");
        $content->push("{$this->indent}constructor(payload: {$this->getInterfaceName($this->model)}) {");
        $content->push($inits->join("\n"));
        $content->push("{$this->indent}}");
        $content->push("}");
        $content->push("");

        $this->files[$filename] ??= [];
        $this->files[$filename]['imports'] = $imports;
        $this->files[$filename]['parts'] ??= [];
        $this->files[$filename]['parts'][] = $content->join("\n");
    }

    protected function getModelLine(ColumnSchema $column): string
    {
        $name = Str::of($column->name)->camel()->toString();
        $nullable = $column->is_nullable ? '?' : '';
        $type = $column->getTsType();

        return "declare {$name}{$nullable}: $type";
    }

    protected function getModelRelation(ColumnSchema $column, array &$imports): string
    {
        $nullable = $column->is_nullable ? '?' : '';
        $relation = Str::of($column->name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
        $relationClass = Str::of($column->referenced_table_name)->singular()->studly()->toString();

        $relationFilename = $this->getModelFilename(Str::camel($relationClass));
        $imports["./{$relationFilename}"] ??= [];
        $imports["./{$relationFilename}"][$this->getModelName($relationClass)] = $this->getModelName($relationClass);

        return "declare {$relation}{$nullable}: {$this->getModelName($relationClass)}";
    }

    protected function getModelName(string $model): string
    {
        return "{$model}";
    }
}
