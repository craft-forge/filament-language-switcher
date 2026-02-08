<?php

namespace CraftForge\FilamentLanguageSwitcher\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = config('app.locale', 'en');

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');

            if ($sessionLocale) {
                $locale = $sessionLocale;
            } elseif ($cookieLocale = $request->cookie('filament_language_switcher_locale')) {
                $locale = $cookieLocale;
                $request->session()->put('locale', $locale);
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
