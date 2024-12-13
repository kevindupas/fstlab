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
        $url = URL::signedRoute('filament.admin.auth.password-reset.reset', [
            'email' => $notifiable->email,
            'token' => app('auth.password.broker')->createToken($notifiable),
        ]);

        return (new MailMessage)
            ->subject('Définir votre mot de passe')
            ->line('Votre compte a été créé avec succès.')
            ->line('Cliquez sur le bouton ci-dessous pour définir votre mot de passe.')
            ->action('Définir mon mot de passe', $url)
            ->line('Si vous n\'avez pas demandé la création de ce compte, aucune action n\'est requise.');
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
