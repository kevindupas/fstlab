<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;

class ExperimentSessionDetails extends Page
{

    use HasExperimentAccess;

    protected static string $resource = ExperimentResource::class;

    protected static string $view = 'filament.resources.experiment-resource.pages.experiment-session-details';

    public $session;

    public function mount($record)
    {
        $this->session = ExperimentSession::with('experiment')->findOrFail($record);

        if (!$this->canAccessExperiment($this->session->experiment)) {
            abort(403, 'Vous n\'avez pas accès aux détails de cette session.');
        }

        $this->isCreator = $this->session->experiment->created_by === Auth::id();
    }

    public function sessionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->session)
            ->schema([
                Section::make('Informations du participant')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('participant_number')
                                    ->label('Nom')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('created_at')
                                    ->label('Date de participation')
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('duration')
                                    ->label('Durée')
                                    ->formatStateUsing(fn($state) => number_format($state / 1000, 2) . ' secondes')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),

                Section::make('Informations techniques')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('browser')
                                    ->label('Navigateur'),
                                TextEntry::make('operating_system')
                                    ->label('Système d\'exploitation'),
                                TextEntry::make('device_type')
                                    ->label('Type d\'appareil'),
                                TextEntry::make('screen_width')
                                    ->label('Largeur d\'écran'),
                                TextEntry::make('screen_height')
                                    ->label('Hauteur d\'écran'),
                            ]),
                    ]),

                Section::make('Feedback et Notes')
                    ->schema([
                        TextEntry::make('feedback')
                            ->label('Feedback du participant'),
                        TextEntry::make('errors_log')
                            ->label('Erreurs rapportées')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return 'Aucune erreur';
                                $errors = json_decode($state);
                                return collect($errors)->map(function ($error) {
                                    $time = \Carbon\Carbon::createFromTimestampMs($error->time)->format('H:i:s');
                                    return "Erreur {$error->type} à {$time}";
                                })->join('<br>');
                            })
                            ->html(),
                        // Notes visibles uniquement pour le créateur
                        TextEntry::make('notes')
                            ->label('Notes de l\'examinateur')
                            ->visible(fn() => $this->isCreator),
                    ]),
            ]);
    }

    protected function getViewData(): array
    {
        return [
            'session' => $this->session,
            'groups' => json_decode($this->session->group_data),
            'actionsLog' => collect(json_decode($this->session->actions_log))->map(function ($action) {
                return [
                    'id' => $action->id,
                    'x' => $action->x,
                    'y' => $action->y,
                    'time' => \Carbon\Carbon::createFromTimestampMs($action->time)->format('H:i:s.v'),
                ];
            })
        ];
    }

    protected function getHeaderActions(): array
    {
        // Seul le créateur peut ajouter/modifier des notes
        if (!$this->isCreator) {
            return [];
        }

        return [
            Action::make('addNote')
                ->label('Ajouter/Modifier la note')
                ->form([
                    Textarea::make('notes')
                        ->label('Notes de l\'examinateur')
                        ->default(fn() => $this->session->notes)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->session->update([
                        'notes' => $data['notes'],
                    ]);

                    Notification::make()
                        ->title('Note enregistrée avec succès')
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
            route('filament.admin.resources.experiments.index') => 'Experiments',
            route('filament.admin.resources.experiments.sessions', ['record' => $this->session->experiment->id]) => "Participants pour l'expérimentation : {$this->session->experiment->name}",
            '#' => "Détails de la session - {$this->session->participant_number}"
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString("Détails de la session - {$this->session->participant_number}");
    }
}
