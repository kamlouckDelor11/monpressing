const CACHE_NAME = 'pressing-v1';

// On ne met en cache que les routes sûres qui ne changent pas de nom
const ASSETS = [
    '/',
    '/manifest.json',
    // Ajoutez ici vos icônes si elles sont dans public/icons/
    // '/icons/icon-192x192.png', 
];

// Installation du Service Worker
self.addEventListener('install', (e) => {
    // Force le SW à prendre le contrôle immédiatement sans attendre
    self.skipWaiting();
    
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('PWA : Mise en cache des ressources critiques');
            return cache.addAll(ASSETS);
        })
    );
});

// Activation : Nettoyage des anciens caches
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    console.log('PWA : Nettoyage ancien cache', key);
                    return caches.delete(key);
                }
            }));
        })
    );
});

// Stratégie : Réseau d'abord, puis Cache si hors-ligne
self.addEventListener('fetch', (e) => {
    e.respondWith(
        fetch(e.request).catch(() => {
            return caches.match(e.request);
        })
    );
});