// FileFusion Progressive Web App Service Worker
const CACHE_NAME = 'filefusion-pwa-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_PRECACHE = [
    OFFLINE_URL,
    '/favicon.ico',
    '/assets/vendors/js/lucide.min.js',
    '/assets/vendors/js/jquery-3.7.1.min.js',
    '/assets/vendors/js/alpinejs.min.js'
];

// Install Event - Pre-cache offline page & core vendor scripts
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_PRECACHE);
        }).then(() => self.skipWaiting())
    );
});

// Activate Event - Clean up outdated caches
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

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle HTTP/HTTPS GET requests
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // Never cache sensitive security endpoints
    if (
        url.pathname.includes('/reveal/') ||
        url.pathname.includes('/2fa/') ||
        url.pathname.includes('/logout') ||
        url.pathname.includes('/auth')
    ) {
        return;
    }

    // 1. Navigation requests (HTML pages) -> Network-first with Offline Fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                const cachedOffline = await cache.match(OFFLINE_URL);
                return cachedOffline || new Response('Offline', { status: 503, statusText: 'Offline' });
            })
        );
        return;
    }

    // 2. Static vendor scripts, fonts, and icons -> Stale-while-revalidate / Cache-first
    if (
        url.pathname.startsWith('/assets/vendors/') ||
        url.pathname.startsWith('/assets/icons/') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdn.jsdelivr.net')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in background
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {});
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

    // Default: Network with fetch
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request);
        })
    );
});
