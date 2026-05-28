const Peticiones = {
    /**
     * Envía datos al servidor soportando reescritura de métodos HTTP (POST, PUT, DELETE).
     * @param {FormData|Object} datos - El payload a enviar.
     * @param {string} url - URL del endpoint.
     * @param {string} metodoHttp - Verbo REST semántico ('POST', 'PUT', 'DELETE').
     * @param {boolean} mostrarCarga - Si se muestra el spinner.
     */
    async enviar(datos, url = "", mostrarCarga = true, metodoHttp = "POST") {
        let modalCarga = null;
        let tiempoCarga;
        let modalVisible = false;

        if (mostrarCarga) {
            const elementoModal = document.getElementById("modal_carga");
            if (elementoModal) {
                modalCarga = new bootstrap.Modal(elementoModal);
                tiempoCarga = setTimeout(() => {
                    modalVisible = true;
                    modalCarga.show();
                }, 600);
            }
        }
        try {
            const tiempoInicio = performance.now();
            metodoHttp = metodoHttp.toUpperCase();

            // Físicamente viaja por POST para soportar $_POST y $_FILES en PHP
            let fetchOptions = {
                method: "POST",
                headers: {}
            };

            if (metodoHttp !== "POST") {
                fetchOptions.headers["X-HTTP-Method-Override"] = metodoHttp;

                // Si son datos de formulario (FormData), inyectamos el campo oculto
                if (datos instanceof FormData) {
                    datos.append("_method", metodoHttp);
                } else if (typeof datos === "object" && datos !== null) {
                    // Si es un objeto plano, le metemos la propiedad
                    datos["_method"] = metodoHttp;
                }
            }

            if (datos instanceof FormData) {
                fetchOptions.body = datos;
            } else if (typeof datos === "object" && datos !== null) {
                fetchOptions.headers["Content-Type"] = "application/json";
                fetchOptions.body = JSON.stringify(datos);
            }

            const respuesta = await fetch(url, fetchOptions);
            const contentType = respuesta.headers.get("content-type");
            let json = null;

            if (contentType && contentType.includes("application/json")) {
                json = await respuesta.json();
            } else {
                throw new Error(`Error HTTP ${respuesta.status}: Respuesta no válida del servidor.`);
            }

            if (respuesta.status === 401) {
                if (tiempoCarga) clearTimeout(tiempoCarga);
                if (modalVisible && modalCarga) modalCarga.hide();

                Alertas.mostrarConAccion(
                    'warning', 
                    'Sesión Expirada', 
                    json.mensaje || 'Por seguridad, inicie sesión nuevamente.', 
                    'Ir al Login',
                    () => { window.location.href = '?pagina=login&accion=inicio'; }
                );
                return { estatus: false, silencioso: true };
            }

            if (mostrarCarga) {
                const tiempoTranscurrido = performance.now() - tiempoInicio;
                if (modalVisible && tiempoTranscurrido < 700) {
                    await new Promise(resolve => setTimeout(resolve, 700 - tiempoTranscurrido));
                }
            }

            return json;

        } catch (error) {
            if (window.estaSaliendoDeLaPagina) {
                return { estatus: false, silencioso: true };
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

window.estaSaliendoDeLaPagina = false;
window.addEventListener('beforeunload', () => {
    window.estaSaliendoDeLaPagina = true;
});