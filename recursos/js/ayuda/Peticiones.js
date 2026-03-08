/**
 * Peticiones.js
 * Propósito: Manejar la comunicación asíncrona (AJAX/Fetch) con el servidor.
 */
const Peticiones = {
    /**
     * Envía datos al servidor mediante POST.
     * @param {string} url - Ruta del servidor (vacío = misma página)
     * @param {FormData|Object} datos - La información a enviar
     * @param {boolean} mostrarCarga - Si debe mostrar el modal del spinner
     * @returns {Promise<Object>} - Respuesta del servidor en JSON
     */
    async enviar(datos, url = "", mostrarCarga = true) {
        let modalCarga = null;
        let tiempoCarga;
        let modalVisible = false;

        // Mostrar spinner de carga si es requerido
        if (mostrarCarga) {
            const elementoModal = document.getElementById("modal_carga");
            if (elementoModal) {
                modalCarga = new bootstrap.Modal(elementoModal);
                tiempoCarga = setTimeout(() => {
                    modalVisible = true;
                    modalCarga.show();
                }, 600); // Esperar un poco antes de mostrarlo para evitar parpadeos
            }
        }

        try {
            const tiempoInicio = performance.now();

            const respuesta = await fetch(url, {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                throw new Error(`Error HTTP: ${respuesta.status}`);
            }

            const json = await respuesta.json();

            // Evitar que el modal parpadee muy rápido si la petición fue veloz
            if (mostrarCarga) {
                const tiempoTranscurrido = performance.now() - tiempoInicio;
                if (modalVisible && tiempoTranscurrido < 700) {
                    await new Promise(resolve => setTimeout(resolve, 700 - tiempoTranscurrido));
                }
            }

            return json;

        } catch (error) {
            console.error("Error en Peticiones.enviar:", error);
            return {
                estatus: false,
                mensaje: "Error de conexión con el servidor.",
                error: error.message
            };
        } finally {
            if (tiempoCarga) clearTimeout(tiempoCarga);
            if (modalVisible && modalCarga) {
                modalCarga.hide();
            }
        }
    }
};