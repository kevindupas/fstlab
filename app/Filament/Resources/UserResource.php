<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

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
        } else {
            $roleOptions = [];
        }


        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->maxLength(255),
                Select::make('role')
                    ->options($roleOptions)
                    ->required()
                    ->hidden(fn ($livewire) => empty($roleOptions))
                    ->default(fn ($record) => $record ? $record->roles->first()->name : null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
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
                    $query->where('created_by', $user->id);
                } elseif ($user->hasRole('supervisor')) {
                    $query->whereHas('roles', function ($q) {
                        dd($q->where('name', 'principal_experimenter'));
                        $q->where('name', 'principal_experimenter');
                    });
                } else {
                    return $query->where('id', 0);
                }
            })
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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

    public static function authorizeViewAny(): void
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->hasAnyRole(['supervisor', 'principal_experimenter'])) {
            abort(403, 'Unauthorized action.');
        }
    }
}
