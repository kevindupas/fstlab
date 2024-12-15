<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RejectedUsersResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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

class RejectedUsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?string $navigationIcon = 'heroicon-o-user-minus';
    protected static ?string $navigationLabel = 'Utilisateurs rejetés';

    public static function getModelLabel(): string
    {
        return __('Utilisateur rejeté');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Utilisateurs rejetés');
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
            ->where('status', 'rejected');
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
                    ->label('Motif d\'inscription')
                    ->columnSpan('full'),
                Select::make('status')
                    ->options([
                        'approved' => 'Approuver',
                        'rejected' => 'Rejeter',
                    ])
                    ->required(),
                Textarea::make('rejection_reason')
                    ->required(fn(Get $get) => $get('status') === 'rejected')
                    ->visible(fn(Get $get) => $get('status') === 'rejected')
                    ->label('Motif du refus')->disabled()->columnSpan('full'),
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
                        'rejected' => 'danger',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejectedUsers::route('/'),
            'edit' => Pages\EditRejectedUsers::route('/{record}/edit'),
        ];
    }
}
