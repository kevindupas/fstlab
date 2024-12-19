<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Carbon\Carbon;

class ActionsTimelineChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'actionsTimelineChart';
    // protected static ?string $heading = 'Timeline des actions';

    public function getHeading(): string
    {
        return __('filament.pages.experiments_statistics.widgets.actions_timeline.heading');
    }

    public Experiment $record;

    protected function getOptions(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('actions_log')
            ->get();

        $actionsData = collect();
        foreach ($sessions as $session) {
            $actions = json_decode($session->actions_log, true) ?? [];
            foreach ($actions as $action) {
                $actionsData->push([
                    'time' => Carbon::createFromTimestamp($action['time'] / 1000),
                    'action' => $action['id'],
                    'session' => $session->participant_number,
                ]);
            }
        }

        $groupedActions = $actionsData->groupBy('session');

        $series = $groupedActions->map(function ($actions, $session) {
            return [
                'name' => "Session $session",
                'data' => $actions->map(fn($action) => [
                    'x' => $action['time']->format('Y-m-d H:i:s'),
                    'y' => 1,
                    'action' => $action['action'],
                ])->toArray(),
            ];
        })->values()->toArray();

        return [
            'chart' => [
                'type' => 'scatter',
                'height' => 300,
                'fontFamily' => 'inherit',
                'zoom' => [
                    'enabled' => true,
                    'type' => 'xy',
                ],
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => $series,
            'xaxis' => [
                'type' => 'datetime',
                'labels' => [
                    'rotate' => -45,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'custom' => <<<'JS'
                function({ seriesIndex, dataPointIndex, w }) {
                    const point = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                    return `
                        <div class="px-3 py-2">
                            <div class="font-medium">${w.globals.initialSeries[seriesIndex].name}</div>
                            <div class="text-xs opacity-80">${point.action}</div>
                            <div class="text-xs opacity-80">${point.x}</div>
                        </div>
                    `;
                }
            JS,
            ],
            'markers' => [
                'size' => 6,
                'strokeWidth' => 2,
                'hover' => [
                    'size' => 8,
                ],
            ],
            'colors' => ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'],
            'grid' => [
                'xaxis' => [
                    'lines' => [
                        'show' => true,
                    ],
                ],
            ],
        ];
    }
}
