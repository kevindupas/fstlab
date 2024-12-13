<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestSubmitted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExperimentAccessRequest $request) {}

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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande d\'accès aux résultats envoyée')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Votre demande d\'accès aux résultats a bien été enregistrée.')
            ->line('Détails de la demande :')
            ->line('Expérimentation : ' . $this->request->experiment->name)
            ->line('Message : ' . $this->request->request_message)
            ->line('Nous vous informerons dès que votre demande aura été traitée.');
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
