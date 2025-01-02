<?php

namespace App\Notifications;

use App\Models\ExperimentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestProcessed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ExperimentAccessRequest $request,
        public bool $isApproved
    ) {}

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

        $requestType = __("notifications.access_request_processed.type.{$this->request->type}");
        $experimentName = $this->request->experiment->name;

        $mail = (new MailMessage)
            ->subject(__('notifications.access_request_processed.subject', ['type' => $requestType]))
            ->greeting(__('notifications.access_request_processed.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.access_request_processed.line1', ['type' => $requestType, 'experiment' => $experimentName]));

        if ($this->isApproved) {
            $mail->line(__('notifications.access_request_processed.approved'));

            if ($this->request->type === 'access') {
                $mail->line(__('notifications.access_request_processed.access_details'));
            } elseif ($this->request->type === 'duplicate') {
                $mail->line(__('notifications.access_request_processed.duplicate_details'));
            } else {
                $mail->line(__('notifications.access_request_processed.results_details'));
            }

            return $mail->action(
                __('notifications.access_request_processed.action'),
                route('filament.admin.resources.experiment-sessions.index', ['record' => $this->request->experiment_id])
            );
        }

        return $mail
            ->error()
            ->line(__('notifications.access_request_processed.rejected'))
            ->when(
                $this->request->response_message,
                fn($mail) => $mail->line(__('notifications.access_request_processed.reason', ['message' => $this->request->response_message]))
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
