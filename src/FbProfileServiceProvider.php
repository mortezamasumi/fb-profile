<?php

namespace Mortezamasumi\FbProfile;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbProfileServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-profile';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasTranslations()
            ->hasConfigFile();
    }
}
