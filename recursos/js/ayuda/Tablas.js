/**
 * Tablas.js
 * Propósito: Configurar e inicializar tablas de datos (DataTables y Tabulator).
 * Dependencias: Peticiones
 */
const Tablas = {
    cargarTabulador(idContenedor, url, columnas, opciones = {}, mostrarCarga = true) {
        const config = {
            movableColumns: true,
            pagination: true,
            paginationSize: opciones.paginaSize || 10,
            paginationSizeSelector: [5, 10, 20, 50],
            columns: columnas,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            
            // Formateador responsivo extraído del original
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
            
            rowFormatter: function(row){ row.getElement().style.padding = "5px 0"; },
            
            paginationCounter: function(pageSize, currentRow, currentPage, totalRows, totalPages) {
                if (totalRows === 0) return "Mostrando registros del 0 al 0 de un total de 0 registros";
                let startRow = currentRow;
                let endRow = currentRow + pageSize - 1;
                if (endRow > totalRows) endRow = totalRows;
                return `Mostrando registros del ${startRow} al ${endRow} de un total de ${totalRows} registros`;
            },

            ajaxURL: url || window.location.href, 
            ajaxParams: { operacion: "consulta", ...opciones.parametrosExtra },

            // Aquí integramos Peticiones.js con el nuevo orden de parámetros
            ajaxRequestFunc: async function(urlAjax, configAjax, params) {
                let datos = new FormData();
                for (let key in params) {
                    datos.append(key, params[key]);
                }
                
                // Usamos: Peticiones.enviar(datos, url, mostrarCarga)
                const respuesta = await Peticiones.enviar(datos, "", mostrarCarga);

                if (respuesta && respuesta.estatus === true) {
                    return respuesta.datos || respuesta.data || [];
                } else {
                    console.error("Error en la consulta Tabulator:", respuesta?.mensaje);
                    // Asume que tienes Alertas.js configurado
                    if(typeof Alertas !== 'undefined') {
                        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'Error al cargar la tabla');
                    }
                    return []; 
                }
            },

            locale: "es",
            langs: {
                "es": {
                    "pagination": { "first": "Primero", "last": "Último", "prev": "<i class='bi bi-caret-left-fill'></i>", "next": "<i class='bi bi-caret-right-fill'></i>", "all": "Todos", "page_size": "Mostrar" },
                    "data": { "loading": "Cargando registros...", "error": "Error de carga" }
                }
            },
            placeholder: "No se encontraron registros",
            columnDefaults: {
                tooltip: true, // Muestra tooltip en las celdas si el texto es muy largo
                headerTooltip: true // Muestra tooltip en los encabezados
            },
            ...opciones
        };

        let contenedorHtml = typeof idContenedor === "string" ? document.getElementById(idContenedor) : idContenedor;
        if (contenedorHtml && opciones.cssClass) contenedorHtml.classList.add(opciones.cssClass);

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

};