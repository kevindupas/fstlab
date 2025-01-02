<?php
// app/Notifications/AccessUpgradedNotification.php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessUpgradedNotification extends Notification
{
    use Queueable;

    private $accessRequest;

    public function __construct(ExperimentAccessRequest $accessRequest)
    {
        $this->accessRequest = $accessRequest;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        return (new MailMessage)
            ->subject(__('notifications.access_upgraded_notification.subject', ['experiment' => $this->accessRequest->experiment->name]))
            ->greeting(__('notifications.access_upgraded_notification.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.access_upgraded_notification.line1', ['experiment' => $this->accessRequest->experiment->name]))
            ->line(__('notifications.access_upgraded_notification.line2'))
            ->line(__('notifications.access_upgraded_notification.line3'))
            ->line(__('notifications.access_upgraded_notification.line4'))
            ->action(__('notifications.access_upgraded_notification.action'), url('/experiments/' . $this->accessRequest->experiment_id))
            ->line(__('notifications.access_upgraded_notification.thank_you'));
    }
}
