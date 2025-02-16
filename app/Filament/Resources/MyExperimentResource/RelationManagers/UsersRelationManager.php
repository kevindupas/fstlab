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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected array $initialState = [];

    public function getTableHeading(): string
    {
        return "Utilisateurs associés à l'expérimentation";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('can_configure')->label("Configuration de l'expérimentation"),
                Forms\Components\Toggle::make('can_pass')->label('Faire passer des sessions'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                //Tables\Columns\TextColumn::make('roles.name')
                    //->label('Role'),
                Tables\Columns\ToggleColumn::make('can_configure')
                    ->label("Configuration de l'expérimentation")
                    ->afterStateUpdated(function ($record, $state) {
                        $this->handlePermissionChange($record, $state);
                    }),
                Tables\Columns\ToggleColumn::make('can_pass')
                    ->label('Faire passer des sessions')
                    ->afterStateUpdated(function ($record, $state) {
                        $this->handlePermissionChange($record, $state);
                    }),
            ])
            ->headerActions([
                // Bouton pour ajouter un nouvel utilisateur
                Tables\Actions\CreateAction::make('create_user')
                    ->label('Créer un utilisateur')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label("Nom de l'utilisateur")
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label("Adresse email")
                            ->unique('users', 'email'),
                        Forms\Components\TextInput::make('university')
                            ->label("Univeristé")
                            ->required(),
                        Forms\Components\Toggle::make('can_configure')->label("Configuration de l'expérimentation"),
                        Forms\Components\Toggle::make('can_pass')->label('Faire passer des sessions'),
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
                            'can_configure' => $data['can_configure'] ?? false,
                            'can_pass' => $data['can_pass'] ?? false,
                        ]);

                        // Créer le lien d'expérience si nécessaire
                        if ($data['can_configure'] || $data['can_pass']) {
                            $this->createExperimentLink($user->id);
                        }

                        Notification::make()
                            ->title('Utilisateur créé et attaché avec succès')
                            ->success()
                            ->send();
                    }),

                // Bouton pour attacher un utilisateur existant
                Tables\Actions\Action::make('attach_user')
                    ->label('Attacher un utilisateur')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email de l\'utilisateur')
                            ->email()
                            ->required(),
                        Forms\Components\Toggle::make('can_configure')->label("Configuration de l'expérimentation"),
                        Forms\Components\Toggle::make('can_pass')->label('Faire passer des sessions'),
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
                                ->title('Utilisateur non trouvé ou non autorisé')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            // Attacher l'utilisateur à l'expérience
                            $this->getOwnerRecord()->users()->attach($user->id, [
                                'can_configure' => $data['can_configure'] ?? false,
                                'can_pass' => $data['can_pass'] ?? false,
                            ]);

                            // Créer le lien d'expérience si nécessaire
                            if ($data['can_configure'] || $data['can_pass']) {
                                $this->createExperimentLink($user->id);
                            }

                            $user->notify(new AddedToExperimentNotification($this->getOwnerRecord()->name));


                            Notification::make()
                                ->title('Utilisateur attaché avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur lors de l\'attachement')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('contact')
                    ->label(__('filament.resources.users.actions.contact'))
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->url(fn(User $record) => "/admin/contact-user?user={$record->id}"),
                // ->visible(
                //     fn(User $record) =>
                //     Auth::user()->hasRole('supervisor') ||
                //         ($record->created_by === Auth::id())
                // ),
                Tables\Actions\EditAction::make()
                    ->before(function ($data, $record) {
                        $this->initialState = [
                            'can_configure' => $record->pivot->can_configure,
                            'can_pass' => $record->pivot->can_pass,
                        ];
                    })
                    ->after(function ($data, $record) {
                        if (
                            ($this->initialState['can_configure'] || $this->initialState['can_pass']) &&
                            !$data['can_configure'] && !$data['can_pass']
                        ) {
                            $this->deleteExperimentLink($record->id);
                        }
                    }),
                Tables\Actions\DetachAction::make()
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
