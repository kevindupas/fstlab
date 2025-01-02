<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Si une langue est spécifiée dans l'URL ou la session
        $locale = session('locale', Auth::user()?->locale ?? config('app.locale'));

        // Si la langue change via le switcher
        if ($request->has('change-language')) {
            $locale = $request->get('change-language');

            // Mettre à jour la préférence de l'utilisateur
            if (Auth::check()) {
                /** @var \App\Models\User */
                $user = Auth::user();
                $user->locale = $locale;
                $user->save();
            }
        }

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
