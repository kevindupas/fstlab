<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;

class ExperimentSessions extends Page implements HasTable
{
    use InteractsWithTable;
    use HasExperimentAccess;

    protected static string $resource = ExperimentResource::class;

    protected static string $view = 'filament.resources.experiment-resource.pages.experiment-sessions';

    public $experiment;

    public function mount($record): void
    {
        $experiment = Experiment::findOrFail($record);

        if (!$this->canAccessExperiment($experiment)) {
            abort(403, 'Vous n\'avez pas accès à cette expérience.');
        }

        $this->experiment = $experiment;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $isCreator = $this->experiment->created_by === $user->id;

        return $table
            ->query(ExperimentSession::query()->where('experiment_id', $this->experiment->id))
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
                // ->visible($isCreator),

                Action::make('details')
                    ->label('Détails')
                    ->url(
                        fn(ExperimentSession $record): string =>
                        static::getResource()::getUrl('session-details', ['record' => $record->id])
                    )
                    ->icon('heroicon-o-eye'),
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
        return new HtmlString('Participants pour l\'expérimentation' . ' : ' . $this->experiment->name);
    }

    // Ajout d'une méthode pour vérifier l'accès à la page
    protected function authorizeAccess(): void
    {
        if (!$this->canAccessExperiment($this->experiment)) {
            abort(403);
        }
    }
}
