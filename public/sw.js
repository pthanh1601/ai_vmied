self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const data = event.data ? event.data.text() : 'Default Notification';
    const title = 'Neo Framework Push';
    const options = {
        body: data,
        icon: '/favicon.ico', // Ensure this exists or use a placeholder
        badge: '/favicon.ico'
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
