<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\User;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->required()
                ->email()
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->maxLength(255)
                ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null),
            Select::make('roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->required(),
        ];
    }

    protected function authorizeEdit(User $record): void
    {
        Gate::authorize('edit-user', $record);
    }
}
