<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfPendingApproval
{
    public function handle(Request $request, Closure $next)
    {
        // Ajouter cette vérification en premier
        if (Auth::check() && $request->is('admin*') && !Auth::user()->hasRole('supervisor')) {
            if (Auth::user()?->status === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('filament.admin.auth.login')
                    ->with(['warning' => __('messages.account_pending_approval')]);
            }
        }

        // Garde le reste du middleware inchangé
        if (Auth::check() && Auth::user()?->status === 'pending') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.admin.auth.login')
                ->with(['warning' => __('messages.account_pending_approval')]);
        }

        return $next($request);
    }
}
