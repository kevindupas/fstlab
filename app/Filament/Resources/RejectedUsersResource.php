<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RejectedUsersResource\Pages;
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

class RejectedUsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Users';
    protected static ?string $navigationIcon = 'heroicon-o-user-minus';
    protected static ?string $navigationLabel = 'Utilisateurs rejetés';

    public static function getPluralModelLabel(): string
    {
        return __('navigation.rejected_user');
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
                TextInput::make('name')
                    ->label(__('filament.resources.rejected_user.form.name'))
                    ->disabled(),
                TextInput::make('email')
                    ->label(__('filament.resources.rejected_user.form.email'))
                    ->disabled(),
                TextInput::make('university')
                    ->label(__('filament.resources.rejected_user.form.university'))
                    ->disabled(),
                Textarea::make('registration_reason')
                    ->disabled()
                    ->label(__('filament.resources.rejected_user.form.registration_reason'))
                    ->columnSpan('full'),
                Select::make('status')
                    ->options([
                        'approved' => __('filament.resources.rejected_user.form.status.approved'),
                        'rejected' => __('filament.resources.rejected_user.form.status.rejected'),
                    ])
                    ->required(),
                Textarea::make('rejection_reason')
                    ->required(fn(Get $get) => $get('status') === 'rejected')
                    ->visible(fn(Get $get) => $get('status') === 'rejected')
                    ->label(__('filament.resources.rejected_user.form.rejected_reason'))->disabled()->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.rejected_user.table.name')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.resources.rejected_user.table.email')),
                Tables\Columns\TextColumn::make('university')
                    ->label(__('filament.resources.rejected_user.table.university')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.rejected_user.table.created_at'))
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.rejected_user.table.status.label'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'rejected' => __('filament.resources.rejected_user.table.status.rejected'),
                        default => $state
                    })
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
