<?php

namespace DevMadeIt\Boiler;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\File;
use DevMadeIt\Boiler\ModelSchemaCollection;

class TypescriptGenerator
{
    public string $content = "";

    protected string $indent = "  ";
    protected string $interfacePath = "src/models";
    protected string $modelsPath = "src/models";
    protected bool $genInterface = true;
    protected bool $genModel = true;
    protected string $interfacePrefix = "I";

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
        $content = collect([]);

        if ($this->genInterface) $content->push($this->generateInterface());
        if ($this->genModel) $content->push($this->generateModel());

        $this->content = $content->join("\n\n");

        $this->save();
    }

    protected function save(): void
    {
        $model = Str::of($this->model)->camel()->toString();
        $filename = "{$model}.draft.ts";
        $path = "{$this->modelsPath}/{$filename}";

        if (!File::isDirectory(base_path($this->modelsPath))) {
            File::makeDirectory(base_path($this->modelsPath));
        }

        $this->command->info("- writing: {$path}");

        File::put(base_path($path), $this->content);
    }

    protected function generateInterface(): string
    {
        $imports = collect([]);
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
        if ($imports->count()) $content->push($imports->join("\n"), "");
        $content->push("export interface {$this->getInterfaceName($this->model)} {");
        $content->push($attrs->map(fn ($line) => "{$this->indent}{$line}")->join("\n"));
        $content->push("}");
        $content->push("");

        return $content->join("\n");
    }

    protected function getInterfaceLine(ColumnSchema $column): string
    {
        $name = Str::of($column->name)->camel()->toString();
        $nullable = $column->is_nullable ? '?' : '';
        $type = $column->getTsType();

        return "{$name}{$nullable}: $type";
    }

    protected function getInterfaceRelation(ColumnSchema $column, Collection &$imports): string
    {
        $nullable = $column->is_nullable ? '?' : '';
        $relation = Str::of($column->name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
        $relationClass = Str::of($column->referenced_table_name)->singular()->studly()->toString();

        $relationFilename = Str::camel($relationClass);
        $imports->push("import { {$this->getInterfaceName($relationClass)} } from './{$relationFilename}.draft'");

        return "{$relation}{$nullable}: {$this->getInterfaceName($relationClass)}";
    }

    protected function getInterfaceName(string $model): string
    {
        return "{$this->interfacePrefix}{$model}";
    }


    protected function generateModel(): string
    {
        $imports = collect([]);
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
        if ($imports->count()) $content->push($imports->join("\n"), "");
        $content->push("export class {$this->getModelName($this->model)} {");
        $content->push($attrs->map(fn ($line) => "{$this->indent}{$line}")->join("\n"));
        $content->push("");
        $content->push("{$this->indent}constructor(payload: {$this->getInterfaceName($this->model)}) {");
        $content->push($inits->join("\n"));
        $content->push("{$this->indent}}");
        $content->push("}");
        $content->push("");

        return $content->join("\n");
    }

    protected function getModelLine(ColumnSchema $column): string
    {
        $name = Str::of($column->name)->camel()->toString();
        $nullable = $column->is_nullable ? '?' : '';
        $type = $column->getTsType();

        return "declare {$name}{$nullable}: $type";
    }

    protected function getModelRelation(ColumnSchema $column, Collection &$imports): string
    {
        $nullable = $column->is_nullable ? '?' : '';
        $relation = Str::of($column->name)->whenEndsWith('_id', fn (Stringable $string) => $string->before('_id'))->singular()->camel()->toString();
        $relationClass = Str::of($column->referenced_table_name)->singular()->studly()->toString();

        $relationFilename = Str::camel($relationClass);
        $imports->push("import { {$this->getModelName($relationClass)} } from './{$relationFilename}.draft'");

        return "declare {$relation}{$nullable}: {$this->getModelName($relationClass)}";
    }

    protected function getModelName(string $model): string
    {
        return "{$model}";
    }
}
