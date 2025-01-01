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
        $requestType = match ($this->request->type) {
            'results' => 'aux résultats',
            'access' => 'de collaboration',
            'duplicate' => 'de duplication',
            default => 'd\'accès'
        };

        $mail = (new MailMessage)
            ->subject("Réponse à votre demande $requestType")
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Votre demande $requestType pour l'expérimentation \"" . $this->request->experiment->name . "\" a été traitée.");

        if ($this->isApproved) {
            $mail->line('Votre demande a été approuvée.');

            if ($this->request->type === 'access') {
                $mail->line('Vous pouvez maintenant :')
                    ->line('- Accéder aux résultats et statistiques')
                    ->line('- Faire passer des sessions')
                    ->line('- Partager vos résultats avec les autres collaborateurs');
            } elseif ($this->request->type === 'duplicate') {
                $mail->line('La duplication a été effectuée avec succès.')
                    ->line('Vous trouverez la copie de l\'expérimentation dans votre liste d\'expérimentations.')
                    ->line('Vous pouvez maintenant :')
                    ->line('- Modifier la copie selon vos besoins')
                    ->line('- Commencer à collecter vos propres données')
                    ->line('- Gérer vos propres collaborateurs');
            } else {
                $mail->line('Vous pouvez maintenant accéder aux résultats de l\'expérimentation.');
            }

            return $mail->action(
                'Accéder à l\'expérimentation',
                route('filament.admin.resources.experiment-sessions.index', ['record' => $this->request->experiment_id])
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
