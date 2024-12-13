<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DurationChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'durationChart';
    protected static ?string $heading = 'Distribution des durées';

    public Experiment $record;

    protected function getOptions(): array
    {
        $durations = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('duration')
            ->select('id', 'duration')
            ->orderBy('duration')
            ->get();

        $values = $durations->pluck('duration')->sort()->values();
        $count = $values->count();

        if ($count === 0) {
            return [
                'chart' => [
                    'type' => 'boxPlot',
                    'height' => 300,
                    'fontFamily' => 'inherit',
                ],
                'series' => [
                    [
                        'name' => 'Durée',
                        'data' => [
                            [
                                'x' => 'Sessions',
                                'y' => [0, 0, 0, 0, 0],
                            ],
                        ],
                    ],
                ],
                'plotOptions' => [
                    'boxPlot' => [
                        'colors' => [
                            'upper' => '#34d399',
                            'lower' => '#f87171',
                        ],
                    ],
                ],
            ];
        }

        $q1 = $count > 0 ? $values->slice(0, (int)($count / 4))->avg() : 0;
        $median = $values->avg();
        $q3 = $count > 0 ? $values->slice(3 * (int)($count / 4))->avg() : 0;

        return [
            'chart' => [
                'type' => 'boxPlot',
                'height' => 300,
                'fontFamily' => 'inherit',
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Durée',
                    'data' => [
                        [
                            'x' => 'Sessions',
                            'y' => [
                                $values->min(),
                                $q1,
                                $median,
                                $q3,
                                $values->max()
                            ],
                        ],
                    ],
                ],
            ],
            'plotOptions' => [
                'boxPlot' => [
                    'colors' => [
                        'upper' => '#34d399',
                        'lower' => '#f87171',
                    ],
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'custom' => <<<'JS'
                    function({ seriesIndex, dataPointIndex, w }) {
                        const data = w.globals.initialSeries[seriesIndex].data[dataPointIndex].y;
                        function formatDuration(ms) {
                            const seconds = Math.floor(ms / 1000);
                            const minutes = Math.floor(seconds / 60);
                            const hours = Math.floor(minutes / 60);
                            return hours > 0 
                                ? `${hours}h ${minutes % 60}m ${seconds % 60}s`
                                : minutes > 0 
                                    ? `${minutes}m ${seconds % 60}s`
                                    : `${seconds}s`;
                        }
                        return `
                            <div class="p-2">
                                <div>Min: ${formatDuration(data[0])}</div>
                                <div>Q1: ${formatDuration(data[1])}</div>
                                <div>Médiane: ${formatDuration(data[2])}</div>
                                <div>Q3: ${formatDuration(data[3])}</div>
                                <div>Max: ${formatDuration(data[4])}</div>
                            </div>
                        `;
                    }
                JS,
            ],
        ];
    }
}
