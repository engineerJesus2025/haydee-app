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
                "paginate": { "page_size": "Mostrar","first": "Primero", "last": "Último", "next": "<i class='bi bi-caret-right'></i>", "previous": "<i class='bi bi-caret-left'></i>" }
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

    cargarTabulador(idContenedor, url, columnas, opciones = {}) {
        const config = {
            movableColumns: true,
            pagination: true,
            paginationSize: opciones.paginaSize || 10,
            paginationSizeSelector: [5, 10, 20, 50],
            columns: columnas,
            layout: "fitColumns",
            responsiveLayout: "collapse", // Ayuda si la pantalla es muy pequeña
            responsiveLayoutCollapseFormatter: function(data) {
                if (!data || data.length === 0) {
                    return ""; 
                }

                // 2. Si hay columnas ocultas, entonces sí creamos la lista de Bootstrap
                let lista = document.createElement("ul");
                lista.className = "list-group list-group-flush w-100 shadow-sm rounded border my-2";

                data.forEach(function(col) {
                    let item = document.createElement("li");
                    item.className = "list-group-item d-flex justify-content-between align-items-center py-2 text-sm";

                    let titulo = document.createElement("strong");
                    titulo.className = "text-muted";
                    titulo.innerHTML = col.title;

                    let valorContenedor = document.createElement("div");
                    valorContenedor.className = "text-end";

                    if (col.value instanceof Node) {
                        valorContenedor.appendChild(col.value);
                    } else {
                        valorContenedor.innerHTML = col.value || '<span class="text-muted fst-italic">Vacío</span>';
                    }

                    item.appendChild(titulo);
                    item.appendChild(valorContenedor);
                    lista.appendChild(item);
                });

                return lista;
            },
            rowFormatter: function(row){
                // Si quieres forzar un padding vertical extra para que respire mejor
                row.getElement().style.padding = "5px 0";
            },
            paginationCounter: function(pageSize, currentRow, currentPage, totalRows, totalPages) {
                // Si la tabla está vacía (por ejemplo, por una búsqueda sin resultados)
                if (totalRows === 0) {
                    return "Mostrando registros del 0 al 0 de un total de 0 registros";
                }
                
                let startRow = currentRow;
                let endRow = currentRow + pageSize - 1;
                
                // Asegurarnos de que el final no sobrepase el total
                if (endRow > totalRows) {
                    endRow = totalRows;
                }
                
                return `Mostrando registros del ${startRow} al ${endRow} de un total de ${totalRows} registros`;
            },

            // 1. Usar una URL base (necesario para que Tabulator dispare la petición inicial)
            ajaxURL: url || window.location.href, 
            
            // 2. Parámetros que enviaremos a PHP
            ajaxParams: {
                operacion: "consulta",
                ...opciones.parametrosExtra
            },

            // 3. LA MAGIA: Interceptar la petición y usar tu Utilidades.query()
            ajaxRequestFunc: async function(url, config, params) {
                // Convertir los parámetros de Tabulator a FormData para PHP
                let datos = new FormData();
                for (let key in params) {
                    datos.append(key, params[key]);
                }
                
                // Ejecutar tu propia función que ya tiene el spinner de carga
                // Mandamos url = "" para que dispare a la misma página (igual que DataTables)
                const respuesta = await Utilidades.query(datos, true, "");

                // Tabulator espera que esta promesa retorne el arreglo de datos
                if (respuesta && respuesta.estatus === true) {
                    return respuesta.datos || respuesta.data || [];
                } else {
                    console.error("Error en la consulta Tabulator:", respuesta?.mensaje);
                    Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'Error al cargar la tabla');
                    return []; // Retorna vacío para no romper la tabla
                }
            },

            // 4. Traducciones
            locale: "es",
            langs: {
                "es": {
                    "pagination": {
                        "first": "Primero",
                        "first_title": "Primera página",
                        "last": "Último",
                        "last_title": "Última página",
                        "prev": "<i class='bi bi-caret-left-fill'></i>",
                        "prev_title": "Página anterior",
                        "next": "<i class='bi bi-caret-right-fill'></i>",
                        "next_title": "Página siguiente",
                        "all": "Todos",
                        "page_size": "Mostrar"
                    },
                    "data": {
                        "loading": "Cargando registros...",
                        "error": "Error de carga",
                    }
                }
            },
            placeholder: "No se encontraron registros",
            ...opciones
        };

        // Extraemos el contenedor HTML para inyectarle clases adicionales (igual que en la estática)
        let contenedorHtml = typeof idContenedor === "string" ? document.getElementById(idContenedor) : idContenedor;
        if (contenedorHtml && opciones.cssClass) {
            contenedorHtml.classList.add(opciones.cssClass);
        }

        return new Tabulator(`#${idContenedor}`, config);
    },

    /**
     * Inicializa un Tabulator para datos locales/estáticos (como vistas previas o detalles)
     */
    cargarTabuladorEstatico(idContenedor, data, columnas, opciones = {}) {
        const config = {
            data: data, // Inyecta los datos directamente, sin hacer petición AJAX
            layout: "fitColumns",
            pagination: true,
            paginationSize: opciones.paginaSize || 5, // Por defecto 5 para vistas previas
            paginationSizeSelector: [5, 10, 20],
            columns: columnas,
            
            // Reutilizamos el idioma español
            locale: "es",
            langs: {
                "es": {
                    "pagination": {
                        "page_size": "Mostrar",
                        "first": "Primero", "first_title": "Primera página",
                        "last": "Último", "last_title": "Última página",
                        "prev": "Anterior", "prev_title": "Página anterior",
                        "next": "Siguiente", "next_title": "Página siguiente",
                        "all": "Todos",
                    },
                    "data": {
                        "loading": "Cargando registros...",
                        "error": "Error de carga",
                    }
                }
            },
            
            // Reutilizamos el contador de registros
            paginationCounter: function(pageSize, currentRow, currentPage, totalRows, totalPages) {
                if (totalRows === 0) return "Mostrando registros del 0 al 0 de un total de 0 registros";
                let startRow = currentRow;
                let endRow = currentRow + pageSize - 1;
                if (endRow > totalRows) endRow = totalRows;
                return `Mostrando registros del ${startRow} al ${endRow} de un total de ${totalRows} registros`;
            },
            
            placeholder: "No hay detalles para mostrar",
            
            // Reutilizamos el formateador responsivo de Bootstrap
            responsiveLayout: "collapse",
            responsiveLayoutCollapseFormatter: function(data) {
                if (!data || data.length === 0) return ""; 
                let lista = document.createElement("ul");
                lista.className = "list-group list-group-flush w-100 shadow-sm rounded border my-2";
                data.forEach(function(col) {
                    let item = document.createElement("li");
                    item.className = "list-group-item d-flex justify-content-between align-items-center py-2 text-sm";
                    let titulo = document.createElement("strong");
                    titulo.className = "text-muted";
                    titulo.innerHTML = col.title;
                    let valorContenedor = document.createElement("div");
                    valorContenedor.className = "text-end";
                    if (col.value instanceof Node) valorContenedor.appendChild(col.value);
                    else valorContenedor.innerHTML = col.value || '<span class="text-muted fst-italic">Vacío</span>';
                    item.appendChild(titulo);
                    item.appendChild(valorContenedor);
                    lista.appendChild(item);
                });
                return lista;
            },
            
            rowFormatter: function(row){
                row.getElement().style.padding = "5px 0";
            },
            ...opciones
        };

        // Extraemos el contenedor HTML para inyectarle clases adicionales si las hay
        let contenedorHtml = typeof idContenedor === "string" ? document.getElementById(idContenedor) : idContenedor;
        if (contenedorHtml && opciones.cssClass) {
            contenedorHtml.classList.add(opciones.cssClass);
        }

        return new Tabulator(contenedorHtml, config);
    },


    /**
     * Envía peticiones AJAX al servidor usando Fetch.
     * @param {FormData|Object} datos - Datos a enviar.
     * @param {boolean} spinner - Define si se muestra el modal de carga global (por defecto true).
     * @param {string} url - URL destino (por defecto la misma página).
     * @returns {Promise<Object>} - Respuesta JSON del servidor.
     */
    async query(datos, spinner = true, url = "") {
        // Manejo del Modal de Carga (si existe en el DOM)
        let modalCargaElement = document.getElementById("modal_carga");
        let modal_carga = modalCargaElement ? new bootstrap.Modal(modalCargaElement) : null;
        let mostrarModal = false;
        let tiempoCarga;

        // Solo preparamos el modal si existe y si 'spinner' es true
        if (modal_carga && spinner) {
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

            // Lógica para evitar parpadeos rápidos del modal (Solo si se activó el spinner)
            if (spinner) {
                const tiempoTranscurrido = performance.now() - tiempoInicio;
                const tiempoEsperaMin = 700;

                if (mostrarModal && tiempoTranscurrido < tiempoEsperaMin) {
                    const restante = tiempoEsperaMin - tiempoTranscurrido;
                    await new Promise(resolve => setTimeout(resolve, restante));
                }
            }

            return data;

        } catch (error) {
            console.error("Error en Utilidades.query:", error);
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