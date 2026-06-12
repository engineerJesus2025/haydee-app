document.addEventListener('DOMContentLoaded', async () => {
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        try {
            // Registra el Service Worker
            const register = await navigator.serviceWorker.register(URL_BASE + 'sw.js', {
                scope: URL_BASE
            }); 
            
            // Pedimos permiso al residente y creamos/obtenemos la suscripción local
            const subscription = await register.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(PUBLIC_VAPID_KEY)
            });

            // Caché local
            const endpointActual = subscription.endpoint;
            const endpointGuardado = localStorage.getItem('haydee_push_endpoint');

            // Solo enviamos a BD si no hay caché o si el navegador cambió el endpoint por seguridad
            if (endpointActual !== endpointGuardado) {
                await guardarSuscripcionEnBD(subscription);
            }

        } catch (error) {
            console.warn('El usuario denegó los permisos o hubo un error Push:', error);
        }
    }
});

async function guardarSuscripcionEnBD(subscription) {
    const formData = new FormData();
    formData.append('operacion', 'registrar_suscripcion');
    formData.append('endpoint', subscription.endpoint);
    formData.append('p256dh', btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))));
    formData.append('auth', btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))));

    const url = '?pagina=suscripcion_push&accion=registrar_suscripcion';
    const respuesta = await Peticiones.enviar(formData, url, false);

    if (respuesta && respuesta.estatus) {
        console.log("Haydee Push sincronizado con éxito.");
        // Si el backend guardó con éxito, guardamos el endpoint en la caché del navegador
        localStorage.setItem('haydee_push_endpoint', subscription.endpoint);
    } else if (respuesta && !respuesta.silencioso) {
        console.error("Haydee Push Error:", respuesta.mensaje);
        // No guardamos en localStorage para forzar un reintento en la próxima recarga
    }
}

// Función matemática auxiliar para la API de Push
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}