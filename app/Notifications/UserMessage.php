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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        // Déterminer le type d'expéditeur pour le sujet et le message
        $senderType = $this->sender->hasRole('supervisor')
            ? __('notifications.user_message.supervisor')
            : __('notifications.user_message.researcher');

        $mail = (new MailMessage)
            ->subject(__('notifications.user_message.subject', ['senderType' => $senderType]))
            ->greeting(__('notifications.user_message.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.user_message.line1', ['senderType' => $senderType, 'senderName' => $this->sender->name]));

        if ($this->experiment) {
            $mail->line(__('notifications.user_message.experiment', ['experimentName' => $this->experiment->name]));
        }

        return $mail
            ->line($this->message)
            ->line(
                $this->sender->hasRole('supervisor')
                    ? __('notifications.user_message.response_supervisor')
                    : __('notifications.user_message.response_researcher')
            );
    }
}
