<?php

namespace App\Notifications;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestProcessed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ExperimentAccessRequest $request,
        public bool $isApproved
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
        $mail = (new MailMessage)
            ->subject('Réponse à votre demande d\'accès aux résultats')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Votre demande d\'accès aux résultats pour l\'expérimentation "' . $this->request->experiment->name . '" a été traitée.');

        if ($this->isApproved) {
            return $mail
                ->line('Votre demande a été approuvée.')
                ->line('Vous pouvez maintenant accéder aux résultats de l\'expérimentation.')
                ->action(
                    'Voir les résultats',
                    ExperimentSessions::getUrl(['record' => $this->request->experiment_id])
                );
        }

        return $mail
            ->error()
            ->line('Votre demande a été refusée.')
            ->when(
                $this->request->response_message,
                fn($mail) => $mail->line('Motif : ' . $this->request->response_message)
            );
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
