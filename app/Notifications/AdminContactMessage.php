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
        $emailBuilder = (new MailMessage)
            ->subject("Nouveau message : {$this->subject}")
            ->line("Message de : {$this->sender->name} ({$this->sender->email})")
            ->line("Sujet : {$this->subject}")
            ->line("Message :")
            ->line($this->message);

        // Si c'est une demande de débannissement, ajouter le lien vers le profil
        if ($this->subject === 'unban' && $this->sender->status === 'banned') {
            $emailBuilder->action(
                'Gérer l\'utilisateur',
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
