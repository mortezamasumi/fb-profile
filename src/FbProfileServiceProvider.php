<?php

namespace Mortezamasumi\FbProfile;

use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbProfile\Rules\IranNid;
use Mortezamasumi\FbProfile\Testing\TestsFbProfile;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbProfileServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-profile';

    public function configurePackage(Package $package): void
    {
        $package
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile();
            })
            ->name(static::$name)
            ->hasTranslations()
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        Validator::extend('iran_nid', function ($attribute, $value, $parameters, $validator) {
            (new IranNid)->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });

            return ! $failed;
        });

        Validator::replacer('iran_nid', function ($message, $attribute, $rule, $parameters) {
            return __('fb-profile::fb-profile.nid.validation');
        });

        Testable::mixin(new TestsFbProfile);
    }
}
