<?php

declare(strict_types=1);

namespace DevMadeIt\Boiler;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use DevMadeIt\Boiler\BoilerGenerator;
use DevMadeIt\Boiler\Schema\SchemaLoader;
use DevMadeIt\Boiler\BoilerServiceProvider;
use DevMadeIt\Boiler\Exceptions\BoilerException;
use DevMadeIt\Boiler\Schema\ModelSchemaCollection;
use DevMadeIt\Boiler\Generators\TypescriptGenerator;

class Generator
{
    public string $model;
    public string $modelClassName;

    protected SchemaLoader $schemaLoader;
    protected ModelSchemaCollection $columns;

    protected string $modelsNamespace = '\\App\\Models\\';

    protected bool $generateTypescript = true;

    public function __construct(
        protected Command $command,
    )
    {
        // BoilerServiceProvider::forceConfigSet();

        (new BoilerGenerator('\\App\\Models\\Project'))->loadSchema();

        $this->modelsNamespace = config('boiler.models_namespace', $this->modelsNamespace);
        $this->generateTypescript = config('boiler.ts.generate', $this->generateTypescript);
    }

    public function run(string $model): void
    {
        $this->initSchema($model);

        if ($this->generateTypescript) (new TypescriptGenerator($this->model, $this->command, $this->columns))->run();
        // if ($this->tsGenInterface) $this->generateTsInterface();
    }

    /**
     * @throws BoilerException
     */
    protected function initSchema(string $model): void
    {
        $this->model = Str::of($model)->studly()->toString();
        $this->modelClassName = Str::contains($this->model, "\\") ? $this->model :"{$this->modelsNamespace}{$this->model}";

        if (!class_exists($this->modelClassName)) {
            throw new BoilerException("No model found '{$this->modelClassName}'");
        }

        $this->command->info("Boiling '{$this->model}'");
        $this->command->info("- using '{$this->modelClassName}' model");

        $this->schemaLoader = new SchemaLoader($this->modelClassName);
        $this->columns = $this->schemaLoader->getColumns();
    }
}
