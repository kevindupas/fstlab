<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannedUsersResource\Pages;
use App\Models\User;
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

class BannedUsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';


    public static function getPluralModelLabel(): string
    {
        return __('navigation.banned_user');
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
            ->where('status', 'banned')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'principal_experimenter');
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__("filament.resources.banned.form.name"))
                    ->disabled(),
                TextInput::make('email')
                    ->label(__("filament.resources.banned.form.email"))
                    ->disabled(),
                TextInput::make('university')
                    ->label(__("filament.resources.banned.form.university"))
                    ->disabled(),
                Textarea::make('registration_reason')
                    ->disabled()
                    ->label(__("filament.resources.banned.form.registration_reason"))
                    ->columnSpan('full'),
                Textarea::make('banned_reason')
                    ->disabled()
                    ->label(__("filament.resources.banned.form.banned_reason"))
                    ->columnSpan('full'),
                Select::make('status')
                    ->live()
                    ->options([
                        'approved' => __('filament.resources.banned.form.status.unban'),
                    ])
                    ->required(),
                Textarea::make('unbanned_reason')
                    ->required(fn(Get $get) => $get('status') === 'approved')
                    ->visible(fn(Get $get) => $get('status') === 'approved')
                    ->live()
                    ->dehydrated(true)
                    ->label(__("filament.resources.banned.form.unbanned_reason.label"))
                    ->placeholder(__("filament.resources.banned.form.unbanned_reason.placeholder"))
                    ->helperText(__("filament.resources.banned.form.unbanned_reason.helper"))
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.banned.table.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.resources.banned.table.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('university')
                    ->label(__('filament.resources.banned.table.university')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.banned.table.created_at'))
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.banned.table.status.label'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'banned' => __('filament.resources.banned.table.status.banned'),
                        default => $state
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'banned' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannedUsers::route('/'),
            'edit' => Pages\EditBannedUsers::route('/{record}/edit'),
        ];
    }
}
