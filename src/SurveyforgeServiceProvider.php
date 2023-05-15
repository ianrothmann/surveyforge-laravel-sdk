<?php

namespace Surveyforge\Surveyforge;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Surveyforge\Surveyforge\Commands\SurveyforgeCommand;

class SurveyforgeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('surveyforge-laravel')
            ->hasConfigFile('surveyforge');
//            ->hasViews()
//            ->hasMigration('create_surveyforge-laravel_table')
//            ->hasCommand(SurveyforgeCommand::class);
    }
}
