<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;

class ExperimentSessionDetails extends Page
{
    use HasExperimentAccess;


    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'experiment-session-details/{record}';
    protected static ?string $model = ExperimentSession::class;
    protected static string $view = 'filament.pages.experiments.sessions.experiment-session-details';

    public ExperimentSession $record;
    public bool $isCreator;


    public function mount(ExperimentSession $record): void
    {
        $this->record = $record;

        if (!$this->canAccessExperiment($this->record->experiment)) {
            abort(403, __('filament.pages.experiments_sessions_details.access_denied'));
        }

        $this->isCreator = $this->record->experiment->created_by === Auth::id();
    }


    public function sessionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make(__('filament.pages.experiments_sessions_details.sections.participant'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('participant_number')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.participant_number'))
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('created_at')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.created_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('duration')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.duration'))
                                    ->formatStateUsing(fn($state) => number_format($state / 1000, 2) . ' ' . __('filament.pages.experiments_sessions_details.time.seconds'))
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),

                Section::make(__('filament.pages.experiments_sessions_details.sections.technical'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('browser')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.browser')),
                                TextEntry::make('operating_system')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.operating_system')),
                                TextEntry::make('device_type')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.device_type')),
                                TextEntry::make('screen_width')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.screen_width')),
                                TextEntry::make('screen_height')
                                    ->label(__('filament.pages.experiments_sessions_details.fields.screen_height')),
                            ]),
                    ]),

                Section::make(__('filament.pages.experiments_sessions_details.sections.feedback'))
                    ->schema([
                        TextEntry::make('feedback')
                            ->label(__('filament.pages.experiments_sessions_details.fields.feedback'))
                            ->formatStateUsing(fn($state) => $state ?: __('filament.pages.experiments_sessions_details.na')),
                        TextEntry::make('errors_log')
                            ->label(__('filament.pages.experiments_sessions_details.fields.errors'))
                            ->formatStateUsing(function ($state) {
                                if (!$state) return null;
                                $errors = json_decode($state);
                                if (empty($errors)) return null;
                                return collect($errors)->map(function ($error) {
                                    $time = \Carbon\Carbon::createFromTimestampMs($error->time)->format('H:i:s');
                                    return __('filament.pages.experiments_sessions_details.error_format', [
                                        'type' => $error->type,
                                        'time' => $time
                                    ]);
                                })->join('<br>');
                            })
                            ->hidden(fn($state) => !$state || empty(json_decode($state)))
                            ->html()
                    ]),
            ]);
    }

    protected function getViewData(): array
    {
        return [
            'session' => $this->record,
            'groups' => json_decode($this->record->group_data),
            'actionsLog' => collect(json_decode($this->record->actions_log))->map(function ($action) {
                return [
                    'id' => $action->id,
                    'type' => $action->type ?? 'move',
                    'x' => $action->x ?? null,
                    'y' => $action->y ?? null,
                    'time' => \Carbon\Carbon::createFromTimestampMs($action->time)->format('H:i:s.v'),
                ];
            })
        ];
    }

    protected function getHeaderActions(): array
    {
        if (!$this->isCreator) {
            return [];
        }

        return [
            Action::make('addNote')
                ->label(__('filament.pages.experiments_sessions_details.actions.add_note'))
                ->form([
                    Textarea::make('notes')
                        ->label(__('filament.pages.experiments_sessions_details.fields.examiner_notes'))
                        ->default(fn() => $this->record->notes)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'notes' => $data['notes'],
                    ]);

                    Notification::make()
                        ->title(__('filament.pages.experiments_sessions_details.notifications.note_saved'))
                        ->success()
                        ->send();
                })
                ->button()
                ->color('warning'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ExperimentSessions::getUrl(['record' => $this->record->experiment->id]) =>
            __('filament.pages.experiments_sessions_details.breadcrumbs.participants', [
                'name' => $this->record->experiment->name
            ]),
            '#' => __('filament.pages.experiments_sessions_details.breadcrumbs.details', [
                'participant' => $this->record->participant_number
            ])
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.experiments_sessions_details.title', [
            'participant' => $this->record->participant_number
        ]));
    }
}
