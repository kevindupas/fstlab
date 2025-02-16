<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Resources\UserResource;
use App\Models\Experiment;
use App\Models\User;
use App\Notifications\UserMessage;
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

    public function mount(): void
    {
        $userId = request()->query('user');
        $experimentId = request()->query('experiment');

        $this->user = $userId ? User::find($userId) : null;
        $this->experiment = $experimentId ? Experiment::find($experimentId) : null;

        // Initialiser le formulaire avec les valeurs par défaut
        $this->form->fill([
            'user_id' => $this->user?->id,
            'experiment_id' => $this->experiment?->id,
            'message' => ''
        ]);
    }

    public function form(Form $form): Form
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $form
            ->schema([
                Select::make('user_id')
                    ->label(__('pages.user_contact.form.user'))
                    ->options(function () use ($user) {
                        if ($user->hasRole('supervisor')) {
                            return User::role('principal_experimenter')
                                ->where('status', 'approved')
                                ->pluck('name', 'id');
                        } else {
                            // Pour les principaux :
                            return User::where(function($query) use ($user) {
                                // Leurs comptes secondaires
                                $query->where('created_by', $user->id)
                                    // Les principaux ajoutés à leurs expérimentations
                                    ->orWhereHas('experiments', function($q) use ($user) {
                                        $q->where('experiments.created_by', $user->id);
                                    })
                                    // Les créateurs des expérimentations où ils sont ajoutés
                                    ->orWhereHas('createdExperiments', function($q) use ($user) {
                                        $q->whereHas('users', function($sq) use ($user) {
                                            $sq->where('users.id', $user->id);
                                        });
                                    });
                            })
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    return [$user->id => "{$user->name} ({$user->email})"];
                                });
                        }
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('experiment_id', null);
                    })
                    ->disabled(fn() => $this->user !== null)
                    ->default(fn() => $this->user?->id),

                Select::make('experiment_id')
                    ->label(__('pages.user_contact.form.experiment'))
                    ->options(function ($get) use ($user) {
                        $userId = $get('user_id') ?? $this->user?->id;
                        if (!$userId) return [];

                        if ($user->hasRole('supervisor')) {
                            return Experiment::where('created_by', $userId)->pluck('name', 'id');
                        } else {
                            return Experiment::where(function($query) use ($user, $userId) {
                                $query->where('created_by', $userId)
                                    ->whereHas('users', function($q) use ($user) {
                                        $q->where('users.id', $user->id);
                                    });
                            })
                                ->orWhere(function($query) use ($user, $userId) {
                                    $query->where('created_by', $user->id)
                                        ->whereHas('users', function($q) use ($userId) {
                                            $q->where('users.id', $userId);
                                        });
                                })
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->reactive()
                    ->disabled(fn() => $this->experiment !== null),

                MarkdownEditor::make('message')
                    ->label(__('pages.user_contact.form.message.label'))
                    ->placeholder(__('pages.user_contact.form.message.placeholder'))
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

        $userId = $data['user_id'] ?? $this->user?->id;
        if (!$userId) {
            Notification::make()
                ->title(__('pages.user_contact.form.error'))
                ->danger()
                ->body(__('pages.user_contact.form.no_user'))
                ->send();
            return;
        }

        $user = User::findOrFail($userId);
        $experiment = isset($data['experiment_id']) ? Experiment::find($data['experiment_id']) : null;
        $sender = Auth::user();

        // Une seule notification qui s'adapte selon le rôle
        $user->notify(new UserMessage(
            message: $data['message'],
            experiment: $experiment,
            sender: $sender
        ));

        Notification::make()
            ->title(__('pages.admin_contact.form.success'))
            ->success()
            ->send();

        if ($this->experiment) {
            $this->redirect(ExperimentDetails::getUrl(['record' => $this->experiment]));
        } elseif ($this->user) {
            $this->redirect(UserResource::getUrl());
        } else {
            $this->form->fill();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user?->hasAnyRole(['supervisor', 'principal_experimenter']) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return __('pages.user_contact.title');
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('pages.user_contact.title'));
    }
}
