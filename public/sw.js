const CACHE_NAME = 'mikrotik-manager-v1.1.0';

const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
    '/icons/icon.svg',
    '/favicon.ico'
];

// Install: Cache essential offline shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate: Purge stale caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event handler
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Bypass non-GET requests (e.g. POST for voucher generation, payments, router sync)
    if (request.method !== 'GET') {
        return;
    }

    // Bypass browser extensions and chrome-extension schemes
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // 1. Navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return caches.match('/offline.html');
                })
        );
        return;
    }

    // 2. Static Vite build assets and icons: Cache-first with network fallback
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // 3. Realtime polling & data routes (e.g. Live dashboard metrics, online users polling)
    // Network first with standard fetch; don't cache live router states
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request).then((cached) => cached || Response.error());
        })
    );
});

// Support manual skipWaiting from UI
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
