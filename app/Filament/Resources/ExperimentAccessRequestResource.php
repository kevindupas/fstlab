<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperimentAccessRequestResource\Pages;
use App\Models\ExperimentAccessRequest;
use App\Models\User;
use App\Notifications\AccessRequestProcessed;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExperimentAccessRequestResource extends Resource
{
    protected static ?string $model = ExperimentAccessRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    // protected static ?string $navigationLabel = 'Demandes d\'accès';
    protected static ?string $navigationGroup = 'Experiments';

    public static function getModelLabel(): string
    {
        return __('filament.resources.experiment-access-request.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.experiment-access-request.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.experiment-access-request.navigation_label');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label(__('filament.resources.experiment-access-request.form.status.label'))
                    ->options(__('filament.resources.experiment-access-request.form.status.options'))
                    ->required()
                    ->live()
                    ->disabled(fn($record) => $record->status !== 'pending')
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state === 'rejected') {
                            $set('response_message', '');
                        }
                    }),
                Forms\Components\Textarea::make('response_message')
                    ->label(__('filament.resources.experiment-access-request.form.response_message.label'))
                    ->required(fn($get) => $get('status') === 'rejected')
                    ->visible(fn($get) => $get('status') === 'rejected')
                    ->disabled(fn($record) => $record->status !== 'pending')
                    ->helperText(__('filament.resources.experiment-access-request.form.response_message.helper_text')),
                Forms\Components\Textarea::make('request_message')
                    ->label(__('filament.resources.experiment-access-request.form.request_message.label'))
                    ->disabled()
                    ->columnSpanFull(),
                Placeholder::make('experiment')
                    ->label(__('filament.resources.experiment-access-request.form.experiment.label'))
                    ->content(fn($record) => $record->experiment?->name)
                    ->columnSpanFull(),

                Placeholder::make('user')
                    ->label(__('filament.resources.experiment-access-request.form.user.label'))
                    ->content(fn($record) => $record->user?->name)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('experiment.name')
                    ->label(__('filament.resources.experiment-access-request.table.columns.experiment'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('filament.resources.experiment-access-request.table.columns.user'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('filament.resources.experiment-access-request.table.columns.type'))
                    ->formatStateUsing(fn(string $state): string => __("filament.resources.experiment-access-request.table.columns.type_options.$state")),
                TextColumn::make('status')
                    ->label(__('filament.resources.experiment-access-request.table.columns.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => __("filament.resources.experiment-access-request.form.status.options.$state"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.experiment-access-request.table.columns.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Limiter l'accès aux demandes
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // L'utilisateur ne peut voir que les demandes des expériences qu'il a créées
        return $query->whereHas('experiment', function ($query) {
            $query->where('created_by', Auth::id());
        });
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('experiment', function ($query) {
            $query->where('created_by', Auth::id());
        })
            ->where('status', 'pending')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifie si l'utilisateur est banni
        if ($user->status === 'banned') {
            return false;
        }

        // Vérifie si le principal experimenter est banni
        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        // Vérifie si l'utilisateur a des expériences
        return $user->createdExperiments()->exists();
    }

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

        // Vérifie si l'utilisateur a des expériences
        if (!$user->createdExperiments()->exists()) {
            abort(403, 'Vous n\'avez pas encore créé d\'expérimentation.');
        }

        parent::authorizeAccess();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperimentAccessRequests::route('/'),
            'edit' => Pages\EditExperimentAccessRequest::route('/{record}/edit'),
        ];
    }
}
