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

            ajaxRequestFunc: async function(urlAjax, configAjax, params) {
                let datos = new FormData();
                for (let key in params) {
                    datos.append(key, params[key]);
                }
                
                const respuesta = await Peticiones.enviar(datos, "", mostrarCarga);

                let registros = [];

                /**
                 * Si estatus es false, mostrará el error automáticamente.
                 * Si estatus es true, ejecutará el callback para asignar los datos.
                 */
                Validador.procesarRespuesta(respuesta, (res) => {
                    registros = res.datos || res.data || [];
                });

                return registros;
            },

            locale: "es",
            langs: {
                "es": {
                    "pagination": { "first": "Primero", "last": "Último", "prev": "<i class='bi bi-caret-left-fill'></i>", "next": "<i class='bi bi-caret-right-fill'></i>", "all": "Todos", "page_size": "Mostrar" },
                    "data": { "loading": "Cargando registros...", "error": "Error de carga" }
                }
            },
            placeholder: `
                <div class="text-center p-5 text-muted d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-search fs-1 mb-3 opacity-50"></i>
                    <h5 class="fw-bold mb-1">No se encontraron resultados</h5>
                    <p class="mb-0 small">No hay registros que coincidan con tu búsqueda o la tabla está vacía.</p>
                </div>
            `,
            columnDefaults: {
                tooltip: true, // Muestra tooltip en las celdas si el texto es muy largo
                headerTooltip: true // Muestra tooltip en los encabezados
            },
            ...opciones
        };

        let contenedorHtml = typeof idContenedor === "string" ? document.getElementById(idContenedor) : idContenedor;
        if (contenedorHtml && opciones.cssClass) contenedorHtml.classList.add(opciones.cssClass);

        let tabla = new Tabulator(contenedorHtml, config);

        tabla.on("renderComplete", function() {
            if (typeof Tooltips !== 'undefined') {
                Tooltips.inicializarTodos(contenedorHtml); 
            }
        });

        // ==============================================
        // Detección automática de búsqueda
        // ==============================================
        if (typeof Notificaciones !== 'undefined') {
            // Mandamos la columnaBusqueda (si no existe, llegará como undefined y Notificaciones hará el auto-descubrimiento)
            Notificaciones.resaltarEnTabulator(tabla, opciones.columnaBusqueda);
        }

        return tabla;
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
            
            placeholder: `
                <div class="text-center p-4 text-muted d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-clipboard-x fs-2 mb-2 opacity-50"></i>
                    <h6 class="fw-bold mb-1">Sin detalles para mostrar</h6>
                    <p class="mb-0 small">No se encontró información asociada a este registro.</p>
                </div>
            `,
            
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

        let contenedorHtml = typeof idContenedor === "string" ? document.getElementById(idContenedor) : idContenedor;
        if (contenedorHtml && opciones.cssClass) {
            contenedorHtml.classList.add(opciones.cssClass);
        }

        let tabla = new Tabulator(contenedorHtml, config);

        tabla.on("renderComplete", function() {
            if (typeof Tooltips !== 'undefined') {
                Tooltips.inicializarTodos(contenedorHtml);
            }
        });

        return tabla;
    },

    // Actualiza esta función en tu Helper de Tablas
    inicializarBuscadorGlobal(tablaInstancia, idInput, columnasFiltro, filtroPersonalizado = null) {
        const inputBusqueda = document.getElementById(idInput);
        const btnLimpiar = document.getElementById("btn_limpiar_busqueda");
        const iconoBusqueda = document.getElementById("icono_busqueda");

        if (!inputBusqueda) return; 

        let timeoutBusqueda;

        inputBusqueda.addEventListener("input", function(e) {
            clearTimeout(timeoutBusqueda);
            let valor = e.target.value.trim().toLowerCase(); // Aseguramos minúsculas aquí

            // Lógica visual
            if (valor.length > 0) {
                btnLimpiar?.classList.remove("d-none");
                iconoBusqueda?.classList.add("text-primary");
                inputBusqueda.classList.replace("rounded-end","border-end-0");
            } else {
                btnLimpiar?.classList.add("d-none");
                iconoBusqueda?.classList.remove("text-primary");
                inputBusqueda.classList.replace("border-end-0","rounded-end");
            }

            // Lógica de búsqueda (Debounce)
            timeoutBusqueda = setTimeout(() => {
                if (valor === "") {
                    tablaInstancia.clearFilter();
                } else {
                    if (filtroPersonalizado && typeof filtroPersonalizado === 'function') {
                        // Si el módulo envió una función propia, la usamos
                        tablaInstancia.setFilter(function(data) {
                            return filtroPersonalizado(data, valor);
                        });
                    } else {
                        // Si no, usamos el comportamiento por defecto (automático)
                        let filtros = columnasFiltro
                            .filter(col => col.field) 
                            .map(col => ({ field: col.field, type: "like", value: valor }));
                        tablaInstancia.setFilter([filtros]);
                    }
                }
            }, 300); 
        });

        if (btnLimpiar) {
            btnLimpiar.addEventListener("click", () => {
                inputBusqueda.value = "";
                inputBusqueda.dispatchEvent(new Event("input"));
                inputBusqueda.focus();
            });
        }

        // Atajo de teclado global
        document.addEventListener("keydown", function(e) {
            if (e.key === "/" && e.target.tagName !== "INPUT" && e.target.tagName !== "TEXTAREA") {
                e.preventDefault();
                inputBusqueda.focus();
            }
        });
    }

};