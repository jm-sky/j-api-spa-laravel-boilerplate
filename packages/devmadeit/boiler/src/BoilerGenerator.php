<?php

declare(strict_types=1);

namespace DevMadeIt\Boiler;

use ReflectionClass;
use DevMadeIt\Boiler\Schema\DbSchemaLoader;
use Illuminate\Database\Eloquent\Model;

class BoilerGenerator
{
    protected ReflectionClass $reflection;
    protected Model $model;
    protected DbSchemaLoader $schemaLoader;

    public function __construct(
        protected string $modelClassName,
    ) {
        $this->reflection = new ReflectionClass($this->modelClassName);
        $this->model = $this->reflection->newInstance();
        $this->schemaLoader = new DbSchemaLoader($this->model);
    }

    public function loadSchema()
    {
        $this->schemaLoader->loadSchema();
    }
}
