/**
 * Push Notification Manager
 * Handles push notification subscription and management
 */

class PushNotificationManager {
    constructor() {
        this.swRegistration = null;
        this.publicKey = null;
        this.isSubscribed = false;
    }

    /**
     * Initialize push notifications
     */
    async init() {
        // Check if user is authenticated by looking for auth indicators
        const isAuthenticated = document.querySelector('meta[name="csrf-token"]') &&
                                document.body.dataset.authenticated !== 'false';

        if (!isAuthenticated) {
            console.log('User not authenticated, skipping push notification initialization');
            return false;
        }

        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications are not supported in this browser');
            return false;
        }

        try {
            // Wait for service worker to be ready
            this.swRegistration = await navigator.serviceWorker.ready;

            // Get VAPID public key from server
            await this.fetchPublicKey();

            // Check current subscription status
            await this.checkSubscription();

            return true;
        } catch (error) {
            console.error('Error initializing push notifications:', error);
            return false;
        }
    }

    /**
     * Fetch VAPID public key from server
     */
    async fetchPublicKey() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            const response = await fetch('/api/push/public-key', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('User not authenticated');
                }
                throw new Error(`Failed to fetch public key: ${response.status}`);
            }

            const data = await response.json();
            this.publicKey = data.publicKey;
        } catch (error) {
            console.error('Error fetching public key:', error);
            throw error;
        }
    }

    /**
     * Check current subscription status
     */
    async checkSubscription() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = subscription !== null;
            return this.isSubscribed;
        } catch (error) {
            console.error('Error checking subscription:', error);
            return false;
        }
    }

    /**
     * Request notification permission and subscribe
     */
    async subscribe() {
        try {
            // Request permission
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                console.log('Notification permission denied');
                return { success: false, message: 'Permission denied' };
            }

            // Check if already subscribed
            let subscription = await this.swRegistration.pushManager.getSubscription();

            if (!subscription) {
                // Subscribe to push notifications
                const applicationServerKey = this.urlBase64ToUint8Array(this.publicKey);

                subscription = await this.swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                });
            }

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);

            this.isSubscribed = true;

            return { success: true, message: 'Subscribed successfully' };
        } catch (error) {
            console.error('Error subscribing to push notifications:', error);
            return { success: false, message: error.message };
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();

            if (subscription) {
                // Unsubscribe from browser
                await subscription.unsubscribe();

                // Remove subscription from server
                await this.removeSubscriptionFromServer(subscription);

                this.isSubscribed = false;
            }

            return { success: true, message: 'Unsubscribed successfully' };
        } catch (error) {
            console.error('Error unsubscribing from push notifications:', error);
            return { success: false, message: error.message };
        }
    }

    /**
     * Send subscription to server
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify(subscription.toJSON())
            });

            if (!response.ok) throw new Error('Failed to send subscription to server');

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error sending subscription to server:', error);
            throw error;
        }
    }

    /**
     * Remove subscription from server
     */
    async removeSubscriptionFromServer(subscription) {
        try {
            const response = await fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    endpoint: subscription.endpoint
                })
            });

            if (!response.ok) throw new Error('Failed to remove subscription from server');

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error removing subscription from server:', error);
            throw error;
        }
    }

    /**
     * Convert base64 string to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Get current permission status
     */
    getPermissionStatus() {
        if (!('Notification' in window)) {
            return 'unsupported';
        }
        return Notification.permission;
    }

    /**
     * Check if notifications are supported
     */
    isSupported() {
        return ('serviceWorker' in navigator) &&
               ('PushManager' in window) &&
               ('Notification' in window);
    }
}

// Export singleton instance
window.pushNotificationManager = new PushNotificationManager();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pushNotificationManager.init();
    });
} else {
    window.pushNotificationManager.init();
}
