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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        return (new MailMessage)
            ->subject(__('notifications.user_deleted.subject'))
            ->error()
            ->greeting(__('notifications.user_deleted.greeting') . ' ' . $notifiable->name)
            ->line(__('notifications.user_deleted.line1'))
            ->line($this->reason)
            ->line(__('notifications.user_deleted.line2'));
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
