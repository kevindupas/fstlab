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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        $mail = (new MailMessage)
            ->subject(__('notifications.supervisor_message.subject'))
            ->greeting(__('notifications.supervisor_message.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.supervisor_message.line1', ['supervisor' => $this->supervisor->name]));

        if ($this->experiment) {
            $mail->line(__('notifications.supervisor_message.line2', ['experiment' => $this->experiment->name]));
        }

        return $mail
            ->line($this->message)
            ->line(__('notifications.supervisor_message.line3'));
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
