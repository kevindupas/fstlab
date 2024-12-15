<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPendingApproval
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()?->status === 'pending') {
            // Déconnecter l'utilisateur
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirect = redirect()->route('filament.admin.auth.login');
            $redirect->with(['warning' => 'Votre compte est en attente d\'approbation. Vous recevrez un email dès que votre compte sera validé.']);

            return $redirect;
        }

        return $next($request);
    }
}
