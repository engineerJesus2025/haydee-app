document.addEventListener('DOMContentLoaded', async () => {
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        try {
            // 1. Registramos el Service Worker
            const register = await navigator.serviceWorker.register(URL_BASE + 'sw.js', {
                scope: URL_BASE
            }); 
            
            // 2. Pedimos permiso al residente y creamos la suscripción
            const subscription = await register.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(PUBLIC_VAPID_KEY)
            });

            // 3. Enviamos a BD
            guardarSuscripcionEnBD(subscription);

        } catch (error) {
            console.warn('El usuario denegó los permisos o hubo un error Push:', error);
            // Opcional: Mostrar alerta solo si quieres forzar al usuario a aceptar
            // Alertas.mostrar('warning', 'Aviso', 'Si deseas recibir notificaciones de la cartelera, habilita los permisos en tu navegador.');
        }
    }
});

async function guardarSuscripcionEnBD(subscription) {
    const formData = new FormData();
    formData.append('operacion', 'registrar_suscripcion');
    formData.append('endpoint', subscription.endpoint);
    formData.append('p256dh', btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))));
    formData.append('auth', btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))));

    // ====================================================================
    // USO DE TU HELPER Peticiones.js
    // Pasamos: 1. formData, 2. La URL, 3. false (para ocultar el modal de carga)
    // ====================================================================
    const url = '?pagina=suscripcion_push&accion=registrar_suscripcion';
    const respuesta = await Peticiones.enviar(formData, url, false);

    if (respuesta && respuesta.estatus) {
        console.log("Haydee Push:", respuesta.mensaje);
        // Mantenemos la alerta en silencio para no interrumpir al usuario cada vez que entra
    } else if (respuesta && !respuesta.silencioso) {
        console.error("Haydee Push Error:", respuesta.mensaje);
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