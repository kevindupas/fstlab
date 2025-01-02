<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        $url = URL::signedRoute('filament.admin.auth.password-reset.reset', [
            'email' => $notifiable->email,
            'token' => app('auth.password.broker')->createToken($notifiable),
        ]);

        return (new MailMessage)
            ->subject(__('notifications.password_reset.subject'))
            ->line(__('notifications.password_reset.line1'))
            ->line(__('notifications.password_reset.line2'))
            ->action(__('notifications.password_reset.action'), $url)
            ->line(__('notifications.password_reset.line3'));
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
