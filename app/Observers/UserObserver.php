<?php

namespace App\Observers;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Notifications\NewRegistrationRequest;
use App\Notifications\RegistrationSubmitted;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Si un utilisateur est connecté, c'est une création via resource
        if (Auth::check()) {
            if (isset($user->role)) {
                $user->assignRole($user->role);
            }
            return; // On ne fait rien d'autre
        }

        // Sinon c'est une inscription normale via register
        $principalExperimenterRole = Role::where('name', 'principal_experimenter')->first();
        if ($principalExperimenterRole) {
            $user->assignRole($principalExperimenterRole);
        }

        // Mettre le status en pending par défaut
        $user->status = 'pending';
        $user->save();

        // Notifier l'utilisateur que sa demande est en cours
        $user->notify(new RegistrationSubmitted());

        // Notifier le supervisor
        $supervisor = User::role('supervisor')->first();
        if ($supervisor) {
            $supervisor->notify(new NewRegistrationRequest($user));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
