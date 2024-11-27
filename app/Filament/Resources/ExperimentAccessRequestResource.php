<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperimentAccessRequestResource\Pages;
use App\Models\ExperimentAccessRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExperimentAccessRequestResource extends Resource
{
    protected static ?string $model = ExperimentAccessRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Demandes d\'accès';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                    ])
                    ->required()
                    ->live() // Rend le champ réactif
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state === 'rejected') {
                            $set('response_message', ''); // Reset le message si besoin
                        }
                    }),
                Forms\Components\Textarea::make('response_message')
                    ->label('Message de réponse')
                    ->required(fn($get) => $get('status') === 'rejected')
                    ->visible(fn($get) => $get('status') === 'rejected')
                    ->helperText('Veuillez expliquer la raison du refus'),
                Forms\Components\Textarea::make('request_message')
                    ->label('Message de demande')
                    ->disabled()
                    ->columnSpanFull(),
                // Afficher l'expérience concernée
                Forms\Components\TextInput::make('experiment.name')
                    ->label('Expérience')
                    ->disabled()
                    ->columnSpanFull(),
                // Afficher le demandeur
                Forms\Components\TextInput::make('user.name')
                    ->label('Demandeur')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('experiment.name')
                    ->label('Expérience')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Demandeur')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'access' => 'Accès à l\'expérience',
                        'results' => 'Accès aux résultats',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Date de demande')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc'); // Plus récent en premier
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

    // Masquer la ressource dans la navigation si l'utilisateur n'a pas d'expériences
    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $user->createdExperiments()->exists();
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
