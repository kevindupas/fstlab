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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        $requestType = __("notifications.access_request_submitted.type.{$this->request->type}");
        $experimentName = $this->request->experiment->name;

        $message = (new MailMessage)
            ->subject(__('notifications.access_request_submitted.subject', ['type' => $requestType]))
            ->greeting(__('notifications.access_request_submitted.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.access_request_submitted.line1', ['type' => $requestType]))
            ->line(__('notifications.access_request_submitted.details'))
            ->line(__('notifications.access_request_submitted.experiment', ['name' => $experimentName]))
            ->line(__('notifications.access_request_submitted.message', ['message' => $this->request->request_message]));

        if ($this->request->type === 'access') {
            $message->line(__('notifications.access_request_submitted.access_details'));
        } elseif ($this->request->type === 'duplicate') {
            $message->line(__('notifications.access_request_submitted.duplicate_details'));
        }

        return $message->line(__('notifications.access_request_submitted.pending'));
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
