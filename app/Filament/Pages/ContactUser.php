<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Resources\UserResource;
use App\Models\Experiment;
use App\Models\User;
use App\Notifications\SupervisorMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ContactUser extends Page
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $slug = 'contact-user';
    protected static string $view = 'filament.pages.contact-user';
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    public ?User $user = null;
    public ?Experiment $experiment = null;
    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user?->hasRole('supervisor') ?? false;
    }

    public function mount(): void
    {
        // Récupérer les IDs depuis la query string
        $userId = request()->query('user');
        $experimentId = request()->query('experiment');

        $this->user = $userId ? User::find($userId) : null;
        $this->experiment = $experimentId ? Experiment::find($experimentId) : null;

        $this->form->fill([
            'user_id' => $this->user?->id,
            'experiment_id' => $this->experiment?->id,
        ]);
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label(__('filament.pages.user_contact.form.user'))
                    ->options(
                        User::role('principal_experimenter')
                            ->where('status', 'approved')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        // Réinitialiser experiment_id quand user_id change
                        $set('experiment_id', null);
                    })
                    ->disabled(fn() => $this->user !== null),

                Select::make('experiment_id')
                    ->label(__('filament.pages.user_contact.form.experiment'))
                    ->options(function ($get) {
                        $userId = $get('user_id');
                        if ($userId) {
                            return Experiment::where('created_by', $userId)->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->reactive()
                    ->disabled(fn() => $this->experiment !== null),

                MarkdownEditor::make('message')
                    ->label(__('filament.pages.user_contact.form.message.label'))
                    ->placeholder(__('filament.pages.user_contact.form.message.placeholder'))
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'orderedList',
                        'italic',
                        'link',
                        'undo',
                        'redo',
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = User::findOrFail($data['user_id']);
        $experiment = $data['experiment_id'] ? Experiment::find($data['experiment_id']) : null;

        // Envoyer la notification
        $user->notify(new SupervisorMessage(
            message: $data['message'],
            experiment: $experiment,
            supervisor: Auth::user()
        ));

        Notification::make()
            ->title(__('filament.pages.admin_contact.form.success'))
            ->success()
            ->send();

        // Rediriger selon le contexte
        if ($this->preSelectedExperiment) {
            $this->redirect(ExperimentDetails::getUrl(['record' => $this->preSelectedExperiment]));
        } elseif ($this->preSelectedUser) {
            $this->redirect(UserResource::getUrl());
        } else {
            $this->form->fill();
        }
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.user_contact.title');
    }
    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.user_contact.title'));
    }

}
