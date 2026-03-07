/**
 * mensualidades_ajax.js
 * Gestión de Mensualidades - Peticiones AJAX
 * Dependencias: utilidades.js, validaciones.js, formatoFechas.js
 */

// ============================================================
// VARIABLES GLOBALES
// ============================================================
let tablaMensualidades;
let tablaApartamentos;
let tablaAsignar;

let modalMensualidad = new bootstrap.Modal(document.getElementById("modal_mensualidad"), { focus: false });
let modalApartamentos = new bootstrap.Modal(document.getElementById("modal_mensualidades_apartamentos"), { focus: false });
let modalCarga = new bootstrap.Modal("#modal_carga");

let selectMesAsignar = document.getElementById('mes_select_asignar');
let botonFormulario = document.getElementById("boton_formulario");
let tablaMensualidadAsignar = document.querySelector("#tabla_mensualidad_asignar");
let tablaAsignarInicial = tablaMensualidadAsignar?.innerHTML || '';

let tasaDolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);
let permisoEliminar = document.querySelector("#permiso_eliminar")?.value;

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    consultarMensualidades();
    verificarMeses();

    // Eventos
    selectMesAsignar?.addEventListener("change", (e) => {
        tablaMensualidadAsignar.innerHTML = tablaAsignarInicial;
        let fecha = e.target.selectedOptions[0]?.id;
        if (fecha) cargarTablaPresupuestos(fecha);
    });

    document.getElementById("modal_mensualidad")?.addEventListener("hide.bs.modal", resetModalMensualidad);
    document.getElementById("modal_mensualidades_apartamentos")?.addEventListener("shown.bs.modal", () => {
        if ($.fn.DataTable.isDataTable("#mensualidades_apartamentos")) {
            $('#mensualidades_apartamentos').DataTable().columns.adjust().draw();
        }
    });
});

// ============================================================
// CONSULTAS PRINCIPALES
// ============================================================

/**
 * Consulta la lista de mensualidades agrupadas por mes
 */
