<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationRequest extends Notification
{
    use Queueable;

    private $newUser;
    /**
     * Create a new notification instance.
     */

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        return (new MailMessage)
            ->subject(__('notifications.new_registration_request.subject'))
            ->greeting(__('notifications.new_registration_request.greeting') . ' ' . $notifiable->name)
            ->line(__('notifications.new_registration_request.line1'))
            ->line(__('notifications.new_registration_request.line2'))
            ->line(__('notifications.new_registration_request.line3') . ' ' . $this->newUser->name)
            ->line(__('notifications.new_registration_request.line4') . ' ' . $this->newUser->university)
            ->line(__('notifications.new_registration_request.line5') . ' ' . $this->newUser->email)
            ->line(__('notifications.new_registration_request.line6') . ' ' . $this->newUser->registration_reason)
            ->action(__('notifications.new_registration_request.action'), route('filament.admin.resources.pending-registrations.edit', $this->newUser));
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
