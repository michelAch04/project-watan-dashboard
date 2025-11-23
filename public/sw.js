const CACHE_VERSION = 'v1';
const CACHE_NAME = 'offline-' + CACHE_VERSION;

const preLoad = function () {
    return caches.open(CACHE_NAME).then(function (cache) {
        // caching index and important routes
        return cache.addAll(filesToCache);
    });
};

self.addEventListener("install", function (event) {
    event.waitUntil(preLoad());
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    // Clean up old caches
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(function() {
            return self.clients.claim();
        })
    );
});

const filesToCache = [
    '/offline.html'
];

// Helper function to check if request should be cached
const shouldCache = function(request) {
    const url = new URL(request.url);

    // NEVER cache these paths - they handle authentication and sessions
    const noCachePaths = [
        '/login',
        '/logout',
        '/verify-otp',
        '/resend-otp',
        '/api/',
        '/sanctum/',
    ];

    // Check if URL matches any no-cache path
    const isNoCachePath = noCachePaths.some(path => url.pathname.startsWith(path));
    if (isNoCachePath) {
        return false;
    }

    // Don't cache POST, PUT, DELETE, PATCH requests
    if (request.method !== 'GET') {
        return false;
    }

    // Don't cache requests with query parameters (except for pagination)
    if (url.search && !url.search.includes('page=')) {
        return false;
    }

    return true;
};

const checkResponse = function (request) {
    return new Promise(function (fulfill, reject) {
        // Always include credentials for session cookies
        fetch(request, {
            credentials: 'same-origin'
        }).then(function (response) {
            if (response.status !== 404) {
                fulfill(response);
            } else {
                reject();
            }
        }, reject);
    });
};

const addToCache = function (request) {
    // Only cache http(s) requests that should be cached
    if (!request.url.startsWith('http') || !shouldCache(request)) {
        return Promise.resolve();
    }

    return caches.open(CACHE_NAME).then(function (cache) {
        return fetch(request, {
            credentials: 'same-origin'
        }).then(function (response) {
            // Only cache successful responses
            if (response.status === 200) {
                return cache.put(request, response.clone());
            }
            return response;
        });
    });
};

const returnFromCache = function (request) {
    return caches.open(CACHE_NAME).then(function (cache) {
        return cache.match(request).then(function (matching) {
            if (!matching || matching.status === 404) {
                return cache.match("offline.html");
            } else {
                return matching;
            }
        });
    });
};

self.addEventListener("fetch", function (event) {
    // Skip caching for non-cacheable requests
    if (!shouldCache(event.request)) {
        // Just pass through with credentials
        event.respondWith(
            fetch(event.request, {
                credentials: 'same-origin'
            })
        );
        return;
    }

    // For cacheable requests, try network first, then cache
    event.respondWith(
        checkResponse(event.request).catch(function () {
            return returnFromCache(event.request);
        })
    );

    // Update cache in background if it's an http request
    if (event.request.url.startsWith('http')) {
        event.waitUntil(addToCache(event.request));
    }
});

// Push Notification Handlers
self.addEventListener('push', function(event) {
    console.log('Push event received:', event);

    let notificationData = {
        title: 'New Notification',
        body: 'You have a new notification',
        icon: '/logo.png',
        badge: '/logo.png',
        tag: 'notification',
        requireInteraction: false,
        data: {
            url: '/'
        }
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            notificationData = {
                title: payload.title || notificationData.title,
                body: payload.body || payload.message || notificationData.body,
                icon: payload.icon || notificationData.icon,
                badge: payload.badge || notificationData.badge,
                tag: payload.tag || notificationData.tag,
                requireInteraction: payload.requireInteraction || false,
                data: payload.data || notificationData.data,
                actions: payload.actions || []
            };
        } catch (e) {
            console.error('Error parsing push notification data:', e);
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            data: notificationData.data,
            actions: notificationData.actions,
            vibrate: [200, 100, 200]
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked:', event);
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/inbox';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // Check if there's already a window open
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

self.addEventListener('notificationclose', function(event) {
    console.log('Notification closed:', event);
});
