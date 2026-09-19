/* ==========================================================================
   JMOS — Service Worker for Offline Caching, PWA & Web Push Notifications
   ========================================================================== */

const CACHE_NAME = 'jmos-cache-v2.5.1';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/assets/img/jeota-logo.png',
  '/css/main.css',
  '/css/layout.css',
  '/css/components.css',
  '/css/views.css',
  '/css/modals.css'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('Service worker pre-caching partial failure:', err);
      });
    })
  );
  self.skipWaiting();
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch Event (Network First with Cache Fallback for navigation and assets)
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  
  // Exclude API calls and dynamic data requests from hard cache
  const url = new URL(event.request.url);
  if (url.pathname.startsWith('/api') || url.pathname.startsWith('/sanctum')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache).catch(() => {});
          });
        }
        return networkResponse;
      })
      .catch(() => {
        return caches.match(event.request);
      })
  );
});

// Push Event (Web Push API)
self.addEventListener('push', (event) => {
  let data = {
    title: 'JMOS Alert',
    body: 'You have a new operational update from Jeota Media.',
    icon: '/assets/img/jeota-logo.png',
    badge: '/assets/img/jeota-logo.png',
    url: '/'
  };

  if (event.data) {
    try {
      data = Object.assign(data, event.data.json());
    } catch (_) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon || '/assets/img/jeota-logo.png',
    badge: data.badge || '/assets/img/jeota-logo.png',
    data: { url: data.url || '/' },
    vibrate: [100, 50, 100],
    actions: [
      { action: 'open', title: 'Open in JMOS' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Notification Click Event
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  let rawUrl = event.notification.data?.url || '/';
  let targetUrl = '/';
  try {
    targetUrl = new URL(rawUrl, self.location.origin).href;
  } catch (_) {
    targetUrl = self.location.origin + '/';
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes(self.location.origin) && 'focus' in client) {
          if ('navigate' in client && targetUrl !== client.url) {
            client.navigate(targetUrl);
          }
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