async function consultarMensualidades() {
    const formatoMes = (cell) => {
        const row = cell.getData();
        let fecha = new Date(`${row.mes}/01/${row.anio}`);
        return `${fecha.toLocaleString("es-ES", { month: 'long' })} del ${row.anio}`.toUpperCase();
    };
    
    const formatoMonto = (cell) => {
        const row = cell.getData();
        return `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / row.tasa_dolar).toFixed(2)} $`;
    };

    const formatoDeuda = (cell) => {
        const row = cell.getData();
        let deuda = row.monto - row.pagado;
        if (deuda < 0) return '<span class="text-success fw-bold">Deuda Cancelada</span>';
        return `<span class="text-danger">${deuda.toFixed(2)} Bs. / ${(deuda / row.tasa_dolar).toFixed(2)} $</span>`;
    };

    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" title="Ver detalles"><i class="bi bi-eye-fill"></i></button>
            <button type="button" class="btn btn-info btn-sm text-white cuadro-pagos" style="background-color:#3939a9;" title="Cuadro de Pagos PDF"><i class="bi bi-card-checklist"></i></button>
            <button type="button" class="btn btn-success btn-sm modificar" title="Modificar"><i class="bi bi-pencil-square"></i></button>`;
        if (permisoEliminar == 1) {
            html += `<button type="button" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "PERÍODO", field: "anio", formatter: formatoMes, minWidth: 150, responsive: 0 },
        { title: "MONTO TOTAL", field: "monto", formatter: formatoMonto, minWidth: 180 },
        { title: "POR RECAUDAR", field: "pagado", formatter: formatoDeuda, minWidth: 180 },
        {
            title: "ACCIONES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 180, responsive: 0, download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                // Extraemos TODA la información directamente desde Tabulator
                const row = cell.getData();
                const mes = String(row.mes).padStart(2, '0');
                const fecha = `${row.anio}-${mes}-01`;

                if (btn.classList.contains('vista-previa')) {
                    // Solo necesita la fecha
                    mostrarVistaPrevia(null, fecha);
                } 
                else if (btn.classList.contains('modificar')) {
                    // Simulamos el "fila.dataset" que esperaba la función antigua
                    const mockFila = { dataset: { intereses: row.porcentaje_interes, limite: row.limite_mensualidad } };
                    prepararModificarcion(mockFila, fecha, row.ids, row.ids_apartamentos);
                } 
                else if (btn.classList.contains('eliminar')) {
                    confirmarEliminar(fecha);
                } 
                else if (btn.classList.contains('cuadro-pagos')) {
                    // Envío dinámico de formulario PDF
                    let form = document.createElement('form');
                    form.action = "?pagina=reportes&accion=cuadro_pagos";
                    form.method = "POST";
                    form.target = "_blank";
                    
                    let input = document.createElement('input');
                    input.type = "hidden";
                    input.name = "select_reporte";
                    input.value = fecha;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }
            }
        }
    ];

    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar_mensualidades_mes' }
    };

    tablaMensualidades = Utilidades.cargarTabulador("tabla_mensualidad", "", columnas, opcionesExtra);
    setTimeout(seleccionarMensualidadPorNotificacion, 500);

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim().toLowerCase();
            
            // Si el input está vacío, limpiamos los filtros
            if (valor === "") {
                tablaMensualidades.clearFilter();
                return;
            }

            // LA MAGIA: Función de filtrado personalizada
            tablaMensualidades.setFilter(function(data) {
                // 1. Reconstruir el texto del período tal como se ve en pantalla
                let fechaObj = new Date(`${data.mes}/01/${data.anio}`);
                let textoPeriodo = `${fechaObj.toLocaleString("es-ES", { month: 'long' })} del ${data.anio}`.toLowerCase();
                
                // 2. Reconstruir los textos de los montos y deudas
                let montoTotal = parseFloat(data.monto).toFixed(2);
                let deuda = data.monto - data.pagado;
                let textoEstado = deuda <= 0 ? "deuda cancelada" : deuda.toFixed(2);
                
                // 3. Evaluar si lo que escribió el usuario coincide con alguna columna
                return textoPeriodo.includes(valor) || 
                       String(data.anio).includes(valor) || 
                       montoTotal.includes(valor) || 
                       textoEstado.includes(valor);
            });
        });
    }
}

/**
 * Verifica qué meses tienen presupuesto pero no mensualidad
 */
async function verificarMeses() {
    const formData = new FormData();
	formData.append('operacion', 'verificar_meses');
	const respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const meses = respuesta.datos || [];
    if (meses.length === 0) {
        document.getElementById("boton_registrar")?.closest(".col")?.setAttribute("hidden", "");
        return;
    }

    document.getElementById("boton_registrar")?.closest(".col")?.removeAttribute("hidden");
    const span = document.getElementById("boton_registrar")?.nextElementSibling;
    if (span) span.textContent = `*Hay ${meses.length} mes(es) pendiente(s)`;

    // Construir opciones del select
    let fragment = document.createDocumentFragment();
    meses.forEach(m => {
        let fecha = new Date(`${m.mes_presupuesto}/01/${m.anio_presupuesto}`);
        let option = document.createElement("option");
        // option.id = fecha.toLocaleDateString('es-ES');
        option.id = FormatoFechas.formatoFechaBD(fecha.toLocaleDateString('es-ES'));
        option.textContent = fecha.toLocaleString("es-ES", { month: 'long', year: 'numeric' }).toUpperCase();
        fragment.appendChild(option);
    });

    selectMesAsignar.innerHTML = '';
    selectMesAsignar.appendChild(fragment);
    if (selectMesAsignar.children.length > 0) {
        cargarTablaPresupuestos(selectMesAsignar.selectedOptions[0].id);
    }
}

/**
 * Carga la tabla de asignación de presupuestos por apartamento
 */
