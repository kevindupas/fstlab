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

        // Si c'est un supervisor
        if ($user->hasRole('supervisor')) {
            $principalIds = $this->getPrincipalIds();
            $secondaryIds = $this->getSecondaryIds($principalIds);

            return $experiment->created_by === $user->id ||
                in_array($experiment->created_by, $principalIds) ||
                in_array($experiment->created_by, $secondaryIds);
        }

        // Si c'est un principal experimenter
        if ($user->hasRole('principal_experimenter')) {
            $secondaryIds = $user->createdUsers()
                ->role('secondary_experimenter')
                ->pluck('id')
                ->toArray();

            return $experiment->created_by === $user->id ||
                in_array($experiment->created_by, $secondaryIds);
        }

        // Pour les autres rôles (secondary_experimenter, etc.)
        return $experiment->created_by === $user->id ||
            $experiment->accessRequests()
            ->where('user_id', $user->id)
            ->where('type', 'results')
            ->where('status', 'approved')
            ->exists();
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
