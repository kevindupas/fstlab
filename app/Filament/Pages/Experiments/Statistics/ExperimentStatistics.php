<?php

namespace App\Filament\Pages\Experiments\Statistics;

use App\Filament\Pages\Experiments\Statistics\Widgets\BrowserStatsWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\CanvasSizeStatsWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\GlobalStatsWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\MediaInteractionsWidget;
use App\Filament\Pages\Experiments\Statistics\Widgets\SessionStatsWidget;
use App\Models\Experiment;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
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
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifier si l'utilisateur a le droit d'accéder
        $hasAccess =
            // C'est le créateur
            $record->created_by === $user->id ||
            // C'est un compte secondaire du créateur
            $user->created_by === $record->created_by ||
            // C'est un superviseur (sauf si l'expérimentation appartient à un autre superviseur)
            ($user->hasRole('supervisor') && !User::find($record->created_by)->hasRole('supervisor')) ||
            // C'est un collaborateur qui a accès via les access_requests
            $record->accessRequests()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists() ||
            // C'est un utilisateur qui a été ajouté directement à l'expérimentation
            $record->users()
            ->where('users.id', $user->id)
            ->exists();

        if (!$hasAccess) {
            // Rediriger vers la liste des expérimentations
            abort(403, 'You do not have permission to access these statistics.');
        }

        $this->record = $record;
    }
    protected function getHeaderWidgets(): array
    {
        return [
            SessionStatsWidget::make([
                'record' => $this->record,
            ]),
            GlobalStatsWidget::make([
                'record' => $this->record,
            ]),
            MediaInteractionsWidget::make([
                'record' => $this->record,
            ]),
            BrowserStatsWidget::make([
                'record' => $this->record,
            ]),
            CanvasSizeStatsWidget::make([
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
