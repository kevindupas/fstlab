<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\User;
use Filament\Actions;
use App\Notifications\UserBanned;
use Illuminate\Support\Facades\Log;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // protected function getFormSchema(): array
    // {
    //     return [
    //         TextInput::make('name')
    //             ->required()
    //             ->maxLength(255),
    //         TextInput::make('email')
    //             ->required()
    //             ->email()
    //             ->maxLength(255),
    //         TextInput::make('password')
    //             ->password()
    //             ->maxLength(255)
    //             ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null),
    //         Select::make('roles')
    //             ->multiple()
    //             ->relationship('roles', 'name')
    //             ->preload()
    //             ->required(),
    //     ];
    // }

    // protected function authorizeEdit(User $record): void
    // {
    //     Gate::authorize('edit-user', $record);
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        if ($user->wasChanged('status') && $user->status === 'banned' && $user->banned_reason) {
            $user->notify(new UserBanned($user->banned_reason));
            $this->redirect($this->getResource()::getUrl('index'));
            Notification::make()
                ->title(__('Banni avec succès'))
                ->warning()
                ->send();
        }
    }
}
