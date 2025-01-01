<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDeletionNotification extends Notification
{
    use Queueable;

    private string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Suppression de votre compte')
            ->line('Votre compte a été supprimé pour la raison suivante :')
            ->line($this->reason)
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, veuillez nous contacter.');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