async function cargarTablaPresupuestos(fecha) {
    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_mensualidades");
    formData.append("fecha", fecha);

    const respuesta = await Utilidades.query(formData);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const detallesPresupuesto = respuesta.datos || [];
    if (detallesPresupuesto.length === 0) return;

    const thead = tablaMensualidadAsignar.querySelector("thead tr");
    const tbody = tablaMensualidadAsignar.querySelector("tbody");
    const tfoot = tablaMensualidadAsignar.querySelector("tfoot tr");

    // Eliminar columnas dinámicas previas (conservar solo la primera columna fija)
    while (thead.children.length > 1) thead.removeChild(thead.lastChild);
    Array.from(tbody.rows).forEach(row => {
        while (row.cells.length > 1) row.deleteCell(-1);
    });
    while (tfoot.children.length > 1) tfoot.removeChild(tfoot.lastChild);

    // Agregar columnas de presupuestos en el thead
    detallesPresupuesto.forEach(det => {
	    const th = document.createElement("th");
	    th.className = "text-center";
	    th.innerHTML = `${det.nombre}<br>
	        <button type="button" class="btn btn-sm btn-outline-primary ms-2 seleccionar-todos" 
	            data-nombre="${det.nombre}" data-marcar="1" title="Marcar todos los ${det.nombre}">
	            ✓ Todos
	        </button>`;

	    // Asignar evento al botón
	    const btn = th.querySelector('.seleccionar-todos');
	    btn.addEventListener('click', () => marcarTodosCheckboxes(btn));

	    thead.appendChild(th);
	});

    // Columna de total
    const thTotal = document.createElement("th");
    thTotal.setAttribute("colspan", "2");
    thTotal.textContent = "Total a Pagar";
    thead.appendChild(thTotal);

    // Agregar celdas a cada fila del tbody
    Array.from(tbody.rows).forEach(fila => {
        detallesPresupuesto.forEach(det => {
            const td = document.createElement("td");
            td.className = "text-center";
            const chk = document.createElement("input");
            chk.type = "checkbox";
            chk.className = "form-check-input border-primary";
            chk.setAttribute("detalle_monto", det.monto);
            chk.setAttribute("id_presupuestos_asociados", det.id_presupuestos_asociados);
            chk.addEventListener("change", (e) => manejarCheckbox(e, fila, tfoot));
            td.appendChild(chk);
            fila.appendChild(td);
        });

        // Celdas de total para la fila
        const tdTotal = document.createElement("td");
        tdTotal.className = "text-end pe-0";
        tdTotal.textContent = "0";
        fila.appendChild(tdTotal);
        const tdMoneda = document.createElement("td");
        tdMoneda.textContent = "Bs.";
        fila.appendChild(tdMoneda);
    });

    // Agregar celdas de total en el footer
	const tdVacio = document.createElement('td');
	tdVacio.colSpan = detallesPresupuesto.length; // número de columnas de checkboxes
	tdVacio.textContent = ''; // vacío
	tfoot.appendChild(tdVacio);

	const tdTotal = document.createElement('td');
	tdTotal.className = 'text-end pe-0';
	tdTotal.textContent = '0';
	tfoot.appendChild(tdTotal);
	tfoot.appendChild(document.createElement('td')).textContent = 'Bs.';

    // Marcar checkboxes según datos preexistentes (ej. gas)
    marcarCheckboxesSegunDatos('Servicio de Gas', 'gas');
}

function manejarCheckbox(e, fila, tfoot) {
    const chk = e.target;
    const montoDetalle = parseFloat(chk.getAttribute("detalle_monto"));
    const participacion = parseFloat(fila.dataset.participacion) || 0;
    const montoApartamento = (participacion * montoDetalle) / 100;

    const celdaTotalFila = fila.lastElementChild.previousElementSibling;
    const celdaTotalFooter = tfoot.lastElementChild.previousElementSibling;

    if (chk.checked) {
        celdaTotalFila.textContent = (parseFloat(celdaTotalFila.textContent) + montoApartamento).toFixed(2);
        celdaTotalFooter.textContent = (parseFloat(celdaTotalFooter.textContent) + montoApartamento).toFixed(2);
    } else {
        celdaTotalFila.textContent = Math.max(0, parseFloat(celdaTotalFila.textContent) - montoApartamento).toFixed(2);
        celdaTotalFooter.textContent = Math.max(0, parseFloat(celdaTotalFooter.textContent) - montoApartamento).toFixed(2);
    }
}

