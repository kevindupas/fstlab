<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRevokedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ExperimentAccessRequest $accessRequest
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        return (new MailMessage)
            ->subject(__('notifications.access_revoked_notification.subject'))
            ->line(__('notifications.access_revoked_notification.line1', ['experiment' => $this->accessRequest->experiment->name]))
            ->line(__('notifications.access_revoked_notification.line2'))
            ->line(__('notifications.access_revoked_notification.reason', ['message' => $this->accessRequest->response_message]))
            ->line(__('notifications.access_revoked_notification.contact'));
    }
}
