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

document.addEventListener('DOMContentLoaded', () => {
    consultarMensualidades();
    verificarMeses();

    selectMesAsignar?.addEventListener("change", (e) => {
        let fecha = e.target.selectedOptions[0]?.id;
        if (fecha) cargarTablaPresupuestos(fecha);
    });

    tablaMensualidadAsignar?.addEventListener("focusin", (e) => {
        if (e.target.classList.contains("input-descuento")) {
            const fila = e.target.closest("tr");
            
            delete fila.dataset.exoneraciones; 

            if (parseFloat(e.target.value) === 0) {
                e.target.value = "";
            }
        }
    });

    tablaMensualidadAsignar?.addEventListener("focusout", (e) => {
        if (e.target.classList.contains("input-descuento")) {
            const fila = e.target.closest("tr");
            const montoBase = parseFloat(fila.dataset.montoBase) || 0;
            let valorIngresado = parseFloat(e.target.value) || 0;

            if (e.target.value.trim() === "") {
                e.target.value = "0.00";
            } else if (valorIngresado > montoBase) {
                e.target.value = montoBase.toFixed(2);
                
                recalcularTotalFila(fila);
                
                // Alerta sutil e intuitiva para el usuario
                Alertas.mostrar('warning', 'Exoneración Ajustada', `El descuento no puede ser mayor al monto base de este apartamento (${montoBase.toFixed(2)} Bs.)`);
            } else {
                e.target.value = valorIngresado.toFixed(2);
            }
        }
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
                else if (btn.classList.contains('eliminar')) confirmarEliminar(row.id_periodo); // Pasa el ID directo
                else if (btn.classList.contains('modificar')) {
                    prepararFormulario(row);
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
/**
 * Verifica qué meses tienen presupuesto pero no mensualidad
 */
async function verificarMeses() {
    const formData = new FormData();
    formData.append('operacion', 'verificar_meses');
    const respuesta = await Peticiones.enviar(formData);
    
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {    
        const meses = respuestaServidor.datos || [];

        const botonRegistrar = document.getElementById("boton_nuevo_registro");
        const avisoPendiente = document.getElementById("aviso_no_mensualidades");
        // Seleccionamos directamente el contenedor flex del botón
        const columnaBoton = document.getElementById("columna_boton_nuevo");

        // ==========================================================
        // CASO A: No hay meses disponibles (Ocultar columna entera)
        // ==========================================================
        if (meses.length === 0) {
            if (columnaBoton) {
                columnaBoton.style.setProperty("display", "none", "important");
            }
            if (botonRegistrar) {
                botonRegistrar.closest("[data-bs-toggle='modal']")?.setAttribute("hidden", "true");
                botonRegistrar.setAttribute("hidden", "true");
            }
            if (avisoPendiente) {
                avisoPendiente.classList.remove("d-none");
                avisoPendiente.style.setProperty("display", "flex", "important");
            }
            return;
        }

        // ==========================================================
        // CASO B: Sí hay meses listos (Restaurar comportamiento flex)
        // ==========================================================
        if (columnaBoton) {
            columnaBoton.style.setProperty("display", "flex", "important");
        }
        if (botonRegistrar) {
            botonRegistrar.closest("[hidden]")?.removeAttribute("hidden");
            botonRegistrar.removeAttribute("hidden");
        }
        if (avisoPendiente) {
            avisoPendiente.classList.add("d-none");
            avisoPendiente.style.display = "none";
        }

        // Mostrar texto de ayuda indicando los meses listos
        const span = columnaBoton?.querySelector(".text-danger") || columnaBoton?.querySelector("p");
        if (span) {
            span.className = "text-success small fw-semibold mt-1 d-block";
            span.textContent = `*Tienes ${meses.length} mes${meses.length > 1 ? "es" : ""} con presupuesto listo para procesar.`;
        }

        // Construir opciones del select interno
        let fragment = document.createDocumentFragment();
        meses.forEach(m => {
            let fecha = new Date(`${m.mes_presupuesto}/01/${m.anio_presupuesto}`);
            let option = document.createElement("option");
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
// Variable global para almacenar el presupuesto del mes seleccionado
window.presupuestosActuales = [];

async function cargarTablaPresupuestos(fecha) {
    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_mensualidades");
    formData.append("fecha", fecha);
    const respuesta = await Peticiones.enviar(formData);

    if (!respuesta.estatus) return false;
    window.presupuestosActuales = respuesta.datos || [];
    if (window.presupuestosActuales.length === 0) return false;

    // Calcular el total bruto de la suma de presupuestos del mes
    let totalBrutoMes = window.presupuestosActuales.reduce((sum, det) => sum + parseFloat(det.monto), 0);

    let sumaFooterBase = 0;
    const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");

    // Recorremos las filas existentes de la vista
    filas.forEach(fila => {
        let participacion = parseFloat(fila.dataset.participacion) || 0;
        let montoBaseApto = (participacion * totalBrutoMes) / 100;
        
        fila.dataset.montoBase = montoBaseApto; 
        sumaFooterBase += montoBaseApto;

        delete fila.dataset.exoneraciones; 

        fila.querySelector(".celda-monto-base").textContent = `${montoBaseApto.toFixed(2)} Bs.`;
        fila.querySelector(".celda-monto-base-usd").textContent = (montoBaseApto / tasaDolar).toFixed(2);
        
        fila.querySelector(".input-descuento").value = "0.00";
        fila.querySelector(".celda-exoneracion-usd").textContent = "0.00";
        
        fila.querySelector(".celda-total-neto").textContent = montoBaseApto.toFixed(2);
        fila.querySelector(".celda-total-neto-usd").textContent = (montoBaseApto / tasaDolar).toFixed(2);
    });

    // Actualizamos los identificadores del footer directamente
    document.getElementById("foot_monto_base").textContent = `${sumaFooterBase.toFixed(2)}`;
    document.getElementById("foot_monto_base_usd").textContent = (sumaFooterBase / tasaDolar).toFixed(2);
    
    document.getElementById("foot_total_exoneracion").textContent = "0.00";
    document.getElementById("foot_total_exoneracion_usd").textContent = "0.00";
    
    document.getElementById("foot_total_neto").textContent = sumaFooterBase.toFixed(2);
    document.getElementById("foot_total_neto_usd").textContent = (sumaFooterBase / tasaDolar).toFixed(2);

    return true;
}

function recalcularTotalFila(fila) {
    let montoApartamentoBruto = parseFloat(fila.dataset.montoBase) || 0;
    let descuento = parseFloat(fila.querySelector('.input-descuento').value) || 0;
    
    // Actualizar Ref USD de la exoneración al vuelo
    fila.querySelector('.celda-exoneracion-usd').textContent = (descuento / tasaDolar).toFixed(2);
    
    let montoNetoFinal = Math.max(0, montoApartamentoBruto - descuento);
    fila.querySelector(".celda-total-neto").textContent = montoNetoFinal.toFixed(2);
    fila.querySelector(".celda-total-neto-usd").textContent = (montoNetoFinal / tasaDolar).toFixed(2); // <-- AÑADIDO
    
    let sumaNetoGeneral = 0;
    let sumaExoneracionGeneral = 0;

    tablaMensualidadAsignar.querySelectorAll('tbody tr').forEach(f => {
        let baseFila = parseFloat(f.dataset.montoBase) || 0;
        let exoFila = parseFloat(f.querySelector(".input-descuento").value) || 0;
        let exoEfectiva = Math.min(baseFila, exoFila);
        
        sumaNetoGeneral += parseFloat(f.querySelector(".celda-total-neto").textContent) || 0;
        sumaExoneracionGeneral += exoEfectiva;
    });

    document.getElementById("foot_total_exoneracion").textContent = sumaExoneracionGeneral.toFixed(2);
    document.getElementById("foot_total_exoneracion_usd").textContent = (sumaExoneracionGeneral / tasaDolar).toFixed(2); // <-- AÑADIDO
    
    document.getElementById("foot_total_neto").textContent = sumaNetoGeneral.toFixed(2);
    document.getElementById("foot_total_neto_usd").textContent = (sumaNetoGeneral / tasaDolar).toFixed(2); // <-- AÑADIDO
}

function recolectarDatosTabla() {
    const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
    const datos = [];

    let todosLosPresupuestos = [];
    window.presupuestosActuales.forEach(det => {
        if (det.id_presupuestos_asociados) {
            todosLosPresupuestos.push(...det.id_presupuestos_asociados.split(',').map(Number));
        } else if (det.id_detalle_presupuesto) {
            todosLosPresupuestos.push(det.id_detalle_presupuesto);
        }
    });

    filas.forEach(fila => {
        const montoNeto = parseFloat(fila.querySelector(".celda-total-neto").textContent) || 0;
        const descuento = parseFloat(fila.querySelector('.input-descuento').value) || 0;
        const montoBruto = montoNeto + descuento; 

        if (montoBruto <= 0) return;

        const idMensualidad = fila.querySelector(".celda-total-neto").dataset.idMensualidad || '';

        datos.push({
            id_apartamento: fila.id,
            id_mensualidad: idMensualidad,
            monto: montoBruto,
            descuento: descuento,
            id_presupuestos: todosLosPresupuestos 
        });
    });

    return datos;
}

async function prepararFormulario(row) {
    const mes = String(row.mes).padStart(2, '0');
    const fecha = `${row.anio}-${mes}-01`;
    const id_periodo = row.id_periodo;

    const select = selectMesAsignar;
    let opcionExistente = Array.from(select.options).find(opt => opt.id === fecha);
    if (!opcionExistente) {
        const nuevaOpcion = document.createElement('option');
        nuevaOpcion.id = fecha;
        nuevaOpcion.setAttribute('data-temporal', 'true');
        const fechaObj = new Date(row.anio, parseInt(mes) - 1, 1);
        nuevaOpcion.textContent = fechaObj.toLocaleString("es-ES", { month: 'long', year: 'numeric' }).toUpperCase();
        select.appendChild(nuevaOpcion);
        opcionExistente = nuevaOpcion;
    }
    
    select.value = '';
    opcionExistente.selected = true;
    select.disabled = true;

    const exitoCarga = await cargarTablaPresupuestos(fecha);

    if (!exitoCarga) {
        Alertas.mostrar('error', 'Error de Integridad', 'No se puede modificar esta mensualidad porque el presupuesto base de este mes fue eliminado.');
        resetModalMensualidad();
        return; 
    }

    const formData = new FormData();
    formData.append("operacion", "consultar_presupuestos_asociados");
    formData.append("periodo_id", id_periodo);

    const respuesta = await Peticiones.enviar(formData, "", true); 
    
    if (respuesta.estatus && respuesta.datos) {
        const asignaciones = respuesta.datos;
        const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
        
        filas.forEach((filaTr) => {
            const aptoId = filaTr.id; 
            const asignacion = asignaciones.find(a => String(a.apartamento_id) === aptoId);
            
            if (asignacion) {
                // Almacenamos el ID de la mensualidad en la celda de total neto vía dataset
                const celdaTotal = filaTr.querySelector(".celda-total-neto");
                if (celdaTotal) {
                    celdaTotal.dataset.idMensualidad = asignacion.id_mensualidad;
                }
                
                if (asignacion.descuento) {
                    const inputDesc = filaTr.querySelector('.input-descuento');
                    if (inputDesc) {
                        inputDesc.value = parseFloat(asignacion.descuento).toFixed(2);
                    }
                }
                recalcularTotalFila(filaTr);
            }
        });
    }

    document.getElementById("porcentaje_demora").value = row.porcentaje_interes || '';
    document.getElementById("dia_limite").value = row.limite_mensualidad || '';

    document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
    botonFormulario.dataset.op = "modificar";
    document.getElementById('titulo_modal').textContent = "Modificar Mensualidad";
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-calendar-minus");
    botonFormulario.dataset.periodo_id = id_periodo;
    
    modalMensualidad.show();
}

function mostrarVistaPrevia(data, fecha) {
    const nombreMes = FormatoFechas.nombreMes(parseInt(data.mes)) || data.mes;
    document.getElementById("vp_periodo").textContent = `${nombreMes} ${data.anio}`.toUpperCase();

    const montoBs = parseFloat(data.monto) || 0;
    const tasa = parseFloat(data.tasa_dolar) || 1;
    const montoUsd = montoBs / tasa;

    document.getElementById("vp_monto_base_bs").textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_monto_base_usd").textContent = `Ref: ${montoUsd.toFixed(2)} $`;
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

// Asistente Visual (basicamente otro modal con los checkboxes)
function abrirAsistenteExoneracion(id_apartamento, participacion) {
    const fila = document.getElementById(id_apartamento);
    const nroApto = fila.querySelector('td:first-child').textContent.replace('Apt.', '').trim();

    let exoneracionesGuardadas = [];
    if (fila.dataset.exoneraciones) {
        try {
            exoneracionesGuardadas = JSON.parse(fila.dataset.exoneraciones);
        } catch(e) { }
    }

    let htmlCheckboxes = `<div class="list-group shadow-sm mb-2 rounded-3">`;

    window.presupuestosActuales.forEach((det, index) => {
        let montoProporcional = ((participacion * parseFloat(det.monto)) / 100).toFixed(2);
        let montoProporcionalUsd = (montoProporcional / tasaDolar).toFixed(2); // <-- AÑADIDO
        let estaMarcado = exoneracionesGuardadas.includes(index) ? "" : "checked";
        
        htmlCheckboxes += `
            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer border py-3 px-3" style="background-color: var(--ch-input-bg); border-color: var(--ch-table-border) !important; color: var(--ch-table-text); transition: all 0.2s;">
                <div class="d-flex align-items-center">
                    <input class="form-check-input check-exoneracion me-3 my-0 border-secondary" type="checkbox" data-index="${index}" data-monto="${montoProporcional}" style="transform: scale(1.3);" ${estaMarcado}>
                    <span class="fw-semibold fs-6">${det.nombre}</span>
                </div>
                <div class="text-end ms-3">
                    <span class="fw-bold fs-5 text-primary">${montoProporcional}</span>
                    <span class="text-muted ms-1 font-monospace" style="font-size: 0.8rem;">Bs.</span>
                    <div class="text-muted small mt-1 font-monospace" style="font-size: 0.75rem;">Ref: ${montoProporcionalUsd} $</div>
                </div>
            </label>`;
    });
    
    htmlCheckboxes += `</div>`;

    const titulo = `Exoneración: Apt. ${nroApto}`;
    const subtitulo = `Desmarca los servicios a los que el apartamento <b>renuncia o no tiene acceso</b>. El sistema deducirá su costo automáticamente.`;

    Alertas.mostrarAsistenteInteractivo(titulo, subtitulo, htmlCheckboxes, () => {
        let totalDescuento = 0;
        let nuevasExoneraciones = []; // Arreglo para guardar la nueva memoria
        
        document.querySelectorAll('.check-exoneracion:not(:checked)').forEach(chk => {
            totalDescuento += parseFloat(chk.dataset.monto);
            nuevasExoneraciones.push(parseInt(chk.dataset.index)); // Guardamos el índice desmarcado
        });
        
        fila.dataset.exoneraciones = JSON.stringify(nuevasExoneraciones);

        const inputDescuento = fila.querySelector('.input-descuento');
        const montoBase = parseFloat(fila.dataset.montoBase) || 0;

        if (totalDescuento > montoBase) {
            inputDescuento.value = montoBase.toFixed(2);
            Alertas.mostrar('warning', 'Ajuste Automático', 'El descuento supera la cuota base y fue ajustado al máximo permitido.');
        } else {
            inputDescuento.value = totalDescuento.toFixed(2);
        }
        
        recalcularTotalFila(fila); 
    });
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

    const formData = new FormData();
    formData.append("operacion", "modificar_mensualidad");
    formData.append("periodo_id", botonFormulario.dataset.periodo_id);
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
function confirmarEliminar(id_periodo) {
    Alertas.confirmarAccion(
        "¿Eliminar Mensualidad?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id_periodo); }
    );
}

async function eliminar(id_periodo) {
    const formData = new FormData();
    formData.append("operacion", "eliminar_mensualidad");
    formData.append("periodo_id", id_periodo);

    const respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, () => {
        tablaMensualidades.replaceData();
        verificarMeses();
    });
}

function resetModalMensualidad() {
    const filas = tablaMensualidadAsignar.querySelectorAll("tbody tr");
    filas.forEach(fila => {
        fila.querySelector(".celda-monto-base").textContent = "0.00 Bs.";
        fila.querySelector(".input-descuento").value = "0.00";
        fila.querySelector(".celda-monto-base-usd").textContent = "0.00";
        fila.querySelector(".celda-exoneracion-usd").textContent = "0.00";
        fila.querySelector(".celda-total-neto-usd").textContent = "0.00";

        const celdaTotal = fila.querySelector(".celda-total-neto");
        celdaTotal.textContent = "0.00 Bs.";
        delete celdaTotal.dataset.idMensualidad;
        delete fila.dataset.exoneraciones;
    });

    document.getElementById("foot_monto_base").textContent = "0.00 Bs.";
    document.getElementById("foot_total_exoneracion").textContent = "0.00";
    document.getElementById("foot_total_neto").textContent = "0.00";
    document.getElementById("foot_monto_base_usd").textContent = "0.00";
    document.getElementById("foot_total_exoneracion_usd").textContent = "0.00";
    document.getElementById("foot_total_neto_usd").textContent = "0.00";

    const select = selectMesAsignar;
    Array.from(select.options).forEach(opt => {
        if (opt.hasAttribute('data-temporal')) opt.remove();
    });
    select.disabled = false;
    select.value = "";

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
