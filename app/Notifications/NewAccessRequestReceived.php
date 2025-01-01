<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccessRequestReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExperimentAccessRequest $request)
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
        $requester = $this->request->user;
        $requestType = match ($this->request->type) {
            'results' => 'aux résultats',
            'access' => 'de collaboration complète',
            'duplicate' => 'de duplication',
            default => 'd\'accès'
        };

        $message = (new MailMessage)
            ->subject("Nouvelle demande $requestType")
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Vous avez reçu une nouvelle demande $requestType pour votre expérimentation.")
            ->line('Détails de la demande :')
            ->line('Expérimentation : ' . $this->request->experiment->name)
            ->line('Demandeur : ' . $requester->name)
            ->line('Message : ' . $this->request->request_message);

        if ($this->request->type === 'access') {
            $message->line('Cette collaboration donnera accès :')
                ->line('- Aux résultats et statistiques')
                ->line('- À la possibilité de faire passer des sessions')
                ->line('- Au partage des résultats avec les autres collaborateurs');
        } elseif ($this->request->type === 'duplicate') {
            $message->line('En acceptant la duplication :')
                ->line('- Une copie de votre expérimentation sera créée')
                ->line('- Le demandeur en sera le nouveau créateur')
                ->line('- Vous serez référencé comme créateur original')
                ->line('- Les médias seront dupliqués avec l\'expérimentation');
        }

        return $message->action('Gérer la demande', route('filament.admin.resources.experiment-access-requests.edit', $this->request));
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
