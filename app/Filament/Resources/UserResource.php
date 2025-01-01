<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\UserDeletionNotification;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{

    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = -1;
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getModelLabel(): string
    {
        return __('navigation.approved_user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.approved_user');
    }

    // public static function getNavigationGroup(): ?string
    // {
    //     return __('Utilisateurs');
    // }

    public static function form(Form $form): Form
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $roleOptions = [];

        if ($user->hasRole('supervisor')) {
            $roleOptions = [
                'principal_experimenter' => 'Principal Experimenter'
            ];
        } elseif ($user->hasRole('principal_experimenter')) {
            $roleOptions = [
                'secondary_experimenter' => 'Secondary Experimenter'
            ];
        }

        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament.resources.users.form.name'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                TextInput::make('email')
                    ->label(__('filament.resources.users.form.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                TextInput::make('university')
                    ->label(__('filament.resources.users.form.university'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                Select::make('roles')
                    ->label(__('filament.resources.users.form.role.label'))
                    ->multiple(false)
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->whereNot('name', 'supervisor')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Role $record) => __('filament.resources.users.form.role.options.' . $record->name))
                    ->disableOptionWhen(function (string $value) {
                        $role = Role::where('id', $value)->first();
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return match ($role->name) {
                            'principal_experimenter' => !$user->hasRole('supervisor'),
                            'secondary_experimenter' => !$user->hasRole('principal_experimenter'),
                        };
                    })
                    ->required()
                    ->visible(fn() => !empty($roleOptions))
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),

                Textarea::make('registration_reason')
                    ->label(__('filament.resources.users.form.registration_reason'))
                    ->visible(
                        fn($record, $livewire) =>
                        $livewire instanceof Pages\EditUser &&
                            filled($record->registration_reason)
                    )
                    ->disabled()
                    ->extraAttributes(['class' => 'bg-blue-50'])
                    ->columnSpan('full'),

                Select::make('status')
                    ->label(__('filament.resources.users.form.status.label'))
                    ->options([
                        'approved' => __('filament.resources.users.form.status.options.approved'),
                        'banned' => __('filament.resources.users.form.status.options.banned'),
                    ])
                    ->required()
                    ->live()
                    ->disabled(fn() => !$user->hasRole('supervisor'))
                    ->visible(fn() => $user->hasRole('supervisor')),


                Textarea::make('banned_reason')
                    ->required(fn(Get $get) => $get('status') === 'banned')
                    ->visible(fn(Get $get) => $get('status') === 'banned')
                    ->label(__('filament.resources.users.form.banned_reason'))
                    ->live()
                    ->dehydrated(true)
                    ->columnSpan('full'),


                Section::make(__('filament.resources.users.form.section.history_section'))
                    ->description(__('filament.resources.users.form.section.history_section_description'))
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Textarea::make('registration_reason')
                                    ->label(__('filament.resources.users.form.section.registration_reason'))
                                    ->visible(fn($record) => filled($record->registration_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-blue-300 dark:bg-blue-300']),

                                Textarea::make('rejection_reason')
                                    ->label(__('filament.resources.users.form.section.rejection_reason'))
                                    ->visible(fn($record) => filled($record->rejection_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-red-300 dark:bg-red-300']),

                                Textarea::make('banned_reason')
                                    ->label(__('filament.resources.users.form.section.banned_reason'))
                                    ->visible(fn($record) => filled($record->banned_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-red-300 dark:bg-red-200']),

                                Textarea::make('unbanned_reason')
                                    ->label(__('filament.resources.users.form.section.unbanned_reason'))
                                    ->visible(fn($record) => filled($record->unbanned_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-green-300 dark:bg-green-200']),

                            ])
                            ->columnSpan('full'),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->visible(
                        fn($record, $livewire) =>
                        !($livewire instanceof Pages\CreateUser) &&
                            (
                                filled($record->registration_reason) ||
                                filled($record->rejection_reason) ||
                                filled($record->banned_reason) ||
                                filled($record->unbanned_reason)
                            )
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $table
            ->columns([
                TextColumn::make('name')->searchable()
                    ->label(__('filament.resources.users.table.name'))
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->label(__('filament.resources.users.table.email'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament.resources.users.table.status.label'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => __('filament.resources.users.table.status.approved'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'banned' => 'danger',
                    }),
                TextColumn::make('roles')
                    ->label(__('filament.resources.users.table.role.label'))
                    ->formatStateUsing(function ($state, $record) {
                        return $record->roles->pluck('name')->join(', ');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('contact')
                    ->label(__('filament.resources.users.actions.contact'))
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->url(fn(User $record) => "/admin/contact-user?user={$record->id}")
                    ->visible(
                        fn(User $record) =>
                        $user->hasRole('supervisor') ||
                            ($record->created_by === Auth::id()) // Visible si c'est un secondaire créé par le principal
                    ),
                Tables\Actions\Action::make('experiments')
                    ->label(__('filament.resources.users.actions.show_experiment'))
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->url(fn(User $record) => "/admin/experiments-list?filter_user={$record->id}")
                    ->visible(fn() => $user->hasRole('supervisor')),
                Tables\Actions\Action::make('shared_experiments')
                    ->label('Expérimentations partagées')
                    ->icon('heroicon-o-share')
                    ->color('warning')
                    ->url(fn(User $record) => "/admin/shared-experiments?filter_user={$record->id}")
                    ->visible(fn() => $user->hasRole('principal_experimenter')),
                EditAction::make()
                    ->label(__('filament.resources.users.actions.details'))
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(false)
                    ->modalHeading('Supprimer l\'utilisateur')
                    ->modalDescription('Cette action est irréversible. Veuillez expliquer la raison de la suppression.')
                    ->form([
                        Textarea::make('deletion_reason')
                            ->label('Raison de la suppression')
                            ->required()
                            ->maxLength(500)
                    ])
                    ->before(function ($record, array $data) {
                        // Envoyer une notification à l'utilisateur
                        $record->notify(new UserDeletionNotification($data['deletion_reason']));

                        // Créer une notification dans Filament
                        Notification::make()
                            ->title('Utilisateur supprimé')
                            ->success()
                            ->body("L'utilisateur a été notifié de la suppression de son compte.")
                            ->send();
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relation managers
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->hasAnyRole(['supervisor', 'principal_experimenter'])) {
            return false;
        }

        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifie si l'utilisateur ou son principal est banni
        if ($user->status === 'banned') {
            return false;
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        return true;
    }

    // Ajoutons aussi une vérification similaire pour bloquer l'accès complet
    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->status === 'banned') {
            abort(403, 'Votre compte est banni.');
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                abort(403, 'Le compte de votre expérimentateur principal est banni.');
            }
        }

        parent::authorizeAccess();
    }

    public static function authorizeViewAny(): void
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->hasAnyRole(['supervisor', 'principal_experimenter'])) {
            abort(403, 'Unauthorized action.');
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'approved')
            ->where(function (Builder $query) {
                /** @var \App\Models\User */
                $user = Auth::user();

                if ($user->hasRole('principal_experimenter')) {
                    $query->where('created_by', $user->id);
                } elseif ($user->hasRole('supervisor')) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'principal_experimenter');
                    })
                        ->where(function ($q) {
                            $q->where('created_by', Auth::id())
                                ->orWhereNull('created_by');
                        });
                }
            });
    }
}
