<?php

namespace CraftForge\FilamentLanguageSwitcher\Tests;

use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentLanguageSwitcherServiceProvider::class,
        ];
    }
}
