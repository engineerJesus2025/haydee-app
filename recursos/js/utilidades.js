/**
 * Utilidades.js
 * Helper global para funciones reutilizables en todo el sistema Haydee.
 */
const Utilidades = {

    /**
     * Inicializa un DataTable con configuración estándar y en español.
     */
    crearDataTable(id_tabla, estructura_filas, datos_parametros, configuraciones_post_creacion = () => {}) {
        return new DataTable(`#${id_tabla}`, {
            destroy: true,
            responsive: true,
            scrollX: true,
            pageLength: 10,
            aaSorting: [],
            // Idioma local asegurado (sin depender de internet)
            language: {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "search": "Buscar:",
                "loadingRecords": "Cargando...",
                "paginate": { "first": "Primero", "last": "Último", "next": "<i class='bi bi-caret-right'></i>", "previous": "<i class='bi bi-caret-left'></i>" }
            },
            ajax: {
                url: "",
                dataSrc: "datos", // Estandarizado al nuevo backend
                type: "POST",
                data: datos_parametros
            },
            columns: estructura_filas,
            drawCallback: function() { $(this).DataTable().columns.adjust(); },
            createdRow: configuraciones_post_creacion
        });
    },

    /**
     * Envía peticiones AJAX al servidor usando Fetch.
     * @param {FormData|Object} datos - Datos a enviar.
     * @param {string} url - URL destino (por defecto la misma página).
     * @returns {Promise<Object>} - Respuesta JSON del servidor.
     */
    async query(datos, spinner = false,url = "") {
        // Manejo del Modal de Carga (si existe en el DOM)
        let modalCargaElement = document.getElementById("modal_carga");
        let modal_carga = modalCargaElement ? new bootstrap.Modal(modalCargaElement) : null;
        let mostrarModal = false;
        let tiempoCarga;

        // Solo mostramos el modal si la petición tarda más de 600ms
        if (modal_carga) {
            tiempoCarga = setTimeout(() => {
                mostrarModal = true;
                modal_carga.show();
            }, 600);
        }

        try {
            const tiempoInicio = performance.now();

            const res = await fetch(url, {
                method: "POST",
                body: datos
            });

            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

            const data = await res.json();

            // Lógica para evitar parpadeos rápidos del modal
            const tiempoTranscurrido = performance.now() - tiempoInicio;
            const tiempoEsperaMin = 700;

            if (mostrarModal && tiempoTranscurrido < tiempoEsperaMin) {
                const restante = tiempoEsperaMin - tiempoTranscurrido;
                await new Promise(resolve => setTimeout(resolve, restante));
            }

            return data;

        } catch (error) {
            console.error("Error en Utilidades.query:", error);
            // Retornamos un objeto de error estandarizado
            return {
                estatus: false,
                mensaje: "Error de conexión o servidor.",
                error: error.toString()
            };
        } finally {
            if (tiempoCarga) clearTimeout(tiempoCarga);
            if (mostrarModal && modal_carga) {
                modal_carga.hide();
            }
        }
    },

    /**
     * Muestra alertas usando SweetAlert2.
     * @param {string} icono - 'success', 'error', 'warning', 'info'.
     * @param {string} titulo - Título de la alerta.
     * @param {string} mensaje - Texto descriptivo.
     * @param {number} tiempo - Tiempo en ms (opcional, defecto 4000).
     */

    /**
     * Realiza una validación AJAX genérica.
     * @param {string} tipo - Tipo de validación (ej. 'validar_mes', 'validar_anio', 'validar_correo').
     * @param {Object} datos - Objeto con los datos a enviar (además de 'validar').
     * @returns {Promise<Object>} - Respuesta del servidor.
    */
    async validar(tipo, datos = {}) {
        const formData = new FormData();
        formData.append('validar', tipo);
        Object.keys(datos).forEach(key => formData.append(key, datos[key]));
        return await this.query(formData);
    },

    mensaje(icono, titulo, mensaje, tiempo = 4000) {
        if (typeof Swal === 'undefined') {
            console.warn("SweetAlert2 no está cargado.");
            alert(`${titulo}: ${mensaje}`);
            return;
        }
        Swal.fire({
            icon: icono,
            timer: tiempo,
            title: titulo,
            text: mensaje,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: "#e01d22",
        });
    },

    /**
     * Formatea una fecha YYYY-MM-DD a DD-MM-YYYY.
     * @param {string} fecha 
     * @returns {string}
     */
    formatearFecha(fecha) {
        if (!fecha) return "N/A";
        const partes = fecha.split("-");
        return (partes.length === 3) ? `${partes[2]}-${partes[1]}-${partes[0]}` : fecha;
    },

    /**
     * Reemplaza un elemento del DOM por otro.
     * @param {string} id - ID del elemento a reemplazar.
     * @param {HTMLElement} nuevoElemento - Nuevo nodo DOM.
     */
    reemplazarElemento(id, nuevoElemento) {
        const el = document.getElementById(id);
        if (el) el.replaceWith(nuevoElemento);
    },

    /**
     * Elimina un elemento del DOM por su ID.
     * @param {string} id 
     */
    eliminarElemento(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
};