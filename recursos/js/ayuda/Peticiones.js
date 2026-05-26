const Peticiones = {
    /**
     * Envía datos al servidor mediante POST.
     */
    async enviar(datos, url = "", mostrarCarga = true, encriptar = false) {
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

            let fetchOptions = {
                method: "POST"
            };

            if (datos instanceof FormData) {
                fetchOptions.body = datos;
            }

            // Realizar la petición con las opciones preparadas
            const respuesta = await fetch(url, fetchOptions);

            // Revisamos qué tipo de contenido nos devolvió el servidor
            const contentType = respuesta.headers.get("content-type");
            let json = null;
            // Si es JSON (incluso si es un error 400 o 403), lo parseamos
            if (contentType && contentType.includes("application/json")) {
                json = await respuesta.json();
            } else {
                // Si NO es JSON (ej. un error fatal 500 que devuelve una pantalla HTML)
                throw new Error(`Error HTTP ${respuesta.status}: Respuesta no válida del servidor.`);
            }

            // INTERCEPTOR DE SESIÓN EXPIRADA (CÓDIGO 401)
            if (respuesta.status === 401) {
                // Escondemos el spinner de carga
                if (tiempoCarga) clearTimeout(tiempoCarga);
                if (modalVisible && modalCarga) modalCarga.hide();

                Alertas.mostrarConAccion(
                    'warning', 
                    'Sesión Expirada', 
                    json.mensaje || 'Por seguridad, inicie sesión nuevamente.', 
                    'Ir al Login',
                    () => { window.location.href = '?pagina=login&accion=inicio'; }
                );
                
                // Devuelve silencioso para que Validador.js no intente procesar el error
                return { estatus: false, silencioso: true };
            }

            // Evitar que el modal parpadee muy rápido
            if (mostrarCarga) {
                const tiempoTranscurrido = performance.now() - tiempoInicio;
                if (modalVisible && tiempoTranscurrido < 700) {
                    await new Promise(resolve => setTimeout(resolve, 700 - tiempoTranscurrido));
                }
            }

            // Devolvemos el JSON (sea exitoso o con errores de validación)
            return json;

        } catch (error) {
            // Si el usuario está cambiando de módulo, no mostramos ni reportamos el error
            if (window.estaSaliendoDeLaPagina) {
                return {
                    estatus: false,
                    silencioso: true // por si Validador necesita saberlo
                };
            }
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

// Variable global para detectar si el usuario está abandonando la página
window.estaSaliendoDeLaPagina = false;

window.addEventListener('beforeunload', () => {
    window.estaSaliendoDeLaPagina = true;
});