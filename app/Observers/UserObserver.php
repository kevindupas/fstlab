<?php

namespace App\Observers;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Notifications\NewRegistrationRequest;
use App\Notifications\RegistrationSubmitted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        if (!Auth::check()) {
            $user->assignRole('principal_experimenter');
        }
    }

    public function updated(User $user): void
    {
        if (
            $user->wasChanged('email_verified_at') &&
            $user->email_verified_at !== null &&
            !$user->wasChanged('status')
        ) {

            DB::table('users')
                ->where('id', $user->id)
                ->update(['status' => 'pending']);

            // On notifie
            $user->notify(new RegistrationSubmitted());
            User::role('supervisor')->first()?->notify(new NewRegistrationRequest($user));

            // Déconnexion avec message
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            session()->flash('warning', __('messages.account_pending_approval'));
        }
    }
}
