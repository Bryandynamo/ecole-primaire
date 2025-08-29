self.addEventListener('install', function(event) {
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  return self.clients.claim();
});

self.addEventListener('fetch', function(event) {
  const req = event.request;
  // Only handle same-origin GET requests; let the browser handle cross-origin (e.g., CDNs)
  const url = new URL(req.url);
  if (req.method !== 'GET' || url.origin !== self.location.origin) {
    return; // do not intercept
  }

  event.respondWith(
    (async () => {
      try {
        const cached = await caches.match(req);
        if (cached) return cached;
        return await fetch(req);
      } catch (e) {
        // As a last resort, just fail silently rather than spamming console
        return fetch(req);
      }
    })()
  );
});
