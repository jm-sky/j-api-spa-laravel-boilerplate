<?php

namespace DevMadeIt\Boiler;

use Spatie\LaravelPackageTools\Package;
use DevMadeIt\Console\Commands\BoilCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BoilerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-model-boiler')
            ->hasConfigFile('boiler')
            ->hasCommand(BoilCommand::class);
    }
}