function mostrarVistaPrevia(fila, fecha) {
    // 1. Mostrar el modal ANTES de crear la tabla
    modalApartamentos.show();

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "APARTAMENTO", field: "nro_apartamento", formatter: (cell) => `Apartamento Nº ${cell.getValue()}`, minWidth: 150, responsive: 0 },
        { title: "PROPIETARIO", field: "nombre", formatter: (cell) => `${cell.getValue()} ${cell.getData().apellido}`, minWidth: 150 },
        { 
            title: "MONTO A PAGAR", 
            field: "monto", 
            formatter: (cell) => `${parseFloat(cell.getValue()).toFixed(2)} Bs. / ${(cell.getValue() / cell.getData().tasa_dolar).toFixed(2)} $`, 
            minWidth: 180 
        },
        { 
            title: "ESTATUS / DEUDA", 
            field: "pagado", 
            formatter: (cell) => {
                let row = cell.getData();
                let deuda = row.monto - row.pagado;
                if (deuda <= 0) return '<span class="text-success fw-bold">Deuda Cancelada</span>';
                return `<span class="text-danger">${deuda.toFixed(2)} Bs. / ${(deuda / row.tasa_dolar).toFixed(2)} $</span>`;
            }, 
            minWidth: 180 
        }
    ];

    // 2. Retrasar Tabulator ligeramente para que el DOM mida bien el ancho
    setTimeout(() => {
        tablaApartamentos = Utilidades.cargarTabulador("mensualidades_apartamentos", "", columnas, {
            parametrosExtra: { operacion: "consultar_mensualidades_apartamentos", fecha: fecha },
            cssClass: "tabla-vista-previa", // Aplica la cabecera blanca
            paginaSize: 10
        });
    }, 200);
}

async function prepararModificarcion(fila, fecha, ids, idsApartamentos) {
    // ===== Manejo del select de fecha =====
    const select = selectMesAsignar;
    // Buscar si ya existe una opción con esa fecha
    let opcionExistente = Array.from(select.options).find(opt => opt.id === fecha);
    if (!opcionExistente) {
        // Crear nueva opción
        const nuevaOpcion = document.createElement('option');
        nuevaOpcion.id = fecha;
        nuevaOpcion.setAttribute('data-temporal', 'true'); // Marcarla como temporal
        // Formatear el texto (ej. "ENERO DEL 2025")
        const partes = fecha.split('-');
        const mes = parseInt(partes[1], 10);
        const anio = partes[0];
        const fechaObj = new Date(anio, mes - 1, 1);
        const texto = fechaObj.toLocaleString("es-ES", { month: 'long', year: 'numeric' }).toUpperCase();
        nuevaOpcion.textContent = texto;
        select.appendChild(nuevaOpcion);
        opcionExistente = nuevaOpcion;
    }
    // Seleccionar la opción y deshabilitar el select
    select.value = '';
    opcionExistente.selected = true;
    select.disabled = true;

    // ===== Cargar tabla de presupuestos =====
    tablaMensualidadAsignar.innerHTML = tablaAsignarInicial;
    await cargarTablaPresupuestos(fecha);

    // ===== Marcar checkboxes =====
    const idsArray = ids.split(',');
    const idsAptArray = idsApartamentos.split(',');
    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_asociados");
    formData.append("ids_mensualidades", ids);

    // [MEJORA] Pasamos 'true' para bloquear la pantalla con el spinner mientras procesa
    const respuesta = await Utilidades.query(formData, true); 
    
    if (respuesta.estatus && respuesta.datos) {
        const presupuestosPorMensualidad = respuesta.datos;
        const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
        
        filas.forEach((filaTr, idx) => {
            const idMensualidad = idsArray[idx];
            const presupuestos = presupuestosPorMensualidad[idx] || [];
            
            presupuestos.forEach(p => {
                const chk = filaTr.querySelector(`input[type="checkbox"][id_presupuestos_asociados*="${p}"]`);
                if (chk && !chk.checked) {
                    chk.checked = true;
                    chk.dispatchEvent(new Event('change'));
                }
            });
            
            // [MEJORA] Agregamos '?.' (Optional Chaining) para prevenir errores si hay un colapso en el DOM
            const celdaTotal = filaTr.lastElementChild?.previousElementSibling;
            if (celdaTotal) {
                celdaTotal.dataset.idMensualidad = idMensualidad;
            }
        });
    }

    // ===== Cargar valores de porcentaje y límite =====
    document.getElementById("porcentaje_demora").value = fila.dataset.intereses || '';
    document.getElementById("dia_limite").value = fila.dataset.limite || '';

    // ===== Configurar botón =====
    botonFormulario.textContent = "Guardar Cambios";
    botonFormulario.dataset.op = "modificar";
    document.getElementById('titulo_modal').textContent = "Modificar Mensualidad";
    botonFormulario.dataset.fecha = fecha;
    
    // [MEJORA] La línea que faltaba: Abrir el modal automáticamente al terminar
    modalMensualidad.show();
}

