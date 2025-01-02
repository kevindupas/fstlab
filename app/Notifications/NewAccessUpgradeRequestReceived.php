<?php
// app/Notifications/NewAccessUpgradeRequestReceived.php
namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccessUpgradeRequestReceived extends Notification
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
            ->subject(__('notifications.new_access_upgrade_request_received.subject', ['experiment' => $this->accessRequest->experiment->name]))
            ->greeting(__('notifications.new_access_upgrade_request_received.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.new_access_upgrade_request_received.line1', ['user' => $this->accessRequest->user->name, 'experiment' => $this->accessRequest->experiment->name]))
            ->line(__('notifications.new_access_upgrade_request_received.line2'))
            ->line(__('notifications.new_access_upgrade_request_received.request_message', ['message' => $this->accessRequest->request_message]))
            ->action(__('notifications.new_access_upgrade_request_received.action'), url('/filament/resources/experiment-access-requests/' . $this->accessRequest->id . '/edit'));
    }
}
