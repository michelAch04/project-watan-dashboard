<?php

namespace App\Notifications;

use App\Models\InboxNotification as InboxNotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class InboxPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inboxNotification;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * Determine if the notification should be sent.
     * This prevents errors if user or notification is deleted before job processes.
     */
    public function shouldSend($notifiable)
    {
        return $this->inboxNotification !== null && $notifiable !== null;
    }

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
}
