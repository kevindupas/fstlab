<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SessionStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public Experiment $record;

    protected function getStats(): array
    {
        $sessions = $this->record->sessions;

        $sessionsLog = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('actions_log')
            ->get();

        $completedSessions = $sessions->where('status', 'completed');
        // $averageInteractions = $sessions->avg(function ($session) {
        //     $actions = json_decode($session->actions_log ?? '[]', true);
        //     return count($actions);
        // });

        // $commonBrowsers = $sessions->groupBy('browser')
        //     ->map->count()
        //     ->sortDesc()
        //     ->take(2)
        //     ->map(fn($count, $browser) => "$browser ($count)");
        $totalMoves = 0;
        $totalPlays = 0;
        $totalGroupChanges = 0;

        foreach ($sessionsLog as $session) {
            $actions = collect(json_decode($session->actions_log, true));

            $totalMoves += $actions->where('type', 'move')->count();
            $totalPlays += $actions->whereIn('type', ['sound', 'image'])->count();
            $totalGroupChanges += $actions->where('type', 'item_moved_between_groups')->count();
        }

        return [
            Stat::make('Sessions complétées', $completedSessions->count())
                ->description($sessions->count() . ' sessions au total')
                ->color('success'),

            Stat::make('Durée moyenne', $sessions->avg('duration') / 1000 . 's')
                ->description('Temps moyen par session')
                ->color('warning'),

            // Statistiques globales
            Stat::make(
                'Totaux globaux',
                sprintf('%d actions', $totalMoves + $totalPlays)
            )
                ->description(sprintf('%d déplacements, %d lectures/vues', $totalMoves, $totalPlays))
                ->icon('heroicon-o-chart-bar')
                ->color('info'),

            // Stat::make('Navigateurs', $commonBrowsers->implode(', '))
            //     ->description('Les plus utilisés')
            //     ->color('gray'),
        ];
    }
}
