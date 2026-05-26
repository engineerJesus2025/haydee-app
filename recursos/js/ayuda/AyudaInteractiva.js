/**
 * AyudaInteractiva.js
 * Propósito: Configurar e inicializar los recorridos guiados (Driver.js) en los módulos.
 */
const AyudaInteractiva = {
    driverActivo: null, // Guardamos la instancia para poder limpiarla luego

    /**
     * Inicializa los eventos para lanzar la ayuda interactiva.
     */
    inicializar(opciones) {
        // Asegurarnos de que Driver.js esté cargado
        if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
            console.error("Driver.js no está cargado. No se puede inicializar la ayuda interactiva.");
            return;
        }

        const driver = window.driver.js.driver;
        const btnAyuda = document.getElementById('btn-ayuda-tour');
        const modalElement = document.getElementById(opciones.idModal);

        // Configuración visual y de comportamiento estandarizada
        const configBase = {
            showProgress: true,
            animate: true,
            smoothScroll: false, 
            allowKeyboardControl: false,
            nextBtnText: 'Siguiente ➔',
            prevBtnText: '⬅ Anterior',
            doneBtnText: 'Entendido',
            progressText: 'Paso {{current}} de {{total}}',
            onHighlightStarted: (element) => {
                if (element) {
                    element.scrollIntoView({ behavior: 'instant', block: 'center' });
                    // Disparamos un evento resize para forzar a Driver.js a recalcular la posición
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 10);
                }
            }
        };

        // Evento del botón flotante de ayuda
        if (btnAyuda) {
            btnAyuda.addEventListener('click', () => {
                // Si el modal está visible en la pantalla (clase 'show' de Bootstrap)
                if (modalElement && modalElement.classList.contains('show')) {
                    this.driverActivo = driver({ ...configBase, steps: opciones.pasosModal });
                } else {
                    // Si estamos en la vista principal
                    window.scrollTo({ top: 0, behavior: 'instant' });
                    this.driverActivo = driver({ ...configBase, steps: opciones.pasosPrincipal });
                }
                
                this.driverActivo.drive();
            });
        }

        // Limpiar el tour si el usuario cierra el modal abruptamente mientras lo recorre
        if (modalElement) {
            modalElement.addEventListener('hide.bs.modal', () => {
                if (this.driverActivo) {
                    try { this.driverActivo.destroy(); } catch (e) { console.warn("Error limpiando Driver.js", e) }
                }
            });
        }
    }
};