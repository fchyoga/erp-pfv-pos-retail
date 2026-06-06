const CACHE_NAME = 'pfv-pos-cache-v1';

// Assets to cache
const urlsToCache = [
  '/pos',
  '/favicon.ico',
  '/logo.png',
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net/npm/localforage@1.10.0/dist/localforage.min.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName !== CACHE_NAME;
        }).map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  // Only bypass non-GET requests (like POST requests for Livewire action updates)
  if (event.request.method !== 'GET') {
    return;
  }

  // Network First, fallback to cache
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Clone the response and save it to the cache
        if (response && response.status === 200 && response.type === 'basic') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });
        }
        return response;
      })
      .catch(() => {
        // If network fails, try cache
        return caches.match(event.request).then(response => {
            if (response) {
                return response;
            }
            // If completely offline and not in cache, fallback to /pos if it's a navigation request
            if (event.request.mode === 'navigate') {
                return caches.match('/pos');
            }
        });
      })
  );
});
