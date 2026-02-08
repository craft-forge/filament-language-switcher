<?php

use CraftForge\FilamentLanguageSwitcher\Events\LocaleChanged;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], static function () {
    Route::get('filament/switch-language/{code}', static function ($code) {
        $oldLocale = request()->session()->get('locale', config('app.locale'));

        request()->session()->put('locale', $code);

        $rememberDays = FilamentLanguageSwitcherPlugin::getRememberLocaleDays();

        if ($rememberDays !== null) {
            $cookie = $rememberDays === 0
                ? cookie()->forever('filament_language_switcher_locale', $code)
                : cookie('filament_language_switcher_locale', $code, $rememberDays * 24 * 60);

            cookie()->queue($cookie);
        }

        event(new LocaleChanged(newLocale: $code, oldLocale: $oldLocale));

        return redirect()->back();
    })->name('filament-language-switcher.switch');
});
