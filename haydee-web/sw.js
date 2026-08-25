self.addEventListener('push', function(event) {
    if (event.data) {
        // los datos que enviará PHP
        const data = event.data.json();
        
        const options = {
            body: data.descripcion,
            icon: 'recursos/img/utils/logo-haydee.ico', 
            // badge: 'recursos/img/badge.png',  Agregar despues 
            vibrate: [200, 100, 200]
        };

        event.waitUntil(
            self.registration.showNotification(data.titulo, options)
        );
    }
});

// Evento cuando el residente hace clic en la notificación
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    // Usamos el scope como URL base (Ej: http://localhost/haydee-app/ o https://tu-dominio.com/)
    const targetUrl = self.registration.scope;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
            // Revisar si ya hay una pestaña de Haydee abierta
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url.startsWith(targetUrl) && 'focus' in client) {
                    return client.focus(); // Si está abierta, la trae al frente
                }
            }
            // Si no hay ninguna pestaña abierta, abrimos una nueva en la URL base
            if (clients.openWindow) {
                return clients.openWindow(targetUrl); 
            }
        })
    );
});