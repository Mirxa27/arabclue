/**
 * HabibiStay Progressive Web App Service Worker
 * Implements advanced caching strategies, offline functionality,
 * and background synchronization for app-like experience
 * 
 * @version 1.0.0
 */

const CACHE_NAME = 'habibistay-v1.0.0';
const DYNAMIC_CACHE = 'habibistay-dynamic-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Strategic cache assets for optimal performance
const CACHE_ASSETS = [
  '/',
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  '/js/components/sara-chatbot.js',
  '/manifest.json',
  '/assets/icons/icon-192x192.png',
  '/assets/fonts/Inter-Regular.woff2',
  '/assets/fonts/Inter-Bold.woff2',
  // Pre-cache critical UI components
  '/api/theme/current',
  '/api/content/homepage'
];

// Network-first routes for dynamic content
const NETWORK_FIRST_ROUTES = [
  '/api/',
  '/stays/',
  '/bookings/',
  '/messages/',
  '/notifications/'
];

// Cache-first routes for static assets
const CACHE_FIRST_ROUTES = [
  '/assets/',
  '/images/',
  '/fonts/',
  '/css/',
  '/js/'
];

/**
 * Install Event - Pre-cache critical assets
 * Implements aggressive caching for instant load times
 */
self.addEventListener('install', event => {
  console.log('[ServiceWorker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[ServiceWorker] Pre-caching assets');
        return cache.addAll(CACHE_ASSETS);
      })
      .then(() => self.skipWaiting())
      .catch(err => console.error('[ServiceWorker] Pre-cache failed:', err))
  );
});

/**
 * Activate Event - Clean old caches and claim clients
 * Implements cache versioning for seamless updates
 */
self.addEventListener('activate', event => {
  console.log('[ServiceWorker] Activating...');
  
  event.waitUntil(
    Promise.all([
      // Clean old caches
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME && cacheName !== DYNAMIC_CACHE) {
              console.log('[ServiceWorker] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      // Claim all clients immediately
      self.clients.claim()
    ])
  );
});

/**
 * Fetch Event - Implement intelligent caching strategies
 * Uses different strategies based on request patterns
 */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // Handle API calls with network-first strategy
  if (request.url.includes('/api/')) {
    event.respondWith(networkFirstStrategy(request));
    return;
  }

  // Handle static assets with cache-first strategy
  if (CACHE_FIRST_ROUTES.some(route => request.url.includes(route))) {
    event.respondWith(cacheFirstStrategy(request));
    return;
  }

  // Handle navigation requests
  if (request.mode === 'navigate') {
    event.respondWith(navigationHandler(request));
    return;
  }

  // Default strategy: Network with cache fallback
  event.respondWith(networkWithCacheFallback(request));
});

/**
 * Network-First Strategy
 * Prioritizes fresh content with cache fallback
 */
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[ServiceWorker] Network request failed, falling back to cache');
    
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline fallback for navigation requests
    if (request.mode === 'navigate') {
      return caches.match(OFFLINE_URL);
    }
    
    // Return 503 for API requests
    return new Response(
      JSON.stringify({ error: 'Offline', message: 'Service temporarily unavailable' }),
      { status: 503, headers: { 'Content-Type': 'application/json' } }
    );
  }
}

/**
 * Cache-First Strategy
 * Optimizes performance for static assets
 */
async function cacheFirstStrategy(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    // Update cache in background
    updateCacheInBackground(request);
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('[ServiceWorker] Cache and network failed:', error);
    return new Response('Resource not available', { status: 404 });
  }
}

/**
 * Navigation Handler
 * Special handling for page navigation with offline support
 */
async function navigationHandler(request) {
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
      return networkResponse;
    }
  } catch (error) {
    // Network failed
  }
  
  // Try cache
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }
  
  // Return offline page
  return caches.match(OFFLINE_URL);
}

/**
 * Network with Cache Fallback
 * Default strategy for general requests
 */
async function networkWithCacheFallback(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok && request.method === 'GET') {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || new Response('Resource not available', { status: 404 });
  }
}

/**
 * Background Cache Update
 * Updates cache without blocking response
 */
function updateCacheInBackground(request) {
  fetch(request).then(response => {
    if (response.ok) {
      caches.open(CACHE_NAME).then(cache => {
        cache.put(request, response);
      });
    }
  }).catch(() => {
    // Silently fail background updates
  });
}

/**
 * Push Notification Handler
 * Enables real-time notifications for bookings and messages
 */
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'New notification from HabibiStay',
    icon: '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/badge-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'view',
        title: 'View',
        icon: '/assets/icons/view-icon.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/assets/icons/close-icon.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('HabibiStay', options)
  );
});

/**
 * Background Sync Handler
 * Synchronizes offline actions when connectivity returns
 */
self.addEventListener('sync', event => {
  console.log('[ServiceWorker] Background sync triggered');
  
  if (event.tag === 'sync-bookings') {
    event.waitUntil(syncBookings());
  }
  
  if (event.tag === 'sync-messages') {
    event.waitUntil(syncMessages());
  }
  
  if (event.tag === 'sync-analytics') {
    event.waitUntil(syncAnalytics());
  }
});

/**
 * Sync Bookings
 * Synchronizes offline booking requests
 */
async function syncBookings() {
  try {
    const cache = await caches.open('offline-bookings');
    const requests = await cache.keys();
    
    for (const request of requests) {
      try {
        const response = await fetch(request.clone());
        if (response.ok) {
          await cache.delete(request);
        }
      } catch (error) {
        console.error('[ServiceWorker] Failed to sync booking:', error);
      }
    }
  } catch (error) {
    console.error('[ServiceWorker] Sync bookings failed:', error);
  }
}

/**
 * Sync Messages
 * Synchronizes offline messages
 */
async function syncMessages() {
  try {
    const db = await openIndexedDB();
    const messages = await getOfflineMessages(db);
    
    for (const message of messages) {
      try {
        const response = await fetch('/api/messages', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(message)
        });
        
        if (response.ok) {
          await deleteOfflineMessage(db, message.id);
        }
      } catch (error) {
        console.error('[ServiceWorker] Failed to sync message:', error);
      }
    }
  } catch (error) {
    console.error('[ServiceWorker] Sync messages failed:', error);
  }
}

/**
 * Message Event Handler
 * Enables communication between service worker and clients
 */
self.addEventListener('message', event => {
  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data.type === 'CACHE_URLS') {
    const urlsToCache = event.data.payload;
    caches.open(DYNAMIC_CACHE).then(cache => {
      cache.addAll(urlsToCache);
    });
  }
  
  if (event.data.type === 'CLEAR_CACHE') {
    caches.keys().then(cacheNames => {
      cacheNames.forEach(cacheName => {
        caches.delete(cacheName);
      });
    });
  }
});

/**
 * Performance Monitoring
 * Tracks cache hit rates and performance metrics
 */
let cacheHits = 0;
let cacheMisses = 0;

setInterval(() => {
  if (cacheHits + cacheMisses > 0) {
    const hitRate = (cacheHits / (cacheHits + cacheMisses)) * 100;
    console.log(`[ServiceWorker] Cache hit rate: ${hitRate.toFixed(2)}%`);
    
    // Send analytics
    fetch('/api/analytics/service-worker', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        cacheHitRate: hitRate,
        totalRequests: cacheHits + cacheMisses
      })
    }).catch(() => {});
    
    // Reset counters
    cacheHits = 0;
    cacheMisses = 0;
  }
}, 60000); // Every minute
