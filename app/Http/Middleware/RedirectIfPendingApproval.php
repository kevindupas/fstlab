<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfPendingApproval
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()?->status === 'pending') {
            // Log out the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirect = redirect()->route('filament.admin.auth.login');
            $redirect->with(['warning' => __('messages.account_pending_approval')]);

            return $redirect;
        }

        return $next($request);
    }
}
