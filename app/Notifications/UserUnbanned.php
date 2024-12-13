<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUnbanned extends Notification
{
    use Queueable;
    private $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
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
            ->subject('Votre compte a été débanni')
            ->error()
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Votre compte a été débanni de la plateforme.')
            ->line('Motif du débannissement : ' . $this->reason)
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, veuillez contacter l\'administrateur.');
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
