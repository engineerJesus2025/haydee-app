let tabla_solicitud_gasto;
let id_modificar;
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar")) || 1;

const modal = new bootstrap.Modal(document.getElementById("modal_solicitud_gasto"), { focus: false });
const form = document.querySelector("#form_solicitud_gasto");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.buscarPresupuesto = buscarPresupuesto;

document.addEventListener('DOMContentLoaded', () => {
    consultar();
    cargarMesesYAniosConPresupuesto();
});

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());
    const formatoMonto = (cell) => `Bs. ${parseFloat(cell.getValue()).toFixed(2)}`;
    const formatoPrioridad = (cell) => {
        const map = { '1': 'success', '2': 'warning', '3': 'danger' };
        return `<span class="badge bg-${map[cell.getValue()] || 'secondary'}">${cell.getValue()}</span>`;
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_solicitud;
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) html += `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>`;
        if (window.permiso_eliminar) html += `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "FECHA", field: "fecha_reporte", formatter: formatoFecha, minWidth: 100, responsive: 0 },
        { title: "DESCRIPCIÓN", field: "descripcion_necesidad", minWidth: 200 },
        { title: "SOLICITANTE", field: "nombre_solicitante", minWidth: 150 },
        { title: "MONTO", field: "monto_estimado", formatter: formatoMonto, minWidth: 120 },
        { title: "ESTADO", field: "estado", minWidth: 120 },
        { title: "PRIORIDAD", field: "prioridad", formatter: formatoPrioridad, minWidth: 100 },
        {
            title: "ACCIONES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 100, responsive: 0, download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { currentTarget: btn };
                if (btn.classList.contains('modificar')) prepararFormulario(mockEvent);
                if (btn.classList.contains('eliminar')) {
                    Swal.fire({ title: '¿Estás seguro?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e01d22', confirmButtonText: 'Eliminar' })
                    .then(r => r.isConfirmed && eliminar(btn.value));
                }
            }
        }
    ];

    tabla_solicitud_gasto = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } });

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_solicitud_gasto.setFilter([filtros]);
        });
    }
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar');

    const disponible = await consultarPresupuestoDisponible(datos.get('presupuesto_id'));
    if (disponible === null) return;
    if (parseFloat(datos.get('monto_estimado')) > disponible) {
        Alertas.mostrar('error', 'Presupuesto insuficiente', `Solo hay Bs. ${disponible.toFixed(2)} disponibles.`);
        return;
    }
    datos.set('estado', 'Pendiente');

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_solicitud_gasto.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Solicitud registrada.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos);
    if (!respuesta?.estatus) {
        Alertas.mostrar('error', 'Error', 'No se pudo cargar la solicitud.');
        return;
    }

    const data = respuesta.datos;
    
    await cargarMesesYAniosConPresupuesto(); // asegurar selects

    form.querySelector('#selector_mes').value = data.mes;
    form.querySelector('#selector_anio').value = data.anio;
    await buscarPresupuesto(); // para llenar info

    form.querySelector('#fecha').value = data.fecha_reporte;
    form.querySelector('#descripcion').value = data.descripcion_necesidad;
    form.querySelector('#nombre').value = data.nombre_solicitante;
    form.querySelector('#monto_estimado').value = data.monto_estimado;
    form.querySelector('#monto_estimado').dataset.original = data.monto_estimado;
    form.querySelector('#prioridad').value = data.prioridad;

    document.getElementById('presupuesto_total').textContent = 
        `Bs. ${parseFloat(data.monto_presupuesto_total).toFixed(2) || '-'}`;
    

    form.querySelector('#presupuesto_id').value = data.presupuesto_id;

    document.getElementById('titulo_modal').textContent = 'Modificar Solicitud';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const montoNuevo = parseFloat(form.querySelector('#monto_estimado').value);
    const montoOriginal = parseFloat(form.querySelector('#monto_estimado').dataset.original) || 0;
    const presupuestoId = form.querySelector('#presupuesto_id').value;

    const disponible = await consultarPresupuestoDisponible(presupuestoId);
    if (disponible === null) return;
    const disponibleReal = disponible + montoOriginal;
    if (montoNuevo > disponibleReal) {
        Alertas.mostrar('error', 'Presupuesto insuficiente', `Solo hay Bs. ${disponibleReal.toFixed(2)} disponibles.`);
        return;
    }

    const datos = new FormData(form);
    datos.set('id_solicitud', id);
    datos.set('operacion', 'modificar');
    datos.set('estado', 'Pendiente');

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_solicitud_gasto.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Solicitud actualizada.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Peticiones.enviar(datos);
    if (respuesta?.estatus) {
        tabla_solicitud_gasto.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Solicitud eliminada.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
async function cargarMesesYAniosConPresupuesto() {
    const datos = new FormData();
    datos.append('operacion', 'meses_anios_con_presupuesto');
    const respuesta = await Peticiones.enviar(datos);
    if (!respuesta?.estatus) {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudieron cargar los períodos.');
        return;
    }

    const selectorMes = document.getElementById('selector_mes');
    const selectorAnio = document.getElementById('selector_anio');
    selectorMes.innerHTML = '<option value="" hidden>Seleccione mes</option>';
    selectorAnio.innerHTML = '<option value="" hidden>Seleccione año</option>';

    const meses = [...new Set(respuesta.data.map(p => p.mes))].sort((a,b)=>a-b);
    const anios = [...new Set(respuesta.data.map(p => p.anio))].sort((a,b)=>b-a);

    meses.forEach(mes => {
        const op = document.createElement('option');
        op.value = mes;
        op.textContent = FormatoFechas.nombreMes(mes);
        selectorMes.appendChild(op);
    });

    anios.forEach(anio => {
        const op = document.createElement('option');
        op.value = anio;
        op.textContent = anio;
        selectorAnio.appendChild(op);
    });
}

async function buscarPresupuesto() {
    const mes = document.getElementById('selector_mes').value;
    const anio = document.getElementById('selector_anio').value;
    if (!mes || !anio) return;

    const datos = new FormData();
    datos.append('operacion', 'buscar_presupuesto_por_mes_anio');
    datos.append('mes', mes);
    datos.append('anio', anio);
    const respuesta = await Peticiones.enviar(datos);

    if (respuesta?.estatus) {
        document.getElementById('presupuesto_total').textContent = 
            `Bs. ${parseFloat(respuesta.monto_presupuesto_total).toFixed(2)}`;
        document.getElementById('presupuesto_disponible').textContent = 
            `Bs. ${parseFloat(respuesta.disponible).toFixed(2)}`;
        document.getElementById('presupuesto_id').value = respuesta.id_presupuesto;
        document.getElementById('info_presupuesto').style.display = 'block';
        document.getElementById('campos_formulario_completo').style.display = 'block';
    } else {
        Alertas.mostrar('error', 'Atención', respuesta?.mensaje || 'No hay presupuesto para ese período.');
        document.getElementById('info_presupuesto').style.display = 'none';
        document.getElementById('campos_formulario_completo').style.display = 'none';
        document.getElementById('presupuesto_id').value = '';
    }
}

async function consultarPresupuestoDisponible(presupuestoId) {
    const datos = new FormData();
    datos.append('operacion', 'consultar_presupuesto');
    datos.append('presupuesto_id', presupuestoId);
    const respuesta = await Peticiones.enviar(datos);
    return respuesta?.estatus ? parseFloat(respuesta.disponible) : null;
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_solicitud_gasto').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('info_presupuesto').style.display = 'none';
    document.getElementById('campos_formulario_completo').style.display = 'none';
    document.getElementById('presupuesto_id').value = '';
    document.getElementById('titulo_modal').textContent = 'Registrar Solicitud';
    form.querySelector('#boton_formulario').textContent = 'Registrar';
    delete form.querySelector('#boton_formulario').dataset.id;
});

document.getElementById('selector_mes').addEventListener('change', buscarPresupuesto);
document.getElementById('selector_anio').addEventListener('change', buscarPresupuesto);

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - SOLICITUD DE GASTO
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Función para anclar la burbuja perfectamente
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // Configuración Base
    const configBase = {
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
        }
    };

    // 1. PASOS DE LA VISTA PRINCIPAL
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Solicitudes de Gasto', description: 'Módulo para gestionar peticiones de dinero basadas en el presupuesto mensual del condominio.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_solicitud_gasto"]', popover: { title: 'Crear Solicitud', description: 'Inicia el proceso para solicitar recursos. Necesitarás saber a qué mes y año cargarás el gasto.', side: "bottom", align: 'start' } },
        { element: '#tabla_solicitud_gasto_wrapper', popover: { title: 'Historial', description: 'Aquí verás el estado de tus solicitudes (Pendientes, Aprobadas o Rechazadas) y podrás editarlas si es necesario.', side: "top", align: 'center' } }
    ];

    // 2. PASOS DEL MODAL (FASE 1: SELECCIÓN)
    const stepsModalInicio = [
        { element: '#selector_mes', popover: { title: 'Periodo Presupuestario', description: 'Selecciona el Mes y el Año. El sistema verificará automáticamente si existe presupuesto disponible.', side: 'bottom', align: 'start' } },
        { element: '#modal_solicitud_gasto .modal-body', popover: { title: 'Formulario Dinámico', description: 'Una vez selecciones un periodo válido con fondos disponibles, aparecerán aquí el resto de los campos para completar la solicitud.', side: 'top', align: 'center' } }
    ];

    // 3. PASOS DEL MODAL (FASE 2: LLENADO COMPLETO)
    const stepsModalCompleto = [
        { element: '#info_presupuesto', popover: { title: 'Disponibilidad', description: 'Aquí puedes ver cuánto dinero queda disponible en el presupuesto seleccionado.', side: 'bottom', align: 'center' } },
        { element: '#fecha', popover: { title: 'Datos Básicos', description: 'Indica la fecha de la solicitud y quién la está realizando.', side: 'bottom', align: 'start' } },
        { element: '#monto_estimado', popover: { title: 'Monto Requerido', description: 'Ingresa la cantidad exacta que necesitas. El sistema no te dejará guardar si supera el disponible.', side: 'top', align: 'start' } },
        { element: '#prioridad', popover: { title: 'Prioridad', description: 'Define qué tan urgente es esta solicitud para que la administración la priorice.', side: 'top', align: 'start' } },
        { element: '#descripcion', popover: { title: 'Justificación', description: 'Explica brevemente para qué se usará el dinero.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Finalizar', description: 'Guarda la solicitud para que entre en estado de revisión.', side: 'top', align: 'center' } }
    ];

    // LÓGICA INTELIGENTE DEL BOTÓN
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalSolicitud = document.getElementById('modal_solicitud_gasto');
    const contenedorCampos = document.getElementById('campos_formulario_completo');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            // Caso 1: Modal Abierto
            if (modalSolicitud && modalSolicitud.classList.contains('show')) {
                // Detectamos si el formulario ya se expandió (si el div oculto está visible)
                const camposVisibles = contenedorCampos && contenedorCampos.style.display !== 'none';
                
                if (camposVisibles) {
                    // Tour Completo
                    tourActivo = driver({ ...configBase, steps: stepsModalCompleto });
                } else {
                    // Tour Inicial (Solo selectores)
                    tourActivo = driver({ ...configBase, steps: stepsModalInicio });
                }
                tourActivo.drive();
            } 
            // Caso 2: Vista Principal
            else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    // Limpieza al cerrar
    if (modalSolicitud) {
        modalSolicitud.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});