const CACHE_NAME = 'sigi-pwa-v2';

const scopeUrl = (path) => new URL(path, self.registration.scope).href;
const OFFLINE_URL = scopeUrl('offline.html');
const MANIFEST_URL = scopeUrl('manifest.json');

const PRECACHE_URLS = [
    'offline.html',
    'img/icon-192x192.png',
    'img/icon-512x512.png',
    'assets/css/bootstrap.min.css',
    'assets/css/icons.min.css',
    'assets/css/theme.min.css',
    'assets/js/jquery.min.js',
    'assets/js/bootstrap.bundle.min.js',
    'assets/js/app.js',
    'assets/js/pwa.js'
].map(scopeUrl);

const STATIC_EXT_RE = /\.(?:css|js|png|jpg|jpeg|gif|svg|webp|ico|woff|woff2|ttf|eot)$/i;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((names) => Promise.all(
                names.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Manifest dinámico: siempre intentar red primero para reflejar cambios de Datos del Sistema.
    if (url.href === MANIFEST_URL || url.pathname.endsWith('/manifest.json')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || Response.error()))
        );
        return;
    }

    // Rutas PHP/HTML: siempre red para respetar sesiones, permisos y datos en vivo.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(OFFLINE_URL).then((cached) => cached || Response.error())
            )
        );
        return;
    }

    // Assets estáticos (/assets/, /img/, /images/): Stale-While-Revalidate.
    if (
        url.pathname.includes('/assets/') ||
        url.pathname.includes('/img/') ||
        url.pathname.includes('/images/') ||
        STATIC_EXT_RE.test(url.pathname)
    ) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                cache.match(request).then((cached) => {
                    const networkFetch = fetch(request)
                        .then((response) => {
                            if (response && response.ok && response.type === 'basic') {
                                cache.put(request, response.clone());
                            }
                            return response;
                        })
                        .catch(() => cached || Response.error());

                    return cached || networkFetch;
                })
            )
        );
    }
});
