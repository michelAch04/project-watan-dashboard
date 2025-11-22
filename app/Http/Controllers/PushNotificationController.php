<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Get VAPID public key
     */
    public function getPublicKey()
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key')
        ]);
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:500',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        try {
            $user = Auth::user();

            // Delete old subscription for this endpoint if exists
            PushSubscription::where('endpoint', $validated['endpoint'])->delete();

            // Create new subscription
            PushSubscription::create([
                'user_id' => $user->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => 'aesgcm',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push notification subscription created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Push subscription error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create push subscription'
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string'
        ]);

        try {
            PushSubscription::where('endpoint', $validated['endpoint'])
                ->where('user_id', Auth::id())
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Push notification subscription removed'
            ]);
        } catch (\Exception $e) {
            Log::error('Push unsubscribe error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove push subscription'
            ], 500);
        }
    }
}
