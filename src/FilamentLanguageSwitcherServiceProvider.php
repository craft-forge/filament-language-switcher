<?php

namespace CraftForge\FilamentLanguageSwitcher;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentLanguageSwitcherServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-language-switcher')
            ->hasViews()
            ->hasRoute('web');
    }
}
