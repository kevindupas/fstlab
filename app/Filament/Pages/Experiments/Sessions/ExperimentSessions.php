<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class ExperimentSessions extends Page implements HasTable
{
    use InteractsWithTable;
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.experiments.sessions.experiment-sessions';

    protected static ?string $slug = 'experiment-sessions/{record}';

    protected static string $recordRouteKeyName = 'id';

    protected static ?string $model = Experiment::class;

    public static function getIdColumn(): string
    {
        return 'id';
    }

    public Experiment $record;

    public function mount(Experiment $record): void
    {

        if (!$this->canAccessExperiment($record)) {
            abort(403, 'Vous n\'avez pas accès à cette expérience.');
        }

        $this->record = $record;
    }

    public function getExperiment(): Experiment
    {
        return $this->record;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $isCreator = $this->record->created_by === $user->id;

        return $table
            ->query(ExperimentSession::query()->where('experiment_id', $this->record->id))
            ->columns([
                TextColumn::make('participant_number')
                    ->label('Identifiant du participant')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('status')
                    ->icons([
                        'heroicon-o-check-circle' => fn($state): bool => $state === 'completed',
                        'heroicon-o-clock' => fn($state): bool => $state === 'created',
                    ])
                    ->colors([
                        'success' => fn($state): bool => $state === 'completed',
                        'warning' => fn($state): bool => $state === 'created',
                    ])
                    ->label('Statut')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Date de complétion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('export')
                    ->label('Export JSON')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn(ExperimentSession $record) => $this->exportJson($record)),

                Action::make('details')
                    ->label('Détails')
                    ->url(fn(ExperimentSession $record): string =>
                    ExperimentSessionDetails::getUrl(['record' => $record]))
                    ->icon('heroicon-o-eye')
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function exportJson(ExperimentSession $session)
    {
        // Vérifier si l'utilisateur est le créateur
        if ($session->experiment->created_by !== Auth::id()) {
            abort(403, 'Seul le créateur peut exporter les données.');
        }

        $data = [
            'participant_name' => $session->participant_name,
            'participant_email' => $session->participant_email,
            'created_at' => $session->created_at,
            'group_data' => json_decode($session->group_data),
            'actions_log' => json_decode($session->actions_log),
            'duration' => $session->duration
        ];

        $filename = "session-{$session->id}-" . date('Y-m-d') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Content-Type', 'application/json');
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString('Participants pour l\'expérimentation' . ' : ' . $this->record->name);
    }

    protected function authorizeAccess(): void
    {
        if (!$this->canAccessExperiment($this->record)) {
            abort(403);
        }
    }
}
