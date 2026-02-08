<?php

use CraftForge\FilamentLanguageSwitcher\Events\LocaleChanged;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], static function () {
    Route::get('filament/switch-language/{code}', static function ($code) {
        $oldLocale = request()->session()->get('locale', config('app.locale'));

        request()->session()->put('locale', $code);

        event(new LocaleChanged(newLocale: $code, oldLocale: $oldLocale));

        return redirect()->back();
    })->name('filament-language-switcher.switch');
});
