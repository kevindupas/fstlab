<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Notifications\ResetPasswordNotification;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Génère un mot de passe aléatoire temporaire
        $data['password'] = bcrypt(Str::random(32));
        $data['created_by'] = Auth::id();
        $data['status'] = 'approved';

        /** @var \App\Models\User */
        $user = Auth::user();
        $user = static::getModel()::create($data);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        // Envoie le lien de réinitialisation du mot de passe
        $user->notify(new ResetPasswordNotification());

        return $user;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $data['created_by'] = Auth::id();
        if ($user->hasRole('principal_experimenter')) {
            $data['status'] = 'approved';
        }
        return $data;
    }
}
