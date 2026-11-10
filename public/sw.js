/*
 * EMMS service worker.
 *
 * It exists so browsers offer "Install app". It does not cache anything and does not touch requests:
 * every page, Livewire call, upload and API request goes to the server as usual, so asset statuses are never stale.
 * Change VERSION only to force browsers to pick up a new copy of this file.
 */
const VERSION = 'emms-pwa-1';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Some browsers only treat an app as installable when a fetch handler exists. This one does nothing:
// it never answers the request, so the browser handles it itself.
self.addEventListener('fetch', () => {});
