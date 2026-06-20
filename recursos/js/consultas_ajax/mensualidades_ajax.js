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
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;
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
});

// CONSULTAS PRINCIPALES
async function consultarMensualidades() {
    
    // Formato Período 
    const formatoMes = (cell) => {
        const row = cell.getData();
        let fecha = new Date(row.anio, row.mes - 1, 1);
        let textoFecha = `${fecha.toLocaleString("es-ES", { month: 'long' })} del ${row.anio}`.toUpperCase();
        
        return `<div class="fw-bold">
                    <i class="bi bi-calendar2-month text-primary me-2 opacity-75 fs-5"></i> 
                    ${textoFecha}
                </div>`;
    };
    
    // Estado Dinámico (Calculado en tiempo real)
    const formatoEstado = (cell) => {
        const row = cell.getData();
        let deuda = row.monto - row.pagado;
        
        // Si ya la deuda es cero o menor
        if (deuda <= 0) {
            return ComponentesUI.crearSoftBadge('success', 'bi-check-circle-fill', 'Completada');
        }

        // Si hay deuda, verificamos si pasó la fecha límite
        let hoy = new Date();
        let diaLimite = row.limite_mensualidad || 31;
        let fechaLim = new Date(row.anio, row.mes - 1, diaLimite, 23, 59, 59);

        if (hoy > fechaLim) {
            return ComponentesUI.crearSoftBadge('danger', 'bi-exclamation-octagon-fill', 'En Mora');
        } else {
            return ComponentesUI.crearSoftBadge('primary', 'bi-clock-fill', 'Vigente');
        }
    };

    // Formato Deuda
    const formatoDeuda = (cell) => {
        const row = cell.getData();
        let deuda = row.monto - row.pagado;
        
        if (deuda <= 0) {
            return `<span class="text-success fw-bold"><i class="bi bi-check2-all me-1"></i> Sin Deuda</span>`;
        }
        
        // Mostramos Bs grande y $ pequeño abajo para no alargar la tabla
        return `<div class="d-flex flex-column justify-content-center">
                    <span class="text-danger fw-bold">${deuda.toFixed(2)} Bs.</span>
                    <span class="text-muted" style="font-size: 0.8rem;">Ref: ${(deuda / row.tasa_dolar).toFixed(2)} $</span>
                </div>`;
    };

    // Formato Botones 
    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Detalles">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar Mensualidad">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Eliminar Mensualidad">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    // Configuración de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center" },
        { title: "Período", field: "anio", formatter: formatoMes, minWidth: 200, responsive: 0 },
        { title: "Estado", formatter: formatoEstado, minWidth: 140, hozAlign: "center", headerHozAlign: "center" }, // NUEVA COLUMNA DINÁMICA
        { title: "Pendiente", field: "pagado", formatter: formatoDeuda, minWidth: 140 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, responsive: 0, 
            download: false, headerHozAlign: "center", widthGrow: 2,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                const row = cell.getData();
                const mes = String(row.mes).padStart(2, '0');
                const fecha = `${row.anio}-${mes}-01`;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(row, fecha);
                else if (btn.classList.contains('eliminar')) confirmarEliminar(fecha);
                else if (btn.classList.contains('modificar')) {
                    const mockFila = { dataset: { intereses: row.porcentaje_interes, limite: row.limite_mensualidad } };
                    prepararFormulario(mockFila, fecha, row.ids, row.ids_apartamentos);
                } 
            }
        }
    ];

    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultarPorMeses' },
        columnaBusqueda: 'ids'
    };

    tablaMensualidades = Tablas.cargarTabulador("tabla_mensualidad", "", columnas, opcionesExtra);

    const filtroEspecialMensualidades = (data, valorBuscado) => {
        let fechaObj = new Date(data.anio, data.mes - 1, 1);
        let textoPeriodo = `${fechaObj.toLocaleString("es-ES", { month: 'long' })} del ${data.anio}`.toLowerCase();
        
        let montoTotal = parseFloat(data.monto).toFixed(2);
        let deuda = data.monto - data.pagado;
        let textoEstado = deuda <= 0 ? "completada" : (new Date() > new Date(data.anio, data.mes - 1, data.limite_mensualidad || 31, 23, 59, 59) ? "en mora" : "vigente");
        
        return textoPeriodo.includes(valorBuscado) || 
               String(data.anio).includes(valorBuscado) || 
               montoTotal.includes(valorBuscado) || 
               textoEstado.includes(valorBuscado);
    };

    Tablas.inicializarBuscadorGlobal(tablaMensualidades, "busqueda_global", columnas, filtroEspecialMensualidades);
}

