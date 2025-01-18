<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class BrowserStatsWidget extends Widget
{
    protected static string $view = 'filament.pages.experiments.statistics.widgets.browser-stats-widget';

    public Experiment $record;
    protected int | string | array $columnSpan = 'full';

    protected function getBrowserStats(): Collection
    {
        return $this->record->sessions()
            ->select('browser', 'operating_system', 'device_type', 'screen_width', 'screen_height', 'is_dark')
            ->get()
            ->groupBy('browser')
            ->map(function ($sessions) {
                $total = $sessions->count();
                return [
                    'count' => $total,
                    'percentage' => round(($total / $this->record->sessions()->count()) * 100, 1),
                    'operating_systems' => $sessions->groupBy('operating_system')
                        ->map(fn($os) => $os->count())
                        ->sortDesc(),
                    'device_types' => $sessions->groupBy('device_type')
                        ->map(fn($devices) => $devices->count())
                        ->sortDesc(),
                    'screen_sizes' => $sessions->map(fn($session) => "{$session->screen_width}x{$session->screen_height}")
                        ->countBy()
                        ->sortDesc(),
                    'dark_mode' => $sessions->where('is_dark', 1)->count(),
                    'light_mode' => $sessions->where('is_dark', 0)->count(),
                ];
            })
            ->sortByDesc('count');
    }

    protected function getViewData(): array
    {
        return [
            'browserStats' => $this->getBrowserStats()
        ];
    }
}
