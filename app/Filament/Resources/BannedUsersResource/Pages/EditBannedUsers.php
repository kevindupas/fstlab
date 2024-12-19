<?php

namespace App\Filament\Resources\BannedUsersResource\Pages;

use App\Filament\Resources\BannedUsersResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Notifications\UserUnbanned;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditBannedUsers extends EditRecord
{
    protected static string $resource = BannedUsersResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('filament.resources.banned.title') . " : " . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label(__('filament.resources.banned.action.delete')),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        if ($user->wasChanged('status') && $user->status === 'approved' && $user->unbanned_reason) {
            // Débannir l'utilisateur principal
            $user->notify(new UserUnbanned($user->unbanned_reason));

            // Débannir tous les utilisateurs secondaires
            $secondaryUsers = User::where('created_by', $user->id)
                ->where('status', 'banned')
                ->get();

            foreach ($secondaryUsers as $secondaryUser) {
                // Force la mise à jour directe dans la base de données
                User::where('id', $secondaryUser->id)->update([
                    'status' => 'approved',
                    'unbanned_reason' => __('filament.resources.banned.notification.unbanned_reason') . ' ' . $user->unbanned_reason,
                    'banned_reason' => null
                ]);

                // Rafraîchir l'instance après la mise à jour
                $secondaryUser->refresh();

                // Envoyer la notification
                $secondaryUser->notify(new UserUnbanned($secondaryUser->unbanned_reason));
            }

            // Redirection et notification
            $this->redirect(UserResource::getUrl('index'));
            Notification::make()
                ->title(__('filament.resources.banned.notification.unbanned'))
                ->warning()
                ->send();
        }
    }
}
