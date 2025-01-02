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
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): string
    {
        return __('navigation.group.users');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.pending_users');
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
                TextInput::make('name')
                    ->label(__('filament.resources.pending_registration.form.name'))
                    ->disabled(),
                TextInput::make('email')
                    ->label(__('filament.resources.pending_registration.form.email'))
                    ->disabled(),
                TextInput::make('university')
                    ->label(__('filament.resources.pending_registration.form.university'))
                    ->disabled(),
                Textarea::make('registration_reason')
                    ->label(__('filament.resources.pending_registration.form.registration_reason'))
                    ->disabled()
                    ->columnSpan('full'),
                Select::make('status')
                    ->options([
                        'approved' => __('filament.resources.pending_registration.form.status.approved'),
                        'rejected' => __('filament.resources.pending_registration.form.status.rejected'),
                    ])
                    ->native(false)
                    ->required()
                    ->live(),
                Textarea::make('rejection_reason')
                    ->required(fn(Get $get) => $get('status') === 'rejected')
                    ->visible(fn(Get $get) => $get('status') === 'rejected')
                    ->label(__('filament.resources.pending_registration.form.rejected_reason.label'))
                    ->placeholder(__('filament.resources.pending_registration.form.rejected_reason.placeholder'))
                    ->helperText(__('filament.resources.pending_registration.form.rejected_reason.helper'))
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.pending_registration.table.name')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.resources.pending_registration.table.email')),
                Tables\Columns\TextColumn::make('university')
                    ->label(__('filament.resources.pending_registration.table.university')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de demande')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.pending_registration.table.status.label'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('filament.resources.pending_registration.table.status.pending'),
                        default => $state
                    })
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

    public static function canAccess(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if (!$user->hasRole('supervisor')) {
            abort(403, "Vous n'avez pas accès à cette section.");
        }

        return true;
    }

    // Et aussi pour bien s'assurer que même l'accès à la liste est bloqué
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if (!$user->hasRole('supervisor')) {
            abort(403, "Vous n'avez pas accès à cette section.");
        }

        return true;
    }
}
