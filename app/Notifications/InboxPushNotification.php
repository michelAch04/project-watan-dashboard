<?php

namespace App\Notifications;

use App\Models\InboxNotification as InboxNotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Support\Facades\Log;

class InboxPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inboxNotification;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Determine if the job should be retried when it fails.
     * Return false to prevent retries on failure.
     *
     * @var bool
     */
    public $retryAfter = false;

    /**
     * Create a new notification instance.
     */
    public function __construct(InboxNotificationModel $inboxNotification)
    {
        $this->inboxNotification = $inboxNotification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Verify the user has push subscriptions before attempting to send
        if (!$notifiable->pushSubscriptions()->exists()) {
            Log::info('User has no push subscriptions', [
                'user_id' => $notifiable->id,
                'notification_id' => $this->inboxNotification->id
            ]);
            return [];
        }

        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable)
    {
        return (new WebPushMessage)
            ->title($this->inboxNotification->title)
            ->body($this->inboxNotification->message)
            ->icon('/logo.png')
            ->badge('/logo.png')
            ->tag('inbox-notification-' . $this->inboxNotification->id)
            ->data([
                'url' => '/inbox',
                'notificationId' => $this->inboxNotification->id,
                'requestId' => $this->inboxNotification->request_id,
                'type' => $this->inboxNotification->type
            ])
            ->options(['TTL' => 3600]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Check if failure is due to expired subscription (HTTP 410 Gone)
        if (strpos($exception->getMessage(), '410') !== false ||
            strpos($exception->getMessage(), 'expired') !== false ||
            strpos($exception->getMessage(), 'Subscription has expired') !== false) {
            Log::warning('Push subscription expired or invalid', [
                'notification_id' => $this->inboxNotification->id,
                'error' => $exception->getMessage()
            ]);
            // Don't log as error - this is expected for expired subscriptions
            return;
        }

        Log::error('InboxPushNotification job failed', [
            'notification_id' => $this->inboxNotification->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil()
    {
        // Don't retry - notifications are time-sensitive
        return now();
    }
}
