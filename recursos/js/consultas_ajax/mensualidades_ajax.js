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

let modalMensualidad = new bootstrap.Modal(document.getElementById("modal_mensualidad"));
let modalApartamentos = new bootstrap.Modal(document.getElementById("modal_mensualidades_apartamentos"));
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

    document.getElementById('header-toggle')?.addEventListener("click", () => {
        setTimeout(() => tablaMensualidades?.columns.adjust().draw(), 450);
    });

    document.getElementById("modal_mensualidad")?.addEventListener("hidden.bs.modal", resetModalMensualidad);
    document.getElementById("modal_mensualidades_apartamentos")?.addEventListener("shown.bs.modal", () => {
        if ($.fn.DataTable.isDataTable("#mensualidades_apartamentos")) {
            $('#mensualidades_apartamentos').DataTable().columns.adjust().draw();
        }
    });

    // Delegación de eventos en la tabla principal
    document.querySelector("#tabla_mensualidad tbody")?.addEventListener("click", manejarClickEnTabla);
});

// ============================================================
// CONSULTAS PRINCIPALES
// ============================================================

/**
 * Consulta la lista de mensualidades agrupadas por mes
 */
async function consultarMensualidades() {
    const columnas = [
        {
            data: null,
            render: row => {
                let fecha = new Date(`${row.mes}/01/${row.anio}`);
                return `${fecha.toLocaleString("es-ES", { month: 'long' })} del ${row.anio}`.toUpperCase();
            }
        },
        {
            data: null,
            render: row => `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / row.tasa_dolar).toFixed(2)} $`
        },
        {
            data: null,
            render: row => {
                let deuda = row.monto - row.pagado;
                if (deuda < 0) return 'Deuda Cancelada';
                return `${deuda.toFixed(2)} Bs. / ${(deuda / row.tasa_dolar).toFixed(2)} $`;
            }
        },
        {
            data: null,
            render: row => crearBotones(row.ids, row.ids_apartamentos).innerHTML
        }
    ];

    const parametros = (data) => {
        data.operacion = 'consultar_mensualidades_mes';
    };

    const postCreacion = (row, data) => {
        row.id = `fila-01/${data.mes}/${data.anio}`;
        row.setAttribute("data-mes", data.mes);
        row.setAttribute("data-anio", data.anio);
        row.setAttribute("data-intereses", data.porcentaje_interes);
        row.setAttribute("data-limite", data.limite_mensualidad);

        row.lastElementChild.setAttribute('class','row');
    };

    tablaMensualidades = Utilidades.crearDataTable('tabla_mensualidad', columnas, parametros, postCreacion);
    setTimeout(seleccionarMensualidadPorNotificacion, 500);
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
        option.id = FormatoFechas.cambiarFormatoFecha(fecha.toLocaleDateString('es-ES'));
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

// ============================================================
// CREACIÓN DE BOTONES EN TABLA PRINCIPAL
// ============================================================
function crearBotones(idsMensualidades, idsApartamentos) {
    let div = document.createElement("div");
    div.className = "row justify-content-evenly";

    let html = `
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-primary vista-previa" title="Ver detalles" 
                data-bs-toggle="modal" data-bs-target="#modal_mensualidades_apartamentos"
                data-ids="${idsMensualidades}" data-fecha="">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
        <form class="col-lg-3 col-6 mt-2 mt-lg-0" action="?pagina=reportes_controlador.php&accion=cuadro_pagos" method="POST">
            <input type="hidden" name="select_reporte" value="">
            <button type="submit" class="btn btn-outline-light cuadro-pagos" style="background-color:#3939a9;">
                <i class="bi bi-card-checklist"></i>
            </button>
        </form>
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-success modificar" title="modificar" data-bs-toggle="modal" data-bs-target="#modal_mensualidad"
                data-ids="${idsMensualidades}" data-apartamentos="${idsApartamentos}">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>`;

    if (permisoEliminar == 1) {
        html += `
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-danger eliminar" title="Eliminar" data-fecha="">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    }

    div.innerHTML = html;
    return div;
}

// ============================================================
// MANEJO DE EVENTOS EN TABLA PRINCIPAL (DELEGACIÓN)
// ============================================================
function manejarClickEnTabla(e) {
    const btn = e.target.closest('button');
    if (!btn) return;

    const fila = btn.closest('tr');
    if (!fila) return;

    const mes = fila.dataset.mes.padStart(2, '0');;
    const anio = fila.dataset.anio;
    // const fecha = `01/${mes}/${anio}`;
    const fecha = `${anio}-${mes}-01`;

    if (btn.classList.contains('vista-previa')) {
        mostrarVistaPrevia(fila, fecha);
    } else if (btn.classList.contains('modificar')) {
        prepararEdicion(fila, fecha, btn.dataset.ids, btn.dataset.apartamentos);
    } else if (btn.classList.contains('eliminar')) {
        confirmarEliminar(fecha);
    } else if (btn.classList.contains('cuadro-pagos')) {
        e.preventDefault();
        const inputHidden = btn.closest('form')?.querySelector('input[name="select_reporte"]');
        if (inputHidden) inputHidden.value = fecha.replace(/\//g, '-');
        btn.closest('form')?.submit();
    }
}

async function mostrarVistaPrevia(fila, fecha) {
    const formData = new FormData();
    formData.append("operacion", "consultar_mensualidades_apartamentos");
    formData.append("fecha", fecha);

    const respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    if ($.fn.DataTable.isDataTable('#mensualidades_apartamentos')) {
        $('#mensualidades_apartamentos').DataTable().clear().destroy();
    }

    const columnas = [
        { data: null, render: row => `Apartamento Nº ${row.nro_apartamento}` },
        { data: null, render: row => `${row.nombre} ${row.apellido}` },
        {
            data: null,
            render: row => `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / row.tasa_dolar).toFixed(2)} $`
        },
        {
            data: null,
            render: row => {
                let deuda = row.monto - row.pagado;
                if (deuda < 0) return 'Deuda Cancelada';
                return `${deuda.toFixed(2)} Bs. / ${(deuda / row.tasa_dolar).toFixed(2)} $`;
            }
        }
    ];

    tablaApartamentos = new DataTable('#mensualidades_apartamentos', {
        data: respuesta.datos,
        columns: columnas,
        destroy: true,
        responsive: true,
        language: { url: 'recursos/bootstrap/js/datatable-plugin-es.js' }
    });

    modalApartamentos.show();
}

async function prepararEdicion(fila, fecha, ids, idsApartamentos) {
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

    const respuesta = await Utilidades.query(formData);
    if (respuesta.estatus && respuesta.datos) {
        const presupuestosPorMensualidad = respuesta.datos;
        const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
        filas.forEach((fila, idx) => {
            const idMensualidad = idsArray[idx];
            const presupuestos = presupuestosPorMensualidad[idx] || [];
            presupuestos.forEach(p => {
                const chk = fila.querySelector(`input[type="checkbox"][id_presupuestos_asociados*="${p}"]`);
                if (chk && !chk.checked) {
                    chk.checked = true;
                    chk.dispatchEvent(new Event('change'));
                }
            });
            fila.lastElementChild.previousElementSibling.dataset.idMensualidad = idMensualidad;
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
}

// ============================================================
// REGISTRO Y EDICIÓN MASIVA
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
        tablaMensualidades.ajax.reload();
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
        tablaMensualidades.ajax.reload();
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
        tablaMensualidades.ajax.reload();
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