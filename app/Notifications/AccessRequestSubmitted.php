<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestSubmitted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExperimentAccessRequest $request) {}

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
    public function toMail(object $notifiable): MailMessage
    {
        $requestType = match ($this->request->type) {
            'results' => 'aux résultats',
            'access' => 'de collaboration complète (résultats + sessions)',
            'duplicate' => 'de duplication d\'expérimentation',
            default => 'd\'accès'
        };

        $message = (new MailMessage)
            ->subject("Demande $requestType envoyée")
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Votre demande $requestType a bien été enregistrée.")
            ->line('Détails de la demande :')
            ->line('Expérimentation : ' . $this->request->experiment->name)
            ->line('Message : ' . $this->request->request_message);

        if ($this->request->type === 'access') {
            $message->line('Cette collaboration vous donnera accès :')
                ->line('- Aux résultats et statistiques')
                ->line('- À la possibilité de faire passer des sessions')
                ->line('- Au partage des résultats avec les autres collaborateurs');
        } elseif ($this->request->type === 'duplicate') {
            $message->line('Si votre demande est acceptée :')
                ->line('- Vous recevrez une copie complète de l\'expérimentation')
                ->line('- Vous en serez le créateur')
                ->line('- Vous pourrez la modifier selon vos besoins')
                ->line('- Le créateur original sera référencé sur votre copie');
        }

        return $message->line('Nous vous informerons dès que votre demande aura été traitée.');
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
