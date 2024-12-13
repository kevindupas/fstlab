<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{

    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = -1;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Utilisateurs';

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
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                TextInput::make('university')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),
                Select::make('roles')
                    // ->multiple(false)
                    ->relationship('roles', 'name')
                    ->options($roleOptions)
                    ->required()
                    ->default(fn($record) => $record ? $record->roles->first()->name : null)
                    ->visible(fn() => !empty($roleOptions))
                    ->disabled(fn($livewire) => !$livewire instanceof Pages\CreateUser),

                Textarea::make('registration_reason')
                    ->label('Motif d\'inscription')
                    ->visible(
                        fn($record, $livewire) =>
                        $livewire instanceof Pages\EditUser &&
                            filled($record->registration_reason)
                    )
                    ->disabled()
                    ->extraAttributes(['class' => 'bg-blue-50'])
                    ->columnSpan('full'),

                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'approved' => "Approuvé",
                        'banned' => "Bannir",
                    ])
                    ->required()
                    ->live()
                    ->disabled(fn() => !$user->hasRole('supervisor'))
                    ->visible(fn() => $user->hasRole('supervisor')),


                Textarea::make('banned_reason')
                    ->required(fn(Get $get) => $get('status') === 'banned')
                    ->visible(fn(Get $get) => $get('status') === 'banned')
                    ->label('Motif du bannissement')
                    ->live()
                    ->dehydrated(true)
                    ->columnSpan('full'),


                Section::make('Historique des actions')
                    ->description('Historique des différentes actions effectuées sur ce compte')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Textarea::make('registration_reason')
                                    ->label('Motif d\'inscription')
                                    ->visible(fn($record) => filled($record->registration_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-blue-50']),

                                Textarea::make('rejection_reason')
                                    ->label('Motif de rejet')
                                    ->visible(fn($record) => filled($record->rejection_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-red-50']),

                                Textarea::make('banned_reason')
                                    ->label('Motif de bannissement')
                                    ->visible(fn($record) => filled($record->banned_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-red-50']),

                                Textarea::make('unbanned_reason')
                                    ->label('Motif de débannissement')
                                    ->visible(fn($record) => filled($record->unbanned_reason))
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-green-50']),

                            ])
                            ->columnSpan('full'),
                    ])
                    ->collapsible()
                    ->collapsed(true)
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
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('status')
                    ->label(__('filament.resources.my_experiment.table.columns.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'banned' => 'danger',
                    }),
                TextColumn::make('roles')
                    ->label('Roles')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->roles->pluck('name')->join(', ');
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                /** @var \App\Models\User */
                $user = Auth::user();

                if ($user->hasRole('principal_experimenter')) {
                    // Un principal_experimenter ne voit que les utilisateurs qu'il a créés
                    $query->where('created_by', $user->id);
                } elseif ($user->hasRole('supervisor')) {
                    // Un supervisor ne voit que les principal_experimenters qu'il a créés
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'principal_experimenter');
                    })
                        ->where('created_by', Auth::id());  // Ajout de cette condition
                } else {
                    return $query->where('id', 0);
                }
            })
            ->actions([
                EditAction::make(),
                // DeleteAction::make(),
            ]);
        // ->bulkActions([
        //     DeleteBulkAction::make(),
        // ]);
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
                        ->where('created_by', Auth::id());
                }
            });
    }
}
