<?php

namespace App\Filament\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['principal_experimenter', 'secondary_experimenter'])
                )
                ->count();

            // Nombre de sessions en test
            $testSessionsCount = ExperimentSession::whereHas('experiment', function ($query) use ($principalIds) {
                $query->whereIn('created_by', array_merge($principalIds))
                    ->where('status', 'test');
            })->count();

            $stats = [
                Stat::make('Expérimentations', $experimentsCount)
                    ->description('Total des expérimentations sous votre supervision')
                    ->color('success')
                    ->icon('heroicon-o-beaker'),

                Stat::make('Sessions', $sessionsCount)
                    ->description('Nombre total de sessions')
                    ->color('info')
                    ->icon('heroicon-o-users'),

                Stat::make('Utilisateurs', $usersCount)
                    ->description('Expérimentateurs sous votre supervision')
                    ->color('warning')
                    ->icon('heroicon-o-user-group'),

                Stat::make('Taux de complétion', number_format($completionRate, 1) . '%')
                    ->description('Sessions terminées avec succès')
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Stat::make('Sessions de test', $testSessionsCount)
                    ->description('Sessions en cours de test')
                    ->color('info')
                    ->icon('heroicon-o-beaker'),

                Stat::make('Demandes d\'inscription', $pendingRegistrationsCount)
                    ->description('En attente d\'approbation')
                    ->color('warning')
                    ->icon('heroicon-o-user-plus'),

                Stat::make('Utilisateurs bannis', $bannedUsersCount)
                    ->description('Comptes désactivés')
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
                Stat::make('Mes expérimentations', $experimentsCount)
                    ->description('Total de vos expérimentations')
                    ->color('success')
                    ->icon('heroicon-o-beaker'),

                Stat::make('Sessions', $sessionsCount)
                    ->description('Nombre total de sessions')
                    ->color('info')
                    ->icon('heroicon-o-users'),

                Stat::make('Expérimentateurs', $secondaryCount)
                    ->description('Expérimentateurs secondaires')
                    ->color('warning')
                    ->icon('heroicon-o-user-group'),

                Stat::make('Taux de complétion', number_format($completionRate, 1) . '%')
                    ->description('Sessions terminées avec succès')
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
