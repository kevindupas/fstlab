<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Experiment;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Filament\Actions;
use App\Notifications\UserBanned;
use App\Notifications\UserDeletionNotification;
use Filament\Forms\Components\Textarea;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('filament.resources.users.title') . " : " . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(false)
                ->label(__('filament.resources.users.actions.delete'))
                ->modalHeading('Supprimer l\'utilisateur')
                ->modalDescription('Cette action est irréversible. Veuillez expliquer la raison de la suppression.')
                ->form([
                    Textarea::make('deletion_reason')
                        ->label('Raison de la suppression')
                        ->required()
                        ->maxLength(500)
                ])
                ->before(function ($record, array $data) {
                    // Envoyer une notification à l'utilisateur
                    $record->notify(new UserDeletionNotification($data['deletion_reason']));

                    // Créer une notification dans Filament
                    Notification::make()
                        ->title('Utilisateur supprimé')
                        ->success()
                        ->body("L'utilisateur a été notifié de la suppression de son compte.")
                        ->send();
                })
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        if ($user->wasChanged('status') && $user->status === 'banned' && $user->banned_reason) {
            // Bannir l'utilisateur principal
            $user->notify(new UserBanned($user->banned_reason));

            // Bannir tous les utilisateurs secondaires
            $secondaryUsers = User::where('created_by', $user->id)->get();
            foreach ($secondaryUsers as $secondaryUser) {
                // Force la mise à jour directe dans la base de données
                User::where('id', $secondaryUser->id)->update([
                    'status' => 'banned',
                    'banned_reason' => __('filament.resources.users.notification.banned_reason') . ' ' . $user->banned_reason,

                ]);

                // Rafraîchir l'instance après la mise à jour
                $secondaryUser->refresh();

                // Envoyer la notification
                $secondaryUser->notify(new UserBanned($secondaryUser->banned_reason));
            }

            // Arrêter toutes les expérimentations
            $experiments = Experiment::where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->get();

            foreach ($experiments as $experiment) {
                // Force la mise à jour directe dans la base de données
                Experiment::where('id', $experiment->id)->update([
                    'status' => 'stop',
                    'link' => null
                ]);
            }
            // Redirection et notification
            $this->redirect($this->getResource()::getUrl('index'));
            Notification::make()
                ->title(__('filament.resources.users.notification.banned'))
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
