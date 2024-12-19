<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

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
                Stat::make(
                    __('filament.pages.experiments_statistics.widgets.stats.total.label'),
                    $totalSessions
                )
                    ->description(__('filament.pages.experiments_statistics.widgets.stats.total.description'))
                    ->descriptionIcon('heroicon-o-user-group')
                    ->color('primary'),
                Stat::make(
                    __('filament.pages.experiments_statistics.widgets.stats.completed.label'),
                    $completedSessions
                )
                    ->description(__('filament.pages.experiments_statistics.widgets.stats.completed.description', [
                        'percentage' => $completionPercentage
                    ]))
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
                Stat::make(
                    __('filament.pages.experiments_statistics.widgets.stats.duration.label'),
                    $this->formatDuration($avgDuration)
                )
                    ->description(__('filament.pages.experiments_statistics.widgets.stats.duration.description'))
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('info'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make(
                    __('filament.pages.experiments_statistics.widgets.stats.error.label'),
                    __('filament.pages.experiments_statistics.widgets.stats.error.value')
                )
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
