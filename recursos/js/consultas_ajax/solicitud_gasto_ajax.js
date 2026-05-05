let tabla_solicitud_gasto;
let id_modificar;
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar")) || 1;

const modal = new bootstrap.Modal(document.getElementById("modal_solicitud_gasto"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const form = document.querySelector("#form_solicitud_gasto");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.buscarPresupuesto = buscarPresupuesto;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

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

    const formatoMonto = (cell) => `<span class="fw-semibold">Bs. ${parseFloat(cell.getValue()).toFixed(2)}</span>`;
    
    // Formato para el Estado con Íconos
    const formatoEstado = (cell) => {
        const config = obtenerConfigEstadoSolicitud(cell.getValue());
        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    };

    // Formato para la Prioridad
    const formatoPrioridad = (cell) => {
        const config = obtenerConfigPrioridadSolicitud(cell.getValue());
        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_solicitud;
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" value="${id}" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" value="${id}" data-tooltip="true" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" value="${id}" data-tooltip="true" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 140, responsive: 0 },
        { title: "Prioridad", field: "prioridad", formatter: formatoPrioridad, minWidth: 140, headerHozAlign: "center", hozAlign: "center" },
        { title: "Monto", field: "monto_estimado", formatter: formatoMonto, minWidth: 130 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, responsive: 0, widthGrow: 2,
            download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { currentTarget: btn };

                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }

                if (btn.classList.contains('modificar')) prepararFormulario(mockEvent);
                if (btn.classList.contains('eliminar')) {
                    Swal.fire({ title: '¿Estás seguro?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e01d22', confirmButtonText: 'Eliminar' })
                    .then(r => r.isConfirmed && eliminar(btn.value));
                }
            }
        }
    ];

    tabla_solicitud_gasto = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } });

    Tablas.inicializarBuscadorGlobal(tabla_solicitud_gasto, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // Cálculos de Monto (Usando variable global tasa_dolar)
    let montoBs = parseFloat(data.monto_estimado);
    let montoUsd = montoBs / tasa_dolar;
    
    const tituloMonto = document.getElementById("vp_monto_bs"); // Seleccionamos el título del monto

    tituloMonto.textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_monto_usd").textContent = `Ref: ${montoUsd.toFixed(2)} $`;

    // Solicitante
    document.getElementById("vp_solicitante").textContent = data.nombre_solicitante || 'N/A';

    const estadoEl = document.getElementById("vp_estado");
    
    // Limpiamos colores previos por si el usuario abre varios modales seguidos
    tituloMonto.classList.remove('text-success', 'text-danger', 'text-primary');

    // Estado
    const configEstado = obtenerConfigEstadoSolicitud(data.estado);
    estadoEl.innerHTML = ComponentesUI.crearSoftBadge(configEstado.color, configEstado.icono, configEstado.texto);

    // Prioridad
    const configPrioridad = obtenerConfigPrioridadSolicitud(data.prioridad);
    const prioEl = document.getElementById("vp_prioridad");
    prioEl.innerHTML = ComponentesUI.crearSoftBadge(configPrioridad.color, configPrioridad.icono, configPrioridad.texto);

    // Fecha
    // FormatoFechas está disponible
    if (window.FormatoFechas && typeof FormatoFechas.formatoUsuario === "function") {
        document.getElementById("vp_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha_reporte);
    } else {
        // Fallback nativo por si acaso
        let partes = data.fecha_reporte.split('-');
        document.getElementById("vp_fecha").textContent = `${partes[2]}-${partes[1]}-${partes[0]}`;
    }

    // Justificación
    document.getElementById("vp_descripcion").textContent = data.descripcion_necesidad || 'Sin justificación provista.';

    // Mostramos el modal
    modalDetalles.show();
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar_solicitud');

    const disponible = await consultarPresupuestoDisponible(datos.get('presupuesto_id'));
    if (disponible === null) return;
    if (parseFloat(datos.get('monto_estimado')) > disponible) {
        Alertas.mostrar('error', 'Presupuesto insuficiente', `Solo hay Bs. ${disponible.toFixed(2)} disponibles.`);
        return;
    }
    datos.set('estado', 'Pendiente');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        modal.hide();
        tabla_solicitud_gasto.replaceData();
    });
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'consultar_solicitud');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, async (respuestaServidor) => {
        const data = respuestaServidor.datos;
        
        await cargarMesesYAniosConPresupuesto(); // asegurar selects

        form.querySelector('#selector_mes').value = data.mes;
        form.querySelector('#selector_anio').value = data.anio;
        await buscarPresupuesto(); // para llenar info

        form.querySelector('#fecha_reporte').value = data.fecha_reporte;
        form.querySelector('#descripcion_necesidad').value = data.descripcion_necesidad;
        form.querySelector('#nombre_solicitante').value = data.nombre_solicitante;
        form.querySelector('#monto_estimado').value = data.monto_estimado;
        form.querySelector('#monto_estimado').dataset.original = data.monto_estimado;
        form.querySelector('#prioridad').value = data.prioridad;

        document.getElementById('presupuesto_total').textContent = 
            `Bs. ${parseFloat(data.monto_presupuesto_total).toFixed(2) || '-'}`;
        

        form.querySelector('#presupuesto_id').value = data.presupuesto_id;

        document.getElementById('titulo_modal').textContent = 'Modificar Solicitud';
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-send-exclamation");
        // form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        form.querySelector('#boton_formulario').dataset.id = id;

        modal.show();
    });
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
    datos.set('operacion', 'modificar_solicitud');
    datos.set('estado', 'Pendiente');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_solicitud_gasto.replaceData();
    });
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'eliminar_solicitud');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        tabla_solicitud_gasto.replaceData();
    });
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
async function cargarMesesYAniosConPresupuesto() {
    const datos = new FormData();
    datos.append('operacion', 'meses_anios_con_presupuesto');
    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const selectorMes = document.getElementById('selector_mes');
        const selectorAnio = document.getElementById('selector_anio');
        selectorMes.innerHTML = '<option value="" hidden>Seleccione mes</option>';
        selectorAnio.innerHTML = '<option value="" hidden>Seleccione año</option>';

        const meses = [...new Set(respuestaServidor.data.map(p => p.mes))].sort((a,b)=>a-b);
        const anios = [...new Set(respuestaServidor.data.map(p => p.anio))].sort((a,b)=>b-a);

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
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        document.getElementById('presupuesto_total').textContent = 
            `Bs. ${parseFloat(respuesta.monto_presupuesto_total).toFixed(2)}`;
        document.getElementById('presupuesto_disponible').textContent = 
            `Bs. ${parseFloat(respuesta.disponible).toFixed(2)}`;
        document.getElementById('presupuesto_id').value = respuesta.id_presupuesto;
        document.getElementById('info_presupuesto').style.display = 'block';
        document.getElementById('campos_formulario_completo').style.display = 'block';
    });
    // Revisar caso negativo
}

