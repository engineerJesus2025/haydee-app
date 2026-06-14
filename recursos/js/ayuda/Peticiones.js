const Peticiones = {
    /**
     * Envía datos al servidor soportando reescritura de métodos HTTP (POST, PUT, DELETE).
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
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            };

            if (metodoHttp !== "POST") {
                fetchOptions.headers["X-HTTP-Method-Override"] = metodoHttp;

                // Si son datos de formulario (FormData), inyectamos el campo oculto
                if (datos instanceof FormData) {
                    datos.append("_method", metodoHttp);
                } else if (typeof datos === "object" && datos !== null) {
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

            // --- INTERCEPCIÓN GLOBAL DE SEGURIDAD Y ERRORES ---

            // 401: Sesión Expirada
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

            // 429 Anti-Flood (Limite de peticiones excedido)
            if (respuesta.status === 429) {
                if (tiempoCarga) clearTimeout(tiempoCarga);
                if (modalVisible && modalCarga) modalCarga.hide();
                // Forzamos la recarga. index.php interceptara la peticion
                window.location.reload();
                return { estatus: false, silencioso: true };
            }

            // 403 Prohibido (Bloqueos de Red o Permisos RBAC)
            if (respuesta.status === 403) {
                if (tiempoCarga) clearTimeout(tiempoCarga);
                if (modalVisible && modalCarga) modalCarga.hide();

                // Diferenciamos el tipo de 403:
                // Si el mensaje indica que la red fue castigada, forzamos recarga para mostrar la vista roja (Firewall)
                if (json.mensaje && json.mensaje.toLowerCase().includes('suspendida')) {
                    window.location.reload();
                } else {
                    // Si es un simple rechazo por falta de permisos de usuario, mostramos una alerta suave
                    // Esto evita recargar la página y hacerle perder los datos del formulario al usuario
                    Alertas.mostrar('error', 'Acceso Denegado', json.mensaje || 'No tiene permisos para realizar esta acción.');
                }
                return { estatus: false, silencioso: true };
            }

            // Errores Genéricos desde el backend (500 Interno, 400 Bad Request, etc.)
            if (!respuesta.ok) {
                if (tiempoCarga) clearTimeout(tiempoCarga);
                if (modalVisible && modalCarga) modalCarga.hide();
                
                // Imprimimos la Referencia de Incidente (Ref) que configuramos en index.php si está presente
                let textoError = json.mensaje || 'Ocurrió un error inesperado al procesar la solicitud.';
                if (json.ref) textoError += `<br><br><span style="font-size: 0.85em; color: #6c757d;">Ref: <b>${json.ref}</b></span>`;

                Alertas.mostrar('error', `Error ${respuesta.status}`, textoError);
                return { estatus: false, silencioso: true };
            }

            // Control visual de la carga para que sea fluida en conexiones rápidas
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
            
            // Ocultar modal si ocurre un error de red puro (ej. servidor apagado o sin internet)
            if (tiempoCarga) clearTimeout(tiempoCarga);
            if (modalVisible && modalCarga) modalCarga.hide();

            Alertas.mostrar('error', 'Fallo de Red', 'No se pudo establecer conexión con el servidor.');
            return {
                estatus: false,
                mensaje: "Error de conexión con el servidor.",
                error: error.message,
                silencioso: true
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