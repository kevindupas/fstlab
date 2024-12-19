<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CompletionChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'completionChart';
    // protected static ?string $heading = 'Progression des sessions';

    public function getHeading(): string
    {
        return __('filament.pages.experiments_statistics.widgets.completion.heading');
    }


    public Experiment $record;

    protected function getOptions(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'fontFamily' => 'inherit',
                'toolbar' => [
                    'show' => true,
                ],
                'zoom' => [
                    'enabled' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Sessions',
                    'data' => $sessions->pluck('total')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $sessions->pluck('date')->toArray(),
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
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.2,
                ],
            ],
            'colors' => ['#0ea5e9'],
        ];
    }
}
