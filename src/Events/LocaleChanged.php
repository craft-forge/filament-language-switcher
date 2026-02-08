<?php

namespace CraftForge\FilamentLanguageSwitcher\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LocaleChanged
{
    use Dispatchable;

    public function __construct(
        public string $newLocale,
        public ?string $oldLocale = null,
    ) {}
}
