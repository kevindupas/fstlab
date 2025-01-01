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
        return (new MailMessage)
            ->subject('Access Revoked to Experiment')
            ->line('Your access to the experiment "' . $this->accessRequest->experiment->name . '" has been revoked.')
            ->line('Reason:')
            ->line($this->accessRequest->response_message)
            ->line('If you have any questions, please contact the experiment owner.');
    }
}
