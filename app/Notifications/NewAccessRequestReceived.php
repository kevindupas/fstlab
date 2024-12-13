<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccessRequestReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExperimentAccessRequest $request)
    {
        //
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
        $requester = $this->request->user;

        return (new MailMessage)
            ->subject('Nouvelle demande d\'accès aux résultats')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Vous avez reçu une nouvelle demande d\'accès aux résultats pour votre expérimentation.')
            ->line('Détails de la demande :')
            ->line('Expérimentation : ' . $this->request->experiment->name)
            ->line('Demandeur : ' . $requester->name)
            ->line('Message : ' . $this->request->request_message)
            ->action('Gérer la demande', route('filament.admin.resources.experiment-access-requests.edit', $this->request));
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
