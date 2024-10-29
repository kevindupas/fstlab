<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExperimentSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExperimentSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExperimentSession $experimentSession)
    {
        // Si l'utilisateur est superviseur, il peut tout voir
        if ($user->hasRole('supervisor')) {
            return true;
        }

        // Si l'utilisateur est expérimentateur principal, il peut voir les sessions
        // des expériences qu'il a créées
        if ($user->hasRole('principal_experimenter')) {
            return $experimentSession->experiment->created_by === $user->id;
        }

        return false;
    }
}
