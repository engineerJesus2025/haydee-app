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
    }
};