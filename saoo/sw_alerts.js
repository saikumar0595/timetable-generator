/**
 * Service Worker for ChronoGen Alerts
 * Handles background notifications
 */

self.addEventListener('install', (event) => {
    self.skipWaiting();
    console.log('✓ Alert Service Worker Installed');
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
    console.log('✓ Alert Service Worker Activated');
});

self.addEventListener('push', (event) => {
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/assets_login/img/authentication.svg',
        badge: '/assets_login/img/authentication.svg',
        tag: data.tag || 'class-alert',
        requireInteraction: true
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
