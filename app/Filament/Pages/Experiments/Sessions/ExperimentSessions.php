<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $baseQuery = ExperimentSession::query()
            ->where('experiment_id', $this->record->id);

        return $table
            ->query($baseQuery)
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
                    ->label('Exporter les données')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn (ExperimentSession $record): bool => $record->status !== 'completed')
                    ->url(fn (ExperimentSession $record): string =>
                    ExperimentSessionExport::getUrl(['record' => $record->id])),

                Action::make('details')
                    ->label('Détails')
                    ->hidden(fn (ExperimentSession $record): bool => $record->status !== 'completed')
                    ->url(fn(ExperimentSession $record): string =>
                    ExperimentSessionDetails::getUrl(['record' => $record]))
                    ->icon('heroicon-o-eye')
            ])
            ->headerActions([
                Action::make('exportAll')
                    ->label('Exporter tous')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn () => $baseQuery->count() === 0)
                    ->requiresConfirmation()
                    ->action(function () use ($baseQuery) {
                        // On récupère uniquement les sessions complétées pour l'export
                        $completedRecords = $baseQuery->where('status', 'completed')->get();

                        if ($completedRecords->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Aucune session complétée à exporter')
                                ->send();
                            return;
                        }

                        return $this->exportSessions($completedRecords);
                    })
            ])

            ->bulkActions([
                BulkAction::make('exportSelection')
                    ->label('Exporter la sélection')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        // Filtrer les sessions non complétées si jamais
                        $completedRecords = $records->filter(fn($record) => $record->status === 'completed');

                        if ($completedRecords->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Aucune session complétée sélectionnée')
                                ->send();
                            return;
                        }

                        return $this->exportSessions($completedRecords);
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function exportSessions(Collection $records): StreamedResponse
    {
        return response()->streamDownload(function () use ($records) {
            $csv = Writer::createFromString();
            $isHeaderWritten = false;

            foreach ($records as $session) {
                $data = $this->prepareSessionData($session);

                if (!$isHeaderWritten) {
                    $csv->insertOne(array_keys($data[0]));
                    $isHeaderWritten = true;
                }

                $csv->insertAll($data);
            }

            echo $csv->toString();
        }, "sessions-export-" . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function prepareSessionData(ExperimentSession $session): array
    {
        // On prépare les données de base que vous voulez toujours exporter
        $baseData = [
            'Participant' => $session->participant_number,
            'Date création' => $session->created_at->format('Y-m-d H:i:s'),
            'Date complétion' => $session->completed_at?->format('Y-m-d H:i:s'),
            'Durée (s)' => number_format($session->duration / 1000, 2),
            'Navigateur' => $session->browser,
            'Système' => $session->operating_system,
            'Appareil' => $session->device_type,
            'Dimensions écran' => "{$session->screen_width}x{$session->screen_height}",
            'Feedback' => $session->feedback,
        ];

        $groupData = json_decode($session->group_data, true);

        // Ajout des données de groupe
        foreach ($groupData as $groupIndex => $group) {
            $groupPrefix = "Groupe " . ($groupIndex + 1);
            $baseData["{$groupPrefix} - Nom"] = $group['name'];
            $baseData["{$groupPrefix} - Commentaire"] = $group['comment'] ?? '';

            // Ajout des médias du groupe
            $mediaNames = collect($group['elements'])
                ->map(fn($element) => basename($element['url']))
                ->join(', ');
            $baseData["{$groupPrefix} - Médias"] = $mediaNames;

            // Ajout des interactions et positions
            foreach ($group['elements'] as $element) {
                $mediaName = basename($element['url']);
                $baseData["{$groupPrefix} - {$mediaName} - Interactions"] = $element['interactions'] ?? 0;
                $baseData["{$groupPrefix} - {$mediaName} - Position"] =
                    "X:" . number_format($element['x'], 2) .
                    ", Y:" . number_format($element['y'], 2);
            }
        }

        return [$baseData];
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
