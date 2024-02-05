<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DevMadeIt\Boiler\ModelSchemaCollection;
use DevMadeIt\Boiler\Exceptions\BoilerException;
use DevMadeIt\Boiler\Generator;

class BoilCommand extends Command
{
    protected Generator $generator;

    protected ModelSchemaCollection $columns;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boil:all {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Boil models, types, resources';

    /**
     * Execute the console command.
     *
     * @throws BoilerException
     */
    public function handle()
    {
        $this->generator = new Generator($this);
        $this->generator->run($this->argument('model'));
    }


}
