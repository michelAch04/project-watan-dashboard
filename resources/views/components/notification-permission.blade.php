<div x-data="notificationPermission()" x-show="showBanner" x-cloak class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-1">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm md:text-base font-medium">
                        Enable notifications to stay updated with your inbox
                    </p>
                    <p class="text-xs md:text-sm text-indigo-100 mt-0.5">
                        Get instant alerts when you receive new messages
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="enableNotifications"
                    :disabled="loading"
                    class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-medium text-sm hover:bg-indigo-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Enable</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Enabling...</span>
                    </span>
                </button>
                <button
                    @click="dismissBanner"
                    class="p-2 hover:bg-indigo-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function notificationPermission() {
    return {
        showBanner: false,
        loading: false,

        init() {
            this.checkIfShouldShow();
        },

        checkIfShouldShow() {
            if (!window.pushNotificationManager?.isSupported()) {
                return;
            }

            const permission = window.pushNotificationManager.getPermissionStatus();
            const dismissed = localStorage.getItem('notification-permission-dismissed');

            this.showBanner = permission === 'default' && !dismissed;
        },

        async enableNotifications() {
            this.loading = true;

            try {
                const result = await window.pushNotificationManager.subscribe();

                if (result.success) {
                    this.showBanner = false;
                    this.showSuccessMessage();
                } else {
                    this.showErrorMessage(result.message);
                }
            } catch (error) {
                console.error('Error enabling notifications:', error);
                this.showErrorMessage('Failed to enable notifications');
            } finally {
                this.loading = false;
            }
        },

        dismissBanner() {
            this.showBanner = false;
            localStorage.setItem('notification-permission-dismissed', 'true');
        },

        showSuccessMessage() {
            if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                Alpine.store('toast').show('Notifications enabled successfully!', 'success');
            } else {
                alert('Notifications enabled successfully!');
            }
        },

        showErrorMessage(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                Alpine.store('toast').show(message, 'error');
            } else {
                alert(message);
            }
        }
    }
}
</script>
