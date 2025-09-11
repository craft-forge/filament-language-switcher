<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], static function () {
    Route::get('filament/switch-language/{code}', static function ($code) {
        request()->session()->put('locale', $code);

        return redirect()->back();
    })->name('filament-language-switcher.switch');
});