async function consultarPresupuestoDisponible(presupuestoId) {
    const datos = new FormData();
    datos.append('operacion', 'consultar_presupuesto');
    datos.append('presupuesto_id', presupuestoId);
    const respuesta = await Peticiones.enviar(datos);
    return respuesta?.estatus ? parseFloat(respuesta.disponible) : null;
}

/**
 * Procesa el estado de la solicitud y devuelve su configuración visual
 */
function obtenerConfigEstadoSolicitud(estado) {
    let est = estado || "Pendiente";
    let color = "warning";
    let icono = "bi-clock-history";

    if (est === "Aprobada") {
        color = "success";
        icono = "bi-check-circle-fill";
    } else if (est === "Rechazada") {
        color = "danger";
        icono = "bi-x-circle-fill";
    }

    return { color, icono, texto: est };
}

/**
 * Procesa la prioridad de la solicitud y devuelve su configuración visual
 */
function obtenerConfigPrioridadSolicitud(prioridad) {
    let p = String(prioridad).toLowerCase();
    let color = "secondary";
    let icono = "bi-bookmark";
    let texto = "Desconocida";

    if (p === "1" || p === "alta") {
        color = "danger";
        icono = "bi-arrow-up-circle-fill";
        texto = "Alta";
    } else if (p === "2" || p === "media") {
        color = "warning";
        icono = "bi-dash-circle-fill";
        texto = "Media";
    } else if (p === "3" || p === "baja") {
        color = "info";
        icono = "bi-arrow-down-circle-fill";
        texto = "Baja";
    }

    return { color, icono, texto };
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
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-send-plus");

    // form.querySelector('#boton_formulario').textContent = 'Registrar';
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Solicitud';
    delete form.querySelector('#boton_formulario').dataset.id;
});

document.getElementById('selector_mes').addEventListener('change', buscarPresupuesto);
document.getElementById('selector_anio').addEventListener('change', buscarPresupuesto);

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Solicitudes de Gasto', description: 'Módulo para gestionar peticiones de dinero basadas en el presupuesto mensual del condominio.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_solicitud_gasto"]', popover: { title: 'Crear Solicitud', description: 'Inicia el proceso para solicitar recursos. Necesitarás saber a qué mes y año cargarás el gasto.', side: "bottom", align: 'start' } },
        { element: '#tabla_solicitud_gasto', popover: { title: 'Historial', description: 'Aquí verás el estado de tus solicitudes (Pendientes, Aprobadas o Rechazadas) y podrás editarlas si es necesario.', side: "top", align: 'center' } }
    ];

    const stepsModal = [
        { element: '#selector_mes', popover: { title: 'Periodo Presupuestario', description: 'Selecciona el Mes y el Año. El sistema verificará automáticamente si existe presupuesto disponible.', side: 'bottom', align: 'start' } },
        { element: '#modal_solicitud_gasto .modal-body', popover: { title: 'Formulario Dinámico', description: 'Una vez selecciones un periodo válido con fondos disponibles, aparecerán aquí el resto de los campos para completar la solicitud.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_solicitud_gasto',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
