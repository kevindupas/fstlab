<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use App\Models\Experiment;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\StatsOverviewWidget;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\CompletionChartWidget;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\DeviceTypeChartWidget;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\DurationChartWidget;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\ActionsTimelineChartWidget;
use App\Filament\Resources\ExperimentResource\Pages\ExperimentStatistics\Widgets\ErrorsByTypeChartWidget;
use Filament\Resources\Concerns\Translatable;

class ExperimentStatistics extends Page
{
    protected static string $resource = ExperimentResource::class;

    protected static string $view = 'filament.resources.experiment-resource.pages.experiment-statistics';

    public Experiment $record;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::make([
                'record' => $this->record,
            ]),
            CompletionChartWidget::make([
                'record' => $this->record,
            ]),
            DeviceTypeChartWidget::make([
                'record' => $this->record,
            ]),
            DurationChartWidget::make([
                'record' => $this->record,
            ]),
            ActionsTimelineChartWidget::make([
                'record' => $this->record,
            ]),
            ErrorsByTypeChartWidget::make([
                'record' => $this->record,
            ]),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString('Statistiques pour l\'expérimentation : ' . $this->record->name);
    }
}
