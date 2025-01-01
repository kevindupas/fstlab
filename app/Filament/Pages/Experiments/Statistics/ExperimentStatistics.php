<?php

namespace App\Filament\Pages\Experiments\Statistics;

use App\Filament\Pages\Experiments\Statistics\Widgets\ActionsTimelineChartWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\CompletionChartWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\DeviceTypeChartWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\DurationChartWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\ErrorsByTypeChartWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\StatsOverviewWidget;
use App\Models\Experiment;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class ExperimentStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.experiments.statistics.experiment-statistics';

    protected static ?string $slug = 'experiment-statistics/{record}';
    protected static ?string $model = Experiment::class;

    public Experiment $record;

    public function mount(Experiment $record): void
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est le créateur ou un compte secondaire
        $isCreatorOrSecondary = $record->created_by === $user->id ||
            $user->created_by === $record->created_by;

        // Vérifier si l'utilisateur a un accès approuvé
        $hasAccess = $record->accessRequests()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();

        if (!$isCreatorOrSecondary && !$hasAccess) {
            abort(403, 'You do not have permission to access these statistics.');
        }

        $this->record = $record;
    }

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
        return new HtmlString(__('filament.pages.experiments_statistics.title', [
            'name' => $this->record->name
        ]));
    }
}
