'use strict';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch {
            data = { body: event.data.text() };
        }
    }

    const { title = 'Tickets DEC', body = '', data: payload = {} } = data;
    const icon = '/favicon.svg';

    const options = {
        body,
        icon,
        badge: icon,
        vibrate: [100, 50, 100],
        data: payload,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', async (event) => {
    event.notification.close();

    const url = new URL(event.notification.data?.url || '/', self.location.origin);

    event.waitUntil(
        (async () => {
            const allClients = await self.clients.matchAll({
                type: 'window',
                includeUncontrolled: true,
            });

            for (const client of allClients) {
                if (new URL(client.url).origin === self.location.origin) {
                    await client.navigate(url);
                    return client.focus();
                }
            }

            return self.clients.openWindow(url);
        })()
    );
});