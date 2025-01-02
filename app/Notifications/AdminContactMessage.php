<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminContactMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $sender,
        private string $subject,
        private string $message
    ) {}

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

        $emailBuilder = (new MailMessage)
            ->subject(__('notifications.admin_contact_message.subject', ['subject' => $this->subject]))
            ->line(__('notifications.admin_contact_message.line1', ['sender_name' => $this->sender->name, 'sender_email' => $this->sender->email]))
            ->line(__('notifications.admin_contact_message.line2', ['subject' => $this->subject]))
            ->line(__('notifications.admin_contact_message.line3'))
            ->line($this->message);

        // Si c'est une demande de débannissement, ajouter le lien vers le profil
        if ($this->subject === 'unban' && $this->sender->status === 'banned') {
            $emailBuilder->action(
                __('notifications.admin_contact_message.action'),
                route('filament.admin.resources.banned-users.edit', ['record' => $this->sender])
            );
        }

        return $emailBuilder;
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
