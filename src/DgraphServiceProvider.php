<?php

namespace Zymawy\Dgraph;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zymawy\Dgraph\Commands\DgraphCommand;

class DgraphServiceProvider extends PackageServiceProvider
{
    public function registeringPackage()
    {
        $this->app->singleton(DgraphClient::class, function ($app) {
            return new DgraphClient(config('dgraph.host').':'.config('dgraph.port'));
        });

        $this->app->singleton('dgraph', function ($app) {
            return new Dgraph($app->make(DgraphClient::class));
        });
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('dgraph')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_dgraph_table')
            ->hasCommand(DgraphCommand::class);
    }
}
