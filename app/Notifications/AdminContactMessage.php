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

    public function __construct(
        private User $sender,
        private string $subject,
        private string $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Vérification et définition de la langue
        $locale = $notifiable->locale ?? 'en';
        app()->setLocale($locale);

        // Traduction du sujet selon le type de message
        $translatedSubject = match($this->subject) {
            'info' => $locale === 'fr' ? 'Information' : 'Information',
            'warning' => $locale === 'fr' ? 'Avertissement' : 'Warning',
            'other' => $locale === 'fr' ? 'Autre' : 'Other',
            default => $this->subject
        };

        // Si la langue est en français
        if ($locale === 'fr') {
            return (new MailMessage)
                ->greeting(' ')
                ->subject('Message administratif : ' . $translatedSubject)
                ->line('Message de : ' . $this->sender->name . ' (' . $this->sender->email . ')')
                ->line('Sujet : ' . $translatedSubject)
                ->line('Message :')
                ->line($this->message)
                ->salutation(config('app.name'));
        }

        // Si la langue est en anglais
        return (new MailMessage)
            ->greeting(' ')
            ->subject('Administrative message: ' . $translatedSubject)
            ->line('Message from: ' . $this->sender->name . ' (' . $this->sender->email . ')')
            ->line('Subject: ' . $translatedSubject)
            ->line('Message:')
            ->line($this->message)
            ->salutation(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
