<?php

namespace App\Models;

use App\Notifications\InboxPushNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class InboxNotification extends Model
{
    protected $fillable = [
        'user_id',
        'request_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class, 'request_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Create notification for user
     */
    public static function createForUser($userId, $requestId, $type, $title, $message)
    {
        $notification = static::create([
            'user_id' => $userId,
            'request_id' => $requestId,
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]);

        // Send push notification
        $notification->sendPushNotification();

        return $notification;
    }

    /**
     * Send push notification to user
     */
    public function sendPushNotification()
    {
        try {
            $user = $this->user;

            if (!$user) {
                Log::warning('InboxNotification: User not found for notification ID ' . $this->id);
                return;
            }

            // Check if user has push subscriptions
            if ($user->pushSubscriptions()->exists()) {
                $user->notify(new InboxPushNotification($this));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage(), [
                'notification_id' => $this->id,
                'user_id' => $this->user_id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            $notification->sendPushNotification();
        });
    }
}