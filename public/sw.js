// FileFusion Progressive Web App Service Worker
const CACHE_NAME = 'filefusion-pwa-v2';

const STATIC_PRECACHE = [
    './favicon.ico',
    './assets/vendors/js/lucide.min.js',
    './assets/vendors/js/jquery-3.7.1.min.js',
    './assets/vendors/js/alpinejs.min.js'
];

// Install Event - Pre-cache core assets gracefully (never break on a single missing asset)
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            for (const url of STATIC_PRECACHE) {
                try {
                    const response = await fetch(url);
                    if (response && response.ok) {
                        await cache.put(url, response);
                    }
                } catch (err) {
                    // Ignore non-fatal cache misses during install
                }
            }
        })
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

// IndexedDB helper for Staging Shared Files offline/locally without hitting PHP post_max_size
function openShareDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('filefusion_share_db', 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('shared_files')) {
                db.createObjectStore('shared_files', { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function saveSharedFilesToDB(items) {
    const db = await openShareDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('shared_files', 'readwrite');
        const store = tx.objectStore('shared_files');
        items.forEach((item) => store.add(item));
        tx.oncomplete = () => resolve(true);
        tx.onerror = () => reject(tx.error);
    });
}

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Intercept Web Share Target POSTs to store files in client-side IndexedDB
    // This bypasses PHP's post_max_size and avoids 413 Payload Too Large errors!
    if (request.method === 'POST' && url.pathname.includes('/pwa/share-target')) {
        event.respondWith(
            (async () => {
                try {
                    const formData = await request.formData();
                    const title = formData.get('title') || '';
                    const text = formData.get('text') || '';
                    const sharedUrl = formData.get('url') || '';
                    const files = formData.getAll('shared_files');

                    // If a link or plain text was shared without files
                    if ((!files || files.length === 0 || (files.length === 1 && (!files[0].name || files[0].size === 0))) && (sharedUrl || text)) {
                        const targetUrl = sharedUrl || (text.match(/https?:\/\/[^\s]+/) ? text.match(/https?:\/\/[^\s]+/)[0] : '');
                        if (targetUrl) {
                            return Response.redirect('./panel/add-links?prefill_url=' + encodeURIComponent(targetUrl) + '&prefill_name=' + encodeURIComponent(title || text), 303);
                        }
                        return Response.redirect('./panel/newfile?prefill_content=' + encodeURIComponent(text), 303);
                    }

                    // Process files into IndexedDB
                    const fileRecords = [];
                    for (const file of files) {
                        if (file && file.size > 0) {
                            fileRecords.push({
                                name: file.name || ('shared_' + Date.now()),
                                type: file.type || 'application/octet-stream',
                                size: file.size,
                                blob: file,
                                timestamp: Date.now()
                            });
                        }
                    }

                    if (fileRecords.length > 0) {
                        await saveSharedFilesToDB(fileRecords);
                        return Response.redirect('./panel/upload?intent=pwa_share', 303);
                    }

                    return Response.redirect('./panel', 303);
                } catch (err) {
                    console.error('[SW Web Share Target Intercept Error]:', err);
                    return fetch(event.request);
                }
            })()
        );
        return;
    }

    // Only handle HTTP/HTTPS GET requests
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // Never cache sensitive security endpoints
    if (
        url.pathname.includes('/reveal/') ||
        url.pathname.includes('/2fa/') ||
        url.pathname.includes('/logout') ||
        url.pathname.includes('/auth') ||
        url.pathname.includes('/devices/')
    ) {
        return;
    }

    // 1. Navigation requests (HTML pages) -> Network-first
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cached = await caches.match(request);
                return cached || new Response('Offline - Please reconnect to access FileFusion.', {
                    status: 503,
                    headers: { 'Content-Type': 'text/plain' }
                });
            })
        );
        return;
    }

    // 2. Static vendor scripts, fonts, and icons -> Stale-while-revalidate / Cache-first
    if (
        url.pathname.includes('/assets/vendors/') ||
        url.pathname.includes('/assets/icons/') ||
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
                }).catch(() => null);
            })
        );
        return;
    }

    // Default: Network with fallback to cache
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request);
        })
    );
});

// =========================================================================
// VAPID WEB PUSH & NOTIFICATION CLICK HANDLERS (PWA / Web / Apple iOS)
// =========================================================================
self.addEventListener('push', (event) => {
    let payload = {
        title: 'FileFusion',
        body: 'You have a new notification from FileFusion.',
        icon: './favicon.ico',
        badge: './favicon.ico',
        tag: 'filefusion_general',
        url: './panel'
    };

    if (event.data) {
        try {
            payload = Object.assign(payload, event.data.json());
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const targetUrl = payload.url || (payload.data && payload.data.url) || './panel';

    const notificationOptions = {
        body: payload.body,
        icon: payload.icon || './favicon.ico',
        badge: payload.badge || './favicon.ico',
        tag: payload.tag || 'filefusion_push',
        data: {
            url: targetUrl,
            id: payload.id || Date.now()
        },
        vibrate: [200, 100, 200],
        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, notificationOptions)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    let targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : './panel';

    // Resolve target URL to absolute URL if needed
    try {
        if (!targetUrl.startsWith('http')) {
            targetUrl = new URL(targetUrl, self.location.origin).href;
        }
    } catch (e) {}

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
