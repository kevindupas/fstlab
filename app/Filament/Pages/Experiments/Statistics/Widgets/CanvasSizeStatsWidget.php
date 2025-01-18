<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class CanvasSizeStatsWidget extends Widget
{
    protected static string $view = 'filament.pages.experiments.statistics.widgets.canvas-size-stats-widget';

    public Experiment $record;
    protected int | string | array $columnSpan = 'full';

    protected function getCanvasStats(): Collection
    {
        return $this->record->sessions()
            ->whereNotNull('canvas_size')
            ->get()
            ->map(function ($session) {
                $canvasData = json_decode($session->canvas_size, true);
                if (!$canvasData) return null;

                return [
                    'dimensions_cm' => [
                        'width' => round($canvasData['width_cm'], 2),
                        'height' => round($canvasData['height_cm'], 2)
                    ],
                    'dimensions_px' => [
                        'width' => $canvasData['width_px'],
                        'height' => $canvasData['height_px']
                    ],
                    'dpi' => $canvasData['dpi'],
                    'ratio' => round($canvasData['width_cm'] / $canvasData['height_cm'], 2),
                    'screen' => [
                        'width' => $session->screen_width,
                        'height' => $session->screen_height
                    ]
                ];
            })
            ->filter()
            ->groupBy(function ($canvas) {
                return "{$canvas['dimensions_cm']['width']}x{$canvas['dimensions_cm']['height']}";
            });
    }

    protected function getViewData(): array
    {
        return [
            'canvasStats' => $this->getCanvasStats()
        ];
    }
}
