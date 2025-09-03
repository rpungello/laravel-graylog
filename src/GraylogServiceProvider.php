<?php

namespace Rpungello\Graylog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rpungello\Graylog\Commands\GraylogCommand;

class GraylogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-graylog')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_graylog_table')
            ->hasCommand(GraylogCommand::class);
    }
}
