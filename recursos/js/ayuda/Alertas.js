/**
 * Alertas.js
 * Propósito: Centralizar el uso de notificaciones (SweetAlert2).
 */
const Alertas = {
    /**
     * Muestra una alerta en pantalla.
     * @param {string} tipo - 'success', 'error', 'warning', 'info'
     * @param {string} titulo - Título de la alerta
     * @param {string} mensaje - Texto detallado
     * @param {number} tiempo - Tiempo en milisegundos antes de cerrarse
     */
    mostrar(tipo, titulo, mensaje, tiempo = 4000) {
        if (typeof Swal === 'undefined') {
            console.warn("SweetAlert2 no está cargado. Usando alert nativo.");
            alert(`${titulo}: ${mensaje}`);
            return;
        }
        
        Swal.fire({
            icon: tipo,
            title: titulo,
            text: mensaje,
            timer: tiempo,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: "#e01d22",
        });
    },

    /**
     * Muestra una alerta bloqueante y ejecuta una acción al confirmarla.
     * Ideal para redirecciones forzadas (como sesión expirada).
     */
    mostrarConAccion(tipo, titulo, mensaje, textoBoton, funcionAccion) {
        if (typeof Swal === 'undefined') {
            alert(`${titulo}: ${mensaje}`);
            if (typeof funcionAccion === 'function') funcionAccion();
            return;
        }

        Swal.fire({
            icon: tipo,
            title: titulo,
            text: mensaje,
            allowOutsideClick: false,   // No permite cerrar haciendo clic afuera
            allowEscapeKey: false,      // No permite cerrar con la tecla Esc
            confirmButtonText: textoBoton,
            confirmButtonColor: "#e01d22"
        }).then((result) => {
            if (result.isConfirmed && typeof funcionAccion === 'function') {
                funcionAccion();
            }
        });
    }
};