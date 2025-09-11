<?php

namespace CraftForge\FilamentLanguageSwitcher\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->session()->get('locale') ?? config('app.locale', 'en');

        App::setLocale($locale);

        return $next($request);
    }
}
