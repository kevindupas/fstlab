<?php

namespace App\Notifications;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorMessage extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public ?Experiment $experiment,
        public User $supervisor
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Message du superviseur')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Le superviseur {$this->supervisor->name} vous a envoyé un message :");

        if ($this->experiment) {
            $mail->line("Concernant l'expérience : {$this->experiment->name}");
        }

        return $mail
            ->line($this->message)
            ->line('Vous pouvez répondre via la page "Contacter l\'administrateur" de la plateforme.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'experiment_id' => $this->experiment?->id,
            'supervisor_id' => $this->supervisor->id,
        ];
    }
}
