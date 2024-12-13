<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationRequest extends Notification
{
    use Queueable;

    private $newUser;
    /**
     * Create a new notification instance.
     */

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle demande d\'inscription')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Une nouvelle demande d\'inscription a été soumise.')
            ->line('Détails du demandeur :')
            ->line('Nom : ' . $this->newUser->name)
            ->line('Université : ' . $this->newUser->university)
            ->line('Email : ' . $this->newUser->email)
            ->line('Motif : ' . $this->newUser->registration_reason)
            ->action('Gérer la demande', route('filament.admin.resources.pending-registrations.edit', $this->newUser));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
