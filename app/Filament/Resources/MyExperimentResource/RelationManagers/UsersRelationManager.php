<?php

namespace App\Filament\Resources\MyExperimentResource\RelationManagers;

use App\Models\User;
use App\Notifications\AddedToExperimentNotification;
use App\Notifications\ResetPasswordNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected array $initialState = [];

    public function getTableHeading(): string
    {
        return __('filament.resources.add_user_to_experiment.heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('can_pass')->label(__('filament.resources.add_user_to_experiment.can_pass')),
                Forms\Components\Toggle::make('can_configure')->label(__('filament.resources.add_user_to_experiment.can_configure')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('filament.resources.add_user_to_experiment.name')),
                Tables\Columns\TextColumn::make('email')->label(__('filament.resources.add_user_to_experiment.email')),
                Tables\Columns\ToggleColumn::make('can_pass')
                    ->label(__('filament.resources.add_user_to_experiment.can_pass'))
                    ->afterStateUpdated(function ($record, $state) {
                        $this->handlePermissionChange($record, $state);
                    }),
                Tables\Columns\ToggleColumn::make('can_configure')
                    ->label(__('filament.resources.add_user_to_experiment.can_configure'))
                    ->afterStateUpdated(function ($record, $state) {
                        $this->handlePermissionChange($record, $state);
                    }),
            ])
            ->headerActions([
                // Bouton pour ajouter un nouvel utilisateur
                Tables\Actions\CreateAction::make('create_user')
                    ->label(__('filament.resources.add_user_to_experiment.create_user'))
                    ->modalHeading(__('filament.resources.add_user_to_experiment.create_user'))
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label(__('filament.resources.add_user_to_experiment.name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label(__('filament.resources.add_user_to_experiment.email'))
                            ->unique('users', 'email'),
                        Forms\Components\TextInput::make('university')
                            ->label(__('filament.resources.add_user_to_experiment.university'))
                            ->required(),
                        Forms\Components\Toggle::make('can_pass')->label(__('filament.resources.add_user_to_experiment.can_pass')),
                        Forms\Components\Toggle::make('can_configure')->label(__('filament.resources.add_user_to_experiment.can_configure')),
                    ])
                    ->action(function (array $data): void {
                        // Création de l'utilisateur
                        $userData = [
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'university' => $data['university'],
                            'password' => bcrypt(Str::random(32)),
                            'created_by' => Auth::id(),
                            'status' => 'approved',
                            'email_verified_at' => now(),
                        ];

                        $user = User::create($userData);
                        $user->assignRole('secondary_experimenter');
                        $user->notify(new ResetPasswordNotification());
                        $user->notify(new AddedToExperimentNotification($this->getOwnerRecord()->name));

                        // Attacher l'utilisateur à l'expérience
                        $this->getOwnerRecord()->users()->attach($user->id, [
                            'can_pass' => $data['can_pass'] ?? false,
                            'can_configure' => $data['can_configure'] ?? false,
                        ]);

                        // Créer le lien d'expérience si nécessaire
                        if ($data['can_pass'] || $data['can_configure']) {
                            $this->createExperimentLink($user->id);
                        }

                        Notification::make()
                            ->title(__('filament.resources.add_user_to_experiment.user_created'))
                            ->success()
                            ->send();
                    }),

                // Bouton pour attacher un utilisateur existant
                Tables\Actions\Action::make('attach_user')
                    ->label(__('filament.resources.add_user_to_experiment.attach_user'))
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label(__('filament.resources.add_user_to_experiment.user_email'))
                            ->email()
                            ->required(),
                        Forms\Components\Toggle::make('can_pass')->label(__('filament.resources.add_user_to_experiment.can_pass')),
                        Forms\Components\Toggle::make('can_configure')->label(__('filament.resources.add_user_to_experiment.can_configure')),
                    ])
                    ->action(function (array $data): void {
                        // Recherche de l'utilisateur avec le bon rôle
                        $user = User::where('email', $data['email'])
                            ->whereHas('roles', function ($query) {
                                $query->whereIn('name', ['principal_experimenter', 'secondary_experimenter']);
                            })
                            ->first();

                        if (!$user) {
                            Notification::make()
                                ->title(__('filament.resources.add_user_to_experiment.user_not_found'))
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            // Attacher l'utilisateur à l'expérience
                            $this->getOwnerRecord()->users()->attach($user->id, [
                                'can_pass' => $data['can_pass'] ?? false,
                                'can_configure' => $data['can_configure'] ?? false,
                            ]);

                            // Créer le lien d'expérience si nécessaire
                            if ($data['can_configure'] || $data['can_pass']) {
                                $this->createExperimentLink($user->id);
                            }

                            $user->notify(new AddedToExperimentNotification($this->getOwnerRecord()->name));


                            Notification::make()
                                ->title(__('filament.resources.add_user_to_experiment.user_attached'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('filament.resources.add_user_to_experiment.attachment_error'))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('contact')
                    ->label(__('filament.resources.add_user_to_experiment.contact_user'))
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->url(fn(User $record) => "/admin/contact-user?user={$record->id}"),
                Tables\Actions\EditAction::make()
                    ->label(__('filament.resources.add_user_to_experiment.edit_user'))
                    ->before(function ($data, $record) {
                        $this->initialState = [
                            'can_pass' => $record->pivot->can_pass,
                            'can_configure' => $record->pivot->can_configure,
                        ];
                    })
                    ->after(function ($data, $record) {
                        if (
                            ($this->initialState['can_pass'] || $this->initialState['can_configure']) &&
                            !$data['can_pass'] && !$data['can_configure']
                        ) {
                            $this->deleteExperimentLink($record->id);
                        }
                    }),
                Tables\Actions\DetachAction::make()
                    ->label(__('filament.resources.add_user_to_experiment.detach_user'))
                    ->before(function ($record) {
                        $this->deleteExperimentLink($record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->before(function (Collection $records) {
                        $records->each(fn($record) => $this->deleteExperimentLink($record->id));
                    }),
            ]);
    }

    protected function handlePermissionChange($record, $state): void
    {
        if (!$state && !$record->pivot->can_pass && !$record->pivot->can_configure) {
            $this->deleteExperimentLink($record->id);
        } elseif ($state) {
            $this->createExperimentLink($record->id);
        }
    }

    protected function createExperimentLink($userId): void
    {
        $currentStatus = \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
            ->where('user_id', $this->getOwnerRecord()->created_by)
            ->first();

        if ($currentStatus) {
            \App\Models\ExperimentLink::updateOrCreate(
                [
                    'experiment_id' => $this->getOwnerRecord()->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => $currentStatus->status,
                    'link' => Str::random(6),
                    'is_creator' => false,
                    'is_secondary' => true,
                    'is_collaborator' => false
                ]
            );
        }
    }

    protected function deleteExperimentLink($userId): void
    {
        \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
            ->where('user_id', $userId)
            ->delete();
    }
}
