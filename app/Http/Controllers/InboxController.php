<?php

namespace App\Http\Controllers;

use App\Models\InboxNotification;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    /**
     * Display inbox
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = InboxNotification::with(['requestHeader.requestStatus'])
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = InboxNotification::forUser($user->id)->unread()->count();

        return view('inbox.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = InboxNotification::findOrFail($id);
        
        if ($notification->user_id != Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        InboxNotification::forUser($user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = InboxNotification::findOrFail($id);
        
        if ($notification->user_id != Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Clear all notifications (delete all)
     */
    public function clearAll()
    {
        $user = Auth::user();

        InboxNotification::forUser($user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Get unread count (AJAX)
     */
    public function unreadCount()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0], 401);
        }

        $count = InboxNotification::forUser($user->id)->unread()->count();

        return response()->json(['count' => $count]);
    }
}