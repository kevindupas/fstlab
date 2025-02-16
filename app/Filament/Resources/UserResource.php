<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\AdminContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('supervisor');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('university')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        'banned' => 'Banni'
                    ])
                    ->required(),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name', function (Builder $query) {
                        // Exclure le rôle supervisor de la sélection
                        return $query->where('name', '!=', 'supervisor');
                    })
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('university')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected', 'banned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Exclure les utilisateurs avec le rôle supervisor
                return $query->whereDoesntHave('roles', function (Builder $query) {
                    $query->where('name', 'supervisor');
                });
            })
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        'banned' => 'Banni'
                    ]),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name', function (Builder $query) {
                        // Exclure le rôle supervisor du filtre
                        return $query->where('name', '!=', 'supervisor');
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('Envoyer un message')
                    ->icon('heroicon-o-envelope')
                    ->form([
                        Forms\Components\Select::make('Sujet')
                            ->options([
                                'info' => 'Information',
                                'warning' => 'Avertissement',
                                'other' => 'Autre'
                            ])
                            ->required(),
                        Forms\Components\MarkdownEditor::make('message')
                            ->required()
                            ->columnSpanFull() // Pour que le textarea prenne toute la largeur
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'orderedList',
                                'italic',
                                'link',
                                'undo',
                                'redo',
                            ])
                    ])
                    ->modalWidth('3xl') // Pour avoir une modale plus large
                    ->action(function (User $record, array $data) {
                        $record->notify(new AdminContactMessage(
                            auth()->user(),
                            $data['subject'],
                            $data['message']
                        ));
                        Notification::make()
                            ->title(__('pages.admin_contact.form.success'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
