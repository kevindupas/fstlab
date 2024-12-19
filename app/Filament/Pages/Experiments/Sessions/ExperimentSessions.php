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
            abort(403, __('filament.pages.experiments_sessions.access_denied'));
        }

        $this->record = $record;
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
                    ->label(__('filament.pages.experiments_sessions.columns.participant_number'))
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
                    ->label(__('filament.pages.experiments_sessions.columns.status'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filament.pages.experiments_sessions.columns.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label(__('filament.pages.experiments_sessions.columns.completed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('export')
                    ->label(__('filament.pages.experiments_sessions.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn(ExperimentSession $record): bool => $record->status !== 'completed')
                    ->url(fn(ExperimentSession $record): string =>
                    ExperimentSessionExport::getUrl(['record' => $record->id])),

                Action::make('details')
                    ->label(__('filament.pages.experiments_sessions.actions.details'))
                    ->hidden(fn(ExperimentSession $record): bool => $record->status !== 'completed')
                    ->url(fn(ExperimentSession $record): string =>
                    ExperimentSessionDetails::getUrl(['record' => $record]))
                    ->icon('heroicon-o-eye')
            ])
            ->headerActions([
                Action::make('exportAll')
                    ->label(__('filament.pages.experiments_sessions.actions.export_all'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn() => $baseQuery->count() === 0)
                    ->requiresConfirmation()
                    ->action(function () use ($baseQuery) {
                        $completedRecords = $baseQuery->where('status', 'completed')->get();

                        if ($completedRecords->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title(__('filament.pages.experiments_sessions.notifications.no_completed_sessions'))
                                ->send();
                            return;
                        }

                        return $this->exportSessions($completedRecords);
                    })
            ])
            ->bulkActions([
                BulkAction::make('exportSelection')
                    ->label(__('filament.pages.experiments_sessions.actions.export_selection'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        $completedRecords = $records->filter(fn($record) => $record->status === 'completed');

                        if ($completedRecords->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title(__('filament.pages.experiments_sessions.notifications.no_selection_completed'))
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
        $baseData = [
            __('filament.pages.experiments_sessions.csv.participant') => $session->participant_number,
            __('filament.pages.experiments_sessions.csv.created_at') => $session->created_at->format('Y-m-d H:i:s'),
            __('filament.pages.experiments_sessions.csv.completed_at') => $session->completed_at?->format('Y-m-d H:i:s'),
            __('filament.pages.experiments_sessions.csv.duration') => number_format($session->duration / 1000, 2),
            __('filament.pages.experiments_sessions.csv.browser') => $session->browser,
            __('filament.pages.experiments_sessions.csv.system') => $session->operating_system,
            __('filament.pages.experiments_sessions.csv.device') => $session->device_type,
            __('filament.pages.experiments_sessions.csv.screen_dimensions') => "{$session->screen_width}x{$session->screen_height}",
            __('filament.pages.experiments_sessions.csv.feedback') => $session->feedback,
        ];

        $groupData = json_decode($session->group_data, true);

        foreach ($groupData as $groupIndex => $group) {
            $groupNumber = $groupIndex + 1;
            $baseData[__('filament.pages.experiments_sessions.csv.group_name', ['number' => $groupNumber])] = $group['name'];
            $baseData[__('filament.pages.experiments_sessions.csv.group_comment', ['number' => $groupNumber])] = $group['comment'] ?? '';

            $mediaNames = collect($group['elements'])
                ->map(fn($element) => basename($element['url']))
                ->join(', ');
            $baseData[__('filament.pages.experiments_sessions.csv.group_media', ['number' => $groupNumber])] = $mediaNames;

            foreach ($group['elements'] as $element) {
                $mediaName = basename($element['url']);
                $baseData[__('filament.pages.experiments_sessions.csv.group_media_interactions', [
                    'number' => $groupNumber,
                    'media' => $mediaName
                ])] = $element['interactions'] ?? 0;

                $baseData[__('filament.pages.experiments_sessions.csv.group_media_position', [
                    'number' => $groupNumber,
                    'media' => $mediaName
                ])] = "X:" . number_format($element['x'], 2) . ", Y:" . number_format($element['y'], 2);
            }
        }

        return [$baseData];
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.experiments_sessions.title', ['name' => $this->record->name]));
    }

    protected function authorizeAccess(): void
    {
        if (!$this->canAccessExperiment($this->record)) {
            abort(403);
        }
    }
}
