<?php
// app/Notifications/AccessUpgradeRequestSubmitted.php
namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessUpgradeRequestSubmitted extends Notification
{
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
            ->subject(__('notifications.access_upgrade_request_submitted.subject', ['experiment' => $this->accessRequest->experiment->name]))
            ->greeting(__('notifications.access_upgrade_request_submitted.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.access_upgrade_request_submitted.line1', ['experiment' => $this->accessRequest->experiment->name]))
            ->line(__('notifications.access_upgrade_request_submitted.line2'))
            ->line(__('notifications.access_upgrade_request_submitted.line3'))
            ->action(__('notifications.access_upgrade_request_submitted.action'), url('/experiments/' . $this->accessRequest->experiment_id));
    }
}
