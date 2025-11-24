<?php

namespace App\Http\Controllers;

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

            // Use the trait's method to create/update subscription
            $user->updatePushSubscription(
                $validated['endpoint'],
                $validated['keys']['p256dh'],
                $validated['keys']['auth'],
                'aesgcm'
            );

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
            $user = Auth::user();

            // Use the trait's method to delete subscription
            $user->deletePushSubscription($validated['endpoint']);

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
