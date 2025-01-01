<?php

namespace App\Notifications;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMessage extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public ?Experiment $experiment,
        public User $sender
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Déterminer le type d'expéditeur pour le sujet et le message
        $senderType = $this->sender->hasRole('supervisor')
            ? 'superviseur'
            : 'expérimentateur';

        $mail = (new MailMessage)
            ->subject("Message du {$senderType}")
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Le {$senderType} {$this->sender->name} vous a envoyé un message :");

        if ($this->experiment) {
            $mail->line("Concernant l'expérience : {$this->experiment->name}");
        }

        return $mail
            ->line($this->message)
            ->line(
                $this->sender->hasRole('supervisor')
                    ? 'Vous pouvez répondre via la page "Contacter l\'administrateur" de la plateforme.'
                    : 'Vous pouvez répondre via la plateforme.'
            );
    }
}
