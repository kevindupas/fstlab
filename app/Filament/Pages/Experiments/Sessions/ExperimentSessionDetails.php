<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    public $searchTerm = '';

    public function updatedSearchTerm()
    {
        $this->dispatch('search-updated');
    }

    public function mount(ExperimentSession $record = null): void
    {
        // Vérifie si l'enregistrement existe
        if (!$record) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')->with('error', 'Session not found');
            return;
        }

        $this->record = $record;

        // Vérifie si c'est le créateur ou un compte secondaire du créateur
        $this->isCreator = $record->experiment->created_by === Auth::id() ||
            Auth::user()->created_by === $record->experiment->created_by;

        // Soit c'est le créateur/secondaire, soit il a accès via le trait
        if (!$this->isCreator && !$this->canAccessExperiment($record->experiment)) {
            abort(403, __('filament.pages.experiments_sessions_details.access_denied'));
        }
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
                            ->formatStateUsing(function ($state) {
                                if (!$state) {
                                    return __('filament.pages.experiments_sessions_details.na');
                                }

                                if (!empty($this->searchTerm)) {
                                    return new HtmlString(
                                        preg_replace(
                                            '/(' . preg_quote($this->searchTerm, '/') . ')/i',
                                            '<span class="bg-orange-500 dark:bg-orange-500 rounded-sm px-1">$1</span>',
                                            e($state)
                                        )
                                    );
                                }

                                return $state;
                            })
                            ->html(),
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

    protected function getSearchResults(): array
    {
        if (empty($this->searchTerm)) {
            return [
                'count' => 0,
                'locations' => []
            ];
        }

        $term = $this->searchTerm;
        $count = 0;
        $locations = [];

        // Recherche dans les groupes
        $groups = json_decode($this->record->group_data);
        foreach ($groups as $group) {
            // Comptage dans les commentaires
            if ($group->comment && stripos($group->comment, $term) !== false) {
                $count += substr_count(strtolower($group->comment), strtolower($term));
                $locations['comments'] = ($locations['comments'] ?? 0) + 1;
            }
        }

        // Comptage dans le feedback
        if ($this->record->feedback && stripos($this->record->feedback, $term) !== false) {
            $occurences = substr_count(strtolower($this->record->feedback), strtolower($term));
            $count += $occurences;
            $locations['feedback'] = $occurences;
        }

        return [
            'count' => $count,
            'locations' => $locations
        ];
    }

    protected function getViewData(): array
    {
        $groups = json_decode($this->record->group_data);
        $actionsLog = collect(json_decode($this->record->actions_log));

        if ($this->searchTerm) {
            // On utilise un clone des groupes pour les modifications
            $groups = collect($groups)->map(function ($group) {
                $groupClone = clone $group;
                if ($group->comment) {
                    $groupClone->comment = strip_tags($group->comment); // On s'assure qu'il n'y a pas déjà de HTML
                }
                return $groupClone;
            });
        }

        return [
            'session' => $this->record,
            'groups' => $groups,
            'actionsLog' => $actionsLog->map(function ($action) {
                return [
                    'id' => $action->id,
                    'type' => $action->type ?? 'move',
                    'x' => $action->x ?? null,
                    'y' => $action->y ?? null,
                    'time' => \Carbon\Carbon::createFromTimestampMs($action->time)->format('H:i:s.v'),
                ];
            }),
            'searchTerm' => $this->searchTerm,
            'searchResults' => $this->getSearchResults(),
        ];
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        $actions[] = Action::make('search')
            ->form([
                TextInput::make('searchTerm')
                    ->label('Rechercher dans la session')
                    ->placeholder('Ex: jaune, rouge, etc.')
                    ->default($this->searchTerm)
            ])
            ->action(function (array $data): void {
                $this->searchTerm = $data['searchTerm'];
            });


        if ($this->isCreator) {
            $actions[] = Action::make('addNote')
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
                ->color('warning');
        }

        return $actions;
    }

    public function getBreadcrumbs(): array
    {
        return [
            // Correction du breadcrumb qui pointait vers une page inexistante
            url()->route('filament.admin.resources.experiment-sessions.index', ['record' => $this->record->experiment->id]) =>
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
