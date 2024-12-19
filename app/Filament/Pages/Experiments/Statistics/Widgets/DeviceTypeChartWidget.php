<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DeviceTypeChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'deviceTypeChart';
    // protected static ?string $heading = 'Types d\'appareils';

    public function getHeading(): string
    {
        return __('filament.pages.experiments_statistics.widgets.duration.heading');
    }

    public Experiment $record;

    protected function getOptions(): array
    {
        $deviceTypes = ExperimentSession::where('experiment_id', $this->record->id)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->get();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
                'fontFamily' => 'inherit',
            ],
            'series' => $deviceTypes->pluck('total')->toArray(),
            'labels' => $deviceTypes->pluck('device_type')->toArray(),
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'fontFamily' => 'inherit',
                'fontSize' => '14px',
                'markers' => [
                    'width' => 12,
                    'height' => 12,
                    'radius' => 12,
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '70%',
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontSize' => '14px',
                                'fontFamily' => 'inherit',
                            ],
                            'value' => [
                                'show' => true,
                                'fontSize' => '16px',
                                'fontWeight' => 'bold',
                                'fontFamily' => 'inherit',
                            ],
                            'total' => [
                                'show' => true,
                                'label' => 'Total',
                                'fontSize' => '16px',
                                'fontWeight' => 'bold',
                                'fontFamily' => 'inherit',
                            ],
                        ],
                    ],
                ],
            ],
            'colors' => ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'],
            'dataLabels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}
