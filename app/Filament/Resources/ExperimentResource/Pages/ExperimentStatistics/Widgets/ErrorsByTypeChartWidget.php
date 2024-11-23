<?php

namespace App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ErrorsByTypeChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'errorsByTypeChart';
    protected static ?string $heading = 'Types d\'erreurs';

    public Experiment $record;

    protected function getOptions(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('errors_log')
            ->get();

        $errorTypes = collect();
        foreach ($sessions as $session) {
            $errors = json_decode($session->errors_log, true) ?? [];
            foreach ($errors as $error) {
                $errorTypes->push($error['type']);
            }
        }

        $errorCounts = $errorTypes->countBy();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'fontFamily' => 'inherit',
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                    'distributed' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Erreurs',
                    'data' => $errorCounts->values()->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $errorCounts->keys()->toArray(),
                'labels' => [
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
            'colors' => ['#ef4444'],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontFamily' => 'inherit',
                    'fontWeight' => 600,
                ],
                'offsetX' => 10,
            ],
            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'title' => [
                        'formatter' => 'function (seriesName) { return "Nombre d\'erreurs:" }',
                    ],
                ],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 4,
                'position' => 'back',
            ],
        ];
    }
}