/**
 * Verifica qué meses tienen presupuesto pero no mensualidad
 */
async function verificarMeses() {
    const formData = new FormData();
	formData.append('operacion', 'verificar_meses');
	const respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {    
        const meses = respuestaServidor.datos || [];
        if (meses.length === 0) {
            return;
        }

        document.getElementById("boton_registrar")?.closest("[hidden]")?.removeAttribute("hidden");
        const span = document.getElementById("boton_registrar")?.nextElementSibling;
        if (span) span.textContent = `*Hay ${meses.length} mes${meses.length > 1?"es":""} pendiente${meses.length > 1?"s":""}.`;

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
    });
}

/**
 * Carga la tabla de asignación de presupuestos por apartamento
 */
async function cargarTablaPresupuestos(fecha) {
    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_mensualidades");
    formData.append("fecha", fecha);

    const respuesta = await Peticiones.enviar(formData);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return false;
    }

    const detallesPresupuesto = respuesta.datos || [];
    if (detallesPresupuesto.length === 0) {
        return false;
    }

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

    return true;
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

function mostrarVistaPrevia(data, fecha) {
    const nombreMes = FormatoFechas.nombreMes(parseInt(data.mes)) || data.mes;
    document.getElementById("vp_periodo").textContent = `${nombreMes} ${data.anio}`.toUpperCase();

    const montoUsd = parseFloat(data.monto) || 0;
    const tasa = parseFloat(data.tasa_dolar) || 1;
    const montoBs = montoUsd * tasa;

    document.getElementById("vp_monto_base_usd").textContent = `Ref: ${montoUsd.toFixed(2)} $`;
    document.getElementById("vp_monto_base_bs").textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_tasa").textContent = `${tasa.toFixed(2)} Bs/$`;

    document.getElementById("vp_recargo").innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${data.porcentaje_interes}%`;
    document.getElementById("vp_limite").textContent = `Día ${data.limite_mensualidad}`;

    const btnReporte = document.getElementById("btn_generar_reporte_modal");
    // Removemos event listeners anteriores clonando el botón para evitar que se acumulen clics
    const nuevoBtnReporte = btnReporte.cloneNode(true);
    btnReporte.parentNode.replaceChild(nuevoBtnReporte, btnReporte);
    // nuevoBtnReporte.setAttribute("title",");
    Tooltips.actualizarDinamicamente(nuevoBtnReporte,"Descargar Reporte de Pagos");
    
    nuevoBtnReporte.addEventListener("click", () => {
        let mesFormateado = String(data.mes).padStart(2, '0');
        let form = document.createElement('form');
        form.action = "?pagina=reportes&accion=cuadro_pagos";
        form.method = "POST";
        form.target = "_blank";
        
        let input = document.createElement('input');
        input.type = "hidden";
        input.name = "select_reporte";
        input.value = `${mesFormateado}-${data.anio}`;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });

    modalApartamentos.show();

    const formatoPropietario = (cell) => {
        const nombre = cell.getValue();
        const apellido = cell.getData().apellido || "";

        // Si el backend retornó "Sin Propietario" o viene vacío
        if (!nombre || nombre === "Sin Propietario") {
            // Pasamos nuestra clase personalizada 'soft-neutral' (o puedes usar 'secondary' si el helper lo mapea directo)
            // Usamos el icono 'bi-person-dash' o 'bi-person-x' para denotar la ausencia
            return ComponentesUI.crearSoftBadge("secondary", "bi-person-dash", "Sin Propietario");
        }

        // Si existe el propietario, mostramos el formato estándar capitalizado
        const nombreCompleto = `${nombre} ${apellido}`.trim();
        return `<div class="fw-semibold text-capitalize">${nombreCompleto.toLowerCase()}</div>`;
    }

    const formatoEstatus = (cell) =>{
        let row = cell.getData();
        let deuda = row.monto - row.pagado;
        
        if (deuda <= 0) {
            const badgeSolvente = ComponentesUI.crearSoftBadge('success', 'bi-check2-all', 'Solvente');
            
            return `<div class="d-flex align-items-start">
                        ${badgeSolvente}
                    </div>`;
        }
        
        const badgeDeuda = ComponentesUI.crearSoftBadge('danger', 'bi-exclamation-circle', 'Deuda');
        
        return `<div class="d-flex flex-column align-items-start justify-content-center">
                    <div class="mb-1">${badgeDeuda}</div>
                    <span class="fw-bold text-danger" style="font-size: 0.85rem;">${deuda.toFixed(2)} Bs.</span>
                </div>`;
    }

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Apartamento", field: "nro_apartamento", formatter: (cell) => `<div class="fw-bold"><i class="bi bi-door-closed text-primary me-2 opacity-75"></i>Apt. ${cell.getValue()}</div>`, minWidth: 150, responsive: 0 },
        { title: "Propietario", field: "nombre", formatter: formatoPropietario, minWidth: 150 },
        { 
            title: "Monto A Pagar", 
            field: "monto", 
            formatter: (cell) => `<span class="fw-semibold">${parseFloat(cell.getValue()).toFixed(2)} Bs.</span> <span class="text-muted small">/ ${(cell.getValue() / cell.getData().tasa_dolar).toFixed(2)} $</span>`, 
            minWidth: 180 
        },
        { 
            title: "Estatus / Deuda", 
            field: "pagado", 
            hozAlign: "center", headerHozAlign: "center",vertAlign:"middle",
            formatter: formatoEstatus, 
            minWidth: 160 
        }
    ];

    setTimeout(() => {
        tablaApartamentos = Tablas.cargarTabulador("mensualidades_apartamentos", "", columnas, {
            parametrosExtra: { operacion: "consultar_mensualidad_apartamentos", fecha: fecha },
            cssClass: "tabla-vista-previa",
            paginaSize: 10
        });
    }, 200);
}

async function prepararFormulario(fila, fecha, ids, idsApartamentos) {
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
    
    // Guardamos el resultado (true o false) de la carga
    const exitoCarga = await cargarTablaPresupuestos(fecha);

    // Si no se cargó (porque el presupuesto fue eliminado), detenemos todo
    if (!exitoCarga) {
        Alertas.mostrar('error', 'Error de Integridad', 'No se puede modificar esta mensualidad porque el presupuesto base de este mes fue eliminado. Debe registrar un presupuesto para este mes o eliminar esta mensualidad.');
        resetModalMensualidad(); // Limpiamos el modal por si acaso
        return; // Abortamos la ejecución, el modal no se abrirá
    }

    // ===== Marcar checkboxes =====
    const idsArray = ids.split(',');
    const idsAptArray = idsApartamentos.split(',');
    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_asociados");
    formData.append("ids_mensualidades", ids);

    // Pasamos 'true' para bloquear la pantalla con el spinner mientras procesa
    const respuesta = await Peticiones.enviar(formData, "", true); 
    
    if (respuesta.estatus && respuesta.datos) {
        const presupuestosPorMensualidad = respuesta.datos;
        const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
        
        filas.forEach((filaTr) => {
            // Obtenemos el ID del apartamento directamente de la fila
            const idApartamentoFila = filaTr.id; 
            
            //  Buscamos en qué posición del arreglo de datos se encuentra este apartamento
            const indiceReal = idsAptArray.indexOf(idApartamentoFila);
            
            let idMensualidad = '';
            let presupuestos = [];
            
            // Si el apartamento YA tenía una mensualidad (el índice existe), le asignamos sus datos
            if (indiceReal !== -1) {
                idMensualidad = idsArray[indiceReal];
                presupuestos = presupuestosPorMensualidad[indiceReal] || [];
            }
            
            // Marcamos los presupuestos asociados
            presupuestos.forEach(p => {
                const chk = filaTr.querySelector(`input[type="checkbox"][id_presupuestos_asociados*="${p}"]`);
                if (chk && !chk.checked) {
                    chk.checked = true;
                    chk.dispatchEvent(new Event('change'));
                }
            });
            
            // Asignamos el ID de la mensualidad a la celda (Si es un apto nuevo, quedará vacío y PHP hará un INSERT)
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
    // botonFormulario.textContent = "Guardar Cambios";
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
    botonFormulario.dataset.op = "modificar";
    document.getElementById('titulo_modal').textContent = "Modificar Mensualidad";
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-calendar-minus");
    botonFormulario.dataset.fecha = fecha;
    
    // La línea que faltaba: Abrir el modal automáticamente al terminar
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
        Alertas.mostrar('warning', 'Atención', 'No hay mensualidades con montos para registrar');
        return;
    }

    const mes = parseInt(selectMesAsignar.selectedOptions[0].id.split('-')[1]);
    const anio = parseInt(selectMesAsignar.selectedOptions[0].id.split('-')[0]);

    const formData = new FormData();
    formData.append("operacion", "registrar_mensualidad");
    formData.append("mes", mes);
    formData.append("anio", anio);
    formData.append("tasa_dolar", tasaDolar);
    formData.append("porcentaje_interes", document.getElementById("porcentaje_demora").value);
    formData.append("limite_mensualidad", document.getElementById("dia_limite").value);
    formData.append("datos_apartamentos", JSON.stringify(datos));

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modalMensualidad.hide();
        tablaMensualidades.replaceData();
        verificarMeses();
    });
}

async function modificarMensualidad() {
    const datos = recolectarDatosTabla();
    if (datos.length === 0) {
        Alertas.mostrar('warning', 'Atención', 'No hay mensualidades con montos para modificar');
        return;
    }

    const fecha = botonFormulario.dataset.fecha;
    const partes = fecha.split('-');
    const mes = parseInt(partes[1]);
    const anio = parseInt(partes[0]);

    const formData = new FormData();
    formData.append("operacion", "modificar_mensualidad");
    formData.append("mes", mes);
    formData.append("anio", anio);
    formData.append("tasa_dolar", tasaDolar);
    formData.append("porcentaje_interes", document.getElementById("porcentaje_demora").value);
    formData.append("limite_mensualidad", document.getElementById("dia_limite").value);
    formData.append("datos_apartamentos", JSON.stringify(datos));

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modalMensualidad.hide();
        tablaMensualidades.replaceData();
    });
}

// ELIMINACIÓN
function confirmarEliminar(fecha) {
    Alertas.confirmarAccion(
        "¿Eliminar Mensualidad?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(fecha); }
    );
}

async function eliminar(fecha) {
    const formData = new FormData();
    formData.append("operacion", "eliminar_mensualidad");

    const partes = fecha.split('-');
    const mes = parseInt(partes[1]);
    const anio = parseInt(partes[0]);

    formData.append("mes", mes);
    formData.append("anio", anio);

    const respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, () => {
        tablaMensualidades.replaceData();
        verificarMeses();
    });
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

    // botonFormulario.textContent = "Guardar";
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Mensualidad';
    botonFormulario.dataset.op = "Registrar";
    document.getElementById('titulo_modal').textContent = "Registrar Mensualidad";
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-calendar-plus");
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

// MÓDULO DE AYUDA INTERACTIVA
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Módulo de Mensualidades', description: 'Bienvenido. Aquí podrás generar los cobros mensuales del condominio basados en los presupuestos vigentes.', side: "bottom", align: 'center' } },
            // Seleccionamos el contenedor del botón por si está oculto temporalmente
            { element: document.querySelector('#boton_registrar')?.parentElement || '#boton_registrar', popover: { title: 'Generar Mensualidad', description: 'Si hay meses con presupuestos listos, este botón te permitirá generar la mensualidad y distribuirla a los apartamentos.', side: "bottom", align: 'start' } },
            { element: '#tabla_mensualidad', popover: { title: 'Historial de Cobros', description: 'Aquí verás las mensualidades ya generadas, lo que se ha recaudado y lo que falta por pagar. Puedes ver detalles o editar.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#mes_select_asignar', popover: { title: 'Selección de Mes', description: 'Primero, elige de la lista el mes que deseas procesar para cobrar.', side: 'bottom', align: 'start' } },
            { element: '#tabla_mensualidad_asignar', popover: { title: 'Distribución de Presupuestos', description: 'Aquí se listan los apartamentos. Selecciona mediante las casillas qué presupuestos o gastos se le cobrarán a cada uno.', side: 'top', align: 'center' } },
            { element: '#porcentaje_demora', popover: { title: 'Recargos', description: 'Establece de cuánto será el porcentaje de multa si un propietario se atrasa en el pago.', side: 'top', align: 'start' } },
            { element: '#dia_limite', popover: { title: 'Fecha de Corte', description: 'Indica hasta qué día del mes tienen los propietarios para pagar sin recibir el recargo por mora.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Verifica la asignación y haz clic aquí para registrar formalmente la mensualidad.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_mensualidad',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