// ============================================================
// REGISTRO Y modificarCIÓN MASIVA
// ============================================================

function recolectarDatosTabla() {
    const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
    const datos = [];

    filas.forEach(fila => {
        const monto = parseFloat(fila.lastElementChild.previousElementSibling.textContent);
        if (monto <= 0) return; // No registrar si no tiene monto

        const idMensualidad = fila.lastElementChild.previousElementSibling.dataset.idMensualidad || '';
        const idPresupuestos = [];
        fila.querySelectorAll('input[type="checkbox"]:checked').forEach(chk => {
            const attr = chk.getAttribute("id_presupuestos_asociados");
            if (attr && attr.trim() !== "") {
                const ids = attr.split(',');
                ids.forEach(id => idPresupuestos.push(parseInt(id)));
            }
        });

        datos.push({
            id_apartamento: fila.id,
            id_mensualidad: idMensualidad,
            monto: monto,
            id_presupuestos: idPresupuestos
        });
    });

    return datos;
}

async function registrarMensualidad() {
    const datos = recolectarDatosTabla();
    if (datos.length === 0) {
        Utilidades.mensaje('warning', 'Atención', 'No hay mensualidades con montos para registrar');
        return;
    }

    const mes = parseInt(selectMesAsignar.selectedOptions[0].id.split('-')[1]);
    const anio = parseInt(selectMesAsignar.selectedOptions[0].id.split('-')[0]);

    const formData = new FormData();
    formData.append("operacion", "registrar_masivo");
    formData.append("mes", mes);
    formData.append("anio", anio);
    formData.append("tasa_dolar", tasaDolar);
    formData.append("porcentaje_interes", document.getElementById("porcentaje_demora").value);
    formData.append("limite_mensualidad", document.getElementById("dia_limite").value);
    formData.append("datos_apartamentos", JSON.stringify(datos));

    const respuesta = await Utilidades.query(formData, true);
    if (respuesta.estatus) {
        modalMensualidad.hide();
        tablaMensualidades.replaceData();
        verificarMeses();
        Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
    } else {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
    }
}

