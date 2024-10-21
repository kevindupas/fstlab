<?php

namespace App\Policies;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ExperimentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Experiment $experiment)
    {
        return $user->id === $experiment->created_by || $experiment->users()->where('user_id', $user->id)->first()?->pivot?->can_configure ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user->hasAnyRole(['supervisor', 'principal_experimenter']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Experiment $experiment)
    {
        return $user->id === $experiment->created_by || $experiment->users()->where('user_id', $user->id)->first()?->pivot?->can_configure ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Experiment $experiment)
    {
        return $user->id === $experiment->created_by; // Seul le créateur peut supprimer
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Experiment $experiment): void
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Experiment $experiment): void
    {
        //
    }
}
