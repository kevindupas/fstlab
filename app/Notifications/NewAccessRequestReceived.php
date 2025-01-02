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
        app()->setLocale($notifiable->locale ?? config('app.locale'));

        $requester = $this->request->user;
        $requestType = __("notifications.new_access_request_received.type.{$this->request->type}");

        $message = (new MailMessage)
            ->subject(__('notifications.new_access_request_received.subject', ['type' => $requestType]))
            ->greeting(__('notifications.new_access_request_received.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.new_access_request_received.line1', ['type' => $requestType]))
            ->line(__('notifications.new_access_request_received.details'))
            ->line(__('notifications.new_access_request_received.experiment', ['name' => $this->request->experiment->name]))
            ->line(__('notifications.new_access_request_received.requester', ['name' => $requester->name]))
            ->line(__('notifications.new_access_request_received.message', ['message' => $this->request->request_message]));

        if ($this->request->type === 'access') {
            $message->line(__('notifications.new_access_request_received.access_details'));
        } elseif ($this->request->type === 'duplicate') {
            $message->line(__('notifications.new_access_request_received.duplicate_details'));
        }

        return $message->action(
            __('notifications.new_access_request_received.action'),
            route('filament.admin.resources.experiment-access-requests.edit', $this->request)
        );
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
