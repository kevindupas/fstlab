<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddedToExperimentNotification extends Notification
{
    use Queueable;

    public function __construct(private $experimentName)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ajout à une expérimentation')
            ->line('Vous avez été ajouté à l\'expérimentation : ' . $this->experimentName)
            ->line('Vous pouvez vous connecter pour y accéder.');
    }
}
