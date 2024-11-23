<?php

namespace App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class StatsOverviewWidget extends BaseWidget
{
    public Experiment $record;

    protected function getStats(): array
    {
        try {

            Log::info('Widget Record ID: ' . $this->record?->id);

            $sessions = ExperimentSession::where('experiment_id', $this->record->id)->get();

            $totalSessions = $sessions->count();
            $completedSessions = $sessions->where('status', 'completed')->count();
            $avgDuration = $sessions->whereNotNull('duration')->avg('duration');

            $completionPercentage = $totalSessions > 0
                ? round(($completedSessions / $totalSessions) * 100, 1)
                : 0;

            return [
                Stat::make('Total des sessions', $totalSessions)
                    ->description('Nombre total de sessions')
                    ->descriptionIcon('heroicon-o-user-group')
                    ->color('primary'),
                Stat::make('Sessions complétées', $completedSessions)
                    ->description("$completionPercentage% de complétion")
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
                Stat::make('Durée moyenne', $this->formatDuration($avgDuration))
                    ->description('Temps moyen par session')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('info'),
            ];
        } catch (\Exception $e) {
            Log::error('Widget Error: ' . $e->getMessage());

            return [
                Stat::make('Erreur', 'Erreur de chargement')
                    ->description($e->getMessage())
                    ->color('danger'),
            ];
        }
    }

    private function formatDuration(?int $milliseconds): string
    {
        if (!$milliseconds) return '0s';

        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);

        $minutes = $minutes % 60;
        $seconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($seconds > 0) $parts[] = $seconds . 's';

        return implode(' ', $parts);
    }
}
