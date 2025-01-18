<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\ExperimentSession;

class TimeStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('completed_at')
            ->get();

        $avgDuration = $sessions->avg('duration') / 1000; // Conversion en secondes
        $minDuration = $sessions->min('duration') / 1000;
        $maxDuration = $sessions->max('duration') / 1000;

        return [
            Stat::make('Durée moyenne', sprintf('%.1f sec', $avgDuration))
                ->description('Temps moyen par session')
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Durée minimum', sprintf('%.1f sec', $minDuration))
                ->description('Session la plus rapide')
                ->icon('heroicon-o-arrow-down')
                ->color('success'),

            Stat::make('Durée maximum', sprintf('%.1f sec', $maxDuration))
                ->description('Session la plus longue')
                ->icon('heroicon-o-arrow-up')
                ->color('danger'),
        ];
    }
}
