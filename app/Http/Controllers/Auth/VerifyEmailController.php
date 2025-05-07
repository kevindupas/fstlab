<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('filament.admin.auth.login')
                ->with('warning', __('messages.account_pending_approval'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // Mettre à jour le statut en "pending"
            DB::table('users')
                ->where('id', $request->user()->id)
                ->update(['status' => 'pending']);

            // Notifier (si nécessaire - peut être géré par l'événement)
            // $request->user()->notify(new RegistrationSubmitted());
            // User::role('supervisor')->first()?->notify(new NewRegistrationRequest($request->user()));

            // Déconnecter l'utilisateur
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Rediriger vers la page de connexion avec message
        return redirect()->route('filament.admin.auth.login')
            ->with('warning', __('messages.account_pending_approval'));
    }
}