async function modificarMensualidad() {
    const datos = recolectarDatosTabla();
    if (datos.length === 0) {
        Utilidades.mensaje('warning', 'Atención', 'No hay mensualidades con montos para modificar');
        return;
    }

    const fecha = botonFormulario.dataset.fecha;
    const partes = fecha.split('-');
    const mes = parseInt(partes[1]);
    const anio = parseInt(partes[0]);

    const formData = new FormData();
    formData.append("operacion", "modificar_masivo");
    formData.append("mes", mes);
    formData.append("anio", anio);
    formData.append("tasa_dolar", tasaDolar);
    formData.append("porcentaje_interes", document.getElementById("porcentaje_demora").value);
    formData.append("limite_mensualidad", document.getElementById("dia_limite").value);
    formData.append("datos_apartamentos", JSON.stringify(datos));

    const respuesta = await Utilidades.query(formData, true);
    if (respuesta.estatus) {
        modalMensualidad.hide();
        tablaMensualidades.replaceData();
        Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
    } else {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
    }
}

// ============================================================
// ELIMINACIÓN
// ============================================================
function confirmarEliminar(fecha) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Se eliminarán todas las mensualidades de este período.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(result => {
        if (result.isConfirmed) eliminarMensualidad(fecha);
    });
}

async function eliminarMensualidad(fecha) {
    const formData = new FormData();
    formData.append("operacion", "eliminar_mensualidad");
    formData.append("fecha", fecha);

    const respuesta = await Utilidades.query(formData);
    if (respuesta.estatus) {
        tablaMensualidades.replaceData();
        verificarMeses();
        Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
    } else {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
    }
}

// ============================================================
// FUNCIONES AUXILIARES
// ============================================================

function resetModalMensualidad() {
    tablaMensualidadAsignar.innerHTML = tablaAsignarInicial;

    // Limpiar opciones temporales del select
    const select = selectMesAsignar;
    Array.from(select.options).forEach(opt => {
        if (opt.hasAttribute('data-temporal')) {
            opt.remove();
        }
    });
    select.disabled = false;
    select.value = "";
    selectMesAsignar.parentElement?.removeAttribute("hidden");

    botonFormulario.textContent = "Guardar";
    botonFormulario.dataset.op = "Registrar";
    document.getElementById('titulo_modal').textContent = "Registrar Mensualidad";
}

function marcarCheckboxesSegunDatos(nombreTh, datasetKey) {
    const headers = tablaMensualidadAsignar.querySelectorAll('thead th');
    let colIndex = -1;
    headers.forEach((th, idx) => {
        if (th.textContent.includes(nombreTh)) colIndex = idx;
    });
    if (colIndex === -1) return;

    const filas = tablaMensualidadAsignar.querySelectorAll('tbody tr');
    filas.forEach(fila => {
        const valor = fila.dataset[datasetKey];
        if (valor === '1') {
            const checkbox = fila.children[colIndex]?.querySelector('input[type="checkbox"]');
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        }
    });
}

/**
 * Marca o desmarca todos los checkboxes de una columna y actualiza totales.
 * @param {HTMLElement} boton - El botón que disparó el evento.
 */
function marcarTodosCheckboxes(boton) {
    const th = boton.closest('th');
    if (!th) return;
    const colIndex = Array.from(th.parentNode.children).indexOf(th);
    const marcar = boton.dataset.marcar === '1'; // true para marcar, false para desmarcar

    const filas = tablaMensualidadAsignar.querySelectorAll('tbody tr');
    const tfoot = tablaMensualidadAsignar.querySelector('tfoot tr');
    const totalFooter = tfoot.lastElementChild.previousElementSibling;

    filas.forEach(fila => {
        const celda = fila.children[colIndex];
        if (!celda) return;
        const chk = celda.querySelector('input[type="checkbox"]');
        if (!chk) return;

        const estabaChecked = chk.checked;
        if (marcar && !estabaChecked) {
            chk.checked = true;
            // Disparar evento change para actualizar totales (reutiliza la función existente)
            chk.dispatchEvent(new Event('change'));
        } else if (!marcar && estabaChecked) {
            chk.checked = false;
            chk.dispatchEvent(new Event('change'));
        }
    });

    // Cambiar estado del botón
    boton.dataset.marcar = marcar ? '0' : '1';
    boton.classList.toggle('btn-outline-primary', !marcar);
    boton.classList.toggle('btn-outline-danger', marcar);
    boton.innerHTML = marcar ? '✕ Quitar' : '✓ Todos';
    boton.title = marcar ? 'Desmarcar todos los checkboxes' : 'Marcar todos los checkboxes';
}

