<?php

namespace App\Traits;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasExperimentAccess
{
    public function canAccessExperiment(Experiment $experiment): bool
    {
        /** @var User */
        $user = Auth::user();

        // Si c'est une demande d'accès approuvée
        $hasApprovedAccess = $experiment->accessRequests()
            ->where('user_id', $user->id)
            ->where('type', 'results')
            ->where('status', 'approved')
            ->exists();

        // Si c'est le créateur
        if ($experiment->created_by === $user->id || $hasApprovedAccess) {
            return true;
        }

        // Si c'est un supervisor
        if ($user->hasRole('supervisor')) {
            $principalIds = User::role('principal_experimenter')
                ->where('created_by', $user->id)
                ->pluck('id')
                ->toArray();

            $secondaryIds = User::role('secondary_experimenter')
                ->whereIn('created_by', $principalIds)
                ->pluck('id')
                ->toArray();

            return in_array($experiment->created_by, $principalIds) ||
                in_array($experiment->created_by, $secondaryIds);
        }

        return false;
    }

    public function getPrincipalIds(): array
    {
        /** @var User */
        $user = Auth::user();

        return User::role('principal_experimenter')
            ->where('created_by', $user->id)
            ->pluck('id')
            ->toArray();
    }

    public function getSecondaryIds(array $principalIds = null): array
    {
        if ($principalIds === null) {
            $principalIds = $this->getPrincipalIds();
        }

        return User::role('secondary_experimenter')
            ->whereIn('created_by', $principalIds)
            ->pluck('id')
            ->toArray();
    }
}
