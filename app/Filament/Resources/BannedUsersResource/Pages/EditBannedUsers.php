<?php

namespace App\Filament\Resources\BannedUsersResource\Pages;

use App\Filament\Resources\BannedUsersResource;
use App\Filament\Resources\UserResource;
use App\Notifications\UserUnbanned;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditBannedUsers extends EditRecord
{
    protected static string $resource = BannedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;


        if ($user->wasChanged('status') && $user->status === 'approved' && $user->unbanned_reason) {
            $user->notify(new UserUnbanned($user->unbanned_reason));
            $this->redirect(UserResource::getUrl('index'));
            Notification::make()
                ->title(__('Utilisateur débanni avec succès'))
                ->warning()
                ->send();
        }
    }
}