async function validarFormulario() {
    // Usar Validaciones para fecha, porcentaje, límite, y al menos un checkbox marcado
    // Por simplicidad, retornamos true; se implementará en mensualidad_validar.js
    return true;
}

// Función para resaltar fila desde notificación
function seleccionarMensualidadPorNotificacion() {
    const urlParams = new URLSearchParams(window.location.search);
    const idMensualidad = urlParams.get('buscar');
    if (!idMensualidad) return;

    const interval = setInterval(() => {
        if (tablaMensualidades && tablaMensualidades.rows().count() > 0) {
            clearInterval(interval);
            tablaMensualidades.rows().every(function() {
                const row = this.node();
                const ids = row.querySelector('.modificar')?.dataset.ids;
                if (ids && ids.includes(idMensualidad)) {
                    $(row).addClass('table-primary highlight-row');
                    $('html, body').animate({ scrollTop: $(row).offset().top - 100 }, 1000);
                    return false;
                }
            });
        }
    }, 100);
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - MENSUALIDADES
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // 1. CONFIGURACIÓN DE LA VISTA PRINCIPAL
    const configPrincipal = {
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
                alinearBurbuja();
            }
        },

        steps: [
            { element: '.page-header', popover: { title: 'Módulo de Mensualidades', description: 'Bienvenido. Aquí podrás generar los cobros mensuales del condominio basados en los presupuestos vigentes.', side: "bottom", align: 'center' } },
            // Seleccionamos el contenedor del botón por si está oculto temporalmente
            { element: document.querySelector('#boton_registrar')?.parentElement || '#boton_registrar', popover: { title: 'Generar Mensualidad', description: 'Si hay meses con presupuestos listos, este botón te permitirá generar la mensualidad y distribuirla a los apartamentos.', side: "bottom", align: 'start' } },
            { element: '#tabla_mensualidad_wrapper', popover: { title: 'Historial de Cobros', description: 'Aquí verás las mensualidades ya generadas, lo que se ha recaudado y lo que falta por pagar. Puedes ver detalles o editar.', side: "top", align: 'center' } }
        ]
    };

    // 2. CONFIGURACIÓN DEL MODAL DE MENSUALIDAD
    const configModalMensualidad = {
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
                alinearBurbuja();
            }
        },

        steps: [
            { element: '#mes_select_asignar', popover: { title: 'Selección de Mes', description: 'Primero, elige de la lista el mes que deseas procesar para cobrar.', side: 'bottom', align: 'start' } },
            { element: '#tabla_mensualidad_asignar', popover: { title: 'Distribución de Presupuestos', description: 'Aquí se listan los apartamentos. Selecciona mediante las casillas qué presupuestos o gastos se le cobrarán a cada uno.', side: 'top', align: 'center' } },
            { element: '#porcentaje_demora', popover: { title: 'Recargos', description: 'Establece de cuánto será el porcentaje de multa si un propietario se atrasa en el pago.', side: 'top', align: 'start' } },
            { element: '#dia_limite', popover: { title: 'Fecha de Corte', description: 'Indica hasta qué día del mes tienen los propietarios para pagar sin recibir el recargo por mora.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Verifica la asignación y haz clic aquí para registrar formalmente la mensualidad.', side: 'top', align: 'center' } }
        ]
    };

    // 3. LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalMensualidadHTML = document.getElementById('modal_mensualidad');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalMensualidadHTML && modalMensualidadHTML.classList.contains('show')) {
                tourActivo = driver(configModalMensualidad);
                tourActivo.drive();
            } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver(configPrincipal);
                tourActivo.drive();
            }
        });
    }

    if (modalMensualidadHTML) {
        modalMensualidadHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});