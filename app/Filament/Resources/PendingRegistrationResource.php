<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingRegistrationResource\Pages;
use App\Models\User;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationRejected;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PendingRegistrationResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Demandes d\'inscription';

    public static function getModelLabel(): string
    {
        return __('Demande d\'inscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Demandes d\'inscription');
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user?->hasRole('supervisor') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'pending')
            ->whereHas(
                'roles',
                fn($q) =>
                $q->where('name', 'principal_experimenter')
            );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('university')->disabled(),
                Textarea::make('registration_reason')
                    ->disabled()
                    ->label('Motif d\'inscription')->columnSpan('full'),
                Select::make('status')
                    ->options([
                        'approved' => 'Approuver',
                        'rejected' => 'Rejeter',
                    ])
                    ->required()
                    ->live(),
                Textarea::make('rejection_reason')
                    ->required(fn(Get $get) => $get('status') === 'rejected')
                    ->visible(fn(Get $get) => $get('status') === 'rejected')
                    ->label('Motif du refus')->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('university')
                    ->label('Université'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de demande')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.my_experiment.table.columns.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('Examiner')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->url(fn(User $record): string =>
                    static::getUrl('edit', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendingRegistrations::route('/'),
            'edit' => Pages\EditPendingRegistration::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')
            ->whereHas('roles', fn($q) => $q->where('name', 'principal_experimenter'))
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

}
