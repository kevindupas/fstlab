<?php

namespace App\Filament\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsWidget extends BaseWidget
{
    use HasExperimentAccess;

    protected function getStats(): array
    {
        /** @var User */
        $user = Auth::user();
        $stats = [];

        if ($user->hasRole('supervisor')) {
            // Statistiques pour le supervisor
            $principalIds = $this->getPrincipalIds();
            $secondaryIds = $this->getSecondaryIds($principalIds);

            // Nombre d'expérimentations
            $experimentsCount = Experiment::where('created_by', $user->id)
                ->orWhereIn('created_by', $principalIds)
                ->orWhereIn('created_by', $secondaryIds)
                ->count();

            // Nombre total de sessions
            $sessionsCount = ExperimentSession::whereHas('experiment', function ($query) use ($user, $principalIds, $secondaryIds) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('created_by', $principalIds)
                    ->orWhereIn('created_by', $secondaryIds);
            })->count();

            // Nombre d'utilisateurs gérés
            $usersCount = User::role('principal_experimenter')
                ->where('created_by', $user->id)
                ->count();

            // Taux de complétion moyen des sessions
            $completionRate = ExperimentSession::whereHas('experiment', function ($query) use ($user, $principalIds, $secondaryIds) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('created_by', $principalIds)
                    ->orWhereIn('created_by', $secondaryIds);
            })
                ->where('status', 'completed')
                ->count() / max($sessionsCount, 1) * 100;

            $pendingRegistrationsCount = User::where('status', 'pending')
                ->whereHas('roles', fn($q) => $q->where('name', 'principal_experimenter'))
                ->count();

            // Nombre d'utilisateurs bannis
            $bannedUsersCount = User::where('status', 'banned')
                ->whereHas(
                    'roles',
                    fn($q) =>
                    $q->whereIn('name', ['principal_experimenter', 'secondary_experimenter'])
                )
                ->count();

            // Nombre de sessions en test
            $testSessionsCount = ExperimentSession::whereHas('experiment', function ($query) use ($principalIds) {
                $query->whereIn('created_by', array_merge($principalIds))
                    ->where('status', 'test');
            })->count();

            $stats = [
                Stat::make(__('widgets.dashboard_table.experiments.supervisor.name'), $experimentsCount)
                    ->description(__('widgets.dashboard_table.experiments.supervisor.description'))
                    ->color('success')
                    ->icon('heroicon-o-beaker'),

                Stat::make(__('widgets.dashboard_table.sessions.supervisor.name'), $sessionsCount)
                    ->description(__('widgets.dashboard_table.sessions.supervisor.description'))
                    ->color('info')
                    ->icon('heroicon-o-users'),

                Stat::make(__('widgets.dashboard_table.users.supervisor.name'), $usersCount)
                    ->description(__('widgets.dashboard_table.users.supervisor.description'))
                    ->color('warning')
                    ->icon('heroicon-o-user-group'),

                Stat::make(__('widgets.dashboard_table.completions.supervisor.name'), number_format($completionRate, 1) . '%')
                    ->description(__('widgets.dashboard_table.completions.supervisor.description'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Stat::make(__('widgets.dashboard_table.sessions_test.supervisor.name'), $testSessionsCount)
                    ->description(__('widgets.dashboard_table.sessions_test.supervisor.description'))
                    ->color('info')
                    ->icon('heroicon-o-beaker'),

                Stat::make(__('widgets.dashboard_table.registrations.supervisor.name'), $pendingRegistrationsCount)
                    ->description(__('widgets.dashboard_table.registrations.supervisor.description'))
                    ->color('warning')
                    ->icon('heroicon-o-user-plus'),

                Stat::make(__('widgets.dashboard_table.banned.supervisor.name'), $bannedUsersCount)
                    ->description(__('widgets.dashboard_table.banned.supervisor.description'))
                    ->color('danger')
                    ->icon('heroicon-o-user-minus'),
            ];
        } elseif ($user->hasRole('principal_experimenter')) {
            // Statistiques pour l'expérimentateur principal
            $secondaryIds = $user->createdUsers()
                ->role('secondary_experimenter')
                ->pluck('id');

            // Nombre d'expérimentations
            $experimentsCount = Experiment::where('created_by', $user->id)
                ->orWhereIn('created_by', $secondaryIds)
                ->count();

            // Nombre de sessions
            $sessionsCount = ExperimentSession::whereHas('experiment', function ($query) use ($user, $secondaryIds) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('created_by', $secondaryIds);
            })->count();

            // Nombre d'expérimentateurs secondaires
            $secondaryCount = $secondaryIds->count();

            // Taux de complétion moyen
            $completionRate = ExperimentSession::whereHas('experiment', function ($query) use ($user, $secondaryIds) {
                $query->where('created_by', $user->id)
                    ->orWhereIn('created_by', $secondaryIds);
            })
                ->where('status', 'completed')
                ->count() / max($sessionsCount, 1) * 100;

            $stats = [
                Stat::make(__('widgets.dashboard_table.experiments.principal.name'), $experimentsCount)
                    ->description(__('widgets.dashboard_table.experiments.principal.description'))
                    ->color('success')
                    ->icon('heroicon-o-beaker'),

                Stat::make(__('widgets.dashboard_table.sessions.principal.name'), $sessionsCount)
                    ->description(__('widgets.dashboard_table.sessions.principal.description'))
                    ->color('info')
                    ->icon('heroicon-o-users'),

                Stat::make(__('widgets.dashboard_table.users.principal.name'), $secondaryCount)
                    ->description(__('widgets.dashboard_table.users.principal.description'))
                    ->color('warning')
                    ->icon('heroicon-o-user-group'),

                Stat::make(__('widgets.dashboard_table.completions.principal.name'), number_format($completionRate, 1) . '%')
                    ->description(__('widgets.dashboard_table.completions.principal.description'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),
            ];
        }

        return $stats;
    }

    public static function canView(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Ne pas afficher ce widget si l'utilisateur est banni
        if ($user->status === 'banned') {
            return false;
        }

        // Ne pas afficher si c'est un secondary_experimenter dont le principal est banni
        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        return true;
    }
}
