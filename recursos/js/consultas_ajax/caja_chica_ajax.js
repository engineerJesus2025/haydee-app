/**
 * caja_chica_ajax.js
 * Gestión de Caja Chica - Peticiones AJAX
 * Dependencias: utilidades.js, validaciones.js
 */

let peticionesActivas = 0; // Se mantiene solo para el control manual de modales (opcional)
let tasa_dolar = localStorage.getItem("tasa_dolar") || 0;
let diferencia = 0;

let modal_carga = new bootstrap.Modal("#modal_carga", { focus: false });
let modal_observacion = new bootstrap.Modal("#modal_descripciones", { focus: false });
let modal_registro_gastos = new bootstrap.Modal("#modal_registro_gastos", { focus: false });
let modal_reposicion_caja = new bootstrap.Modal("#modal_reponer_caja");
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

let tabla_movimientos;
let boton_formulario = document.getElementById("boton_gasto_caja");
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

// Almacén de descripciones de cajas
let descripciones = {};

// Inicializar
consultarCajasChicas();

// Evento cambio de caja en el select
document.getElementById("mes_select").addEventListener("change", (e) => {
    e.target.classList.remove('caja-highlight');

    let id_caja = e.target.value;
    let option = e.target.options[e.target.selectedIndex];

    document.getElementById("descripciones").textContent = descripciones[id_caja] || '';
    document.getElementById("descripciones").closest(".col-7")?.removeAttribute("hidden");

    // Actualizar fondo fijo mostrado
    let saldoActual = option.getAttribute("saldo_actual") || 0;
    document.getElementById("span_fondo_fijo").textContent = 
        `Fondo actual de caja: ${saldoActual} Bs. / ${(saldoActual / tasa_dolar).toFixed(2)} $`;

    actualizarSaldos();
    // Estado de la caja
    if (option.getAttribute("activa") === "Cerrada") {
        document.getElementById("botones_movimientos")?.setAttribute("hidden", "");
        document.getElementById("span_caja_activa").className = "text-danger";
        document.getElementById("span_caja_activa").textContent = "Esta caja está cerrada";
    } else {
        document.getElementById("botones_movimientos")?.removeAttribute("hidden");
        document.getElementById("span_caja_activa").className = "text-success";
        document.getElementById("span_caja_activa").textContent = "Esta es la caja actual";
    }

    // Recargar tabla de movimientos
    if (tabla_movimientos) {
        const parametrosExtra = { 
            operacion: 'consultar_movimientos_caja',
            caja_chica_id: document.getElementById("mes_select").value 
        }
        tabla_movimientos.setData("",parametrosExtra);

        // tabla_movimientos.replaceData();
    } else {
        inicializarTablaMovimientos();
    }
});

// Botón intercambio de moneda en formulario de gasto
document.getElementById("boton_intercambio_monto")?.addEventListener('click', (e) => {
    e.preventDefault();
    intercambiarMoneda('monto', 'monto_cambio');
});

// Botón intercambio en reposición
document.getElementById("boton_intercambio_monto_reponer")?.addEventListener('click', (e) => {
    e.preventDefault();
    intercambiarMoneda('monto_reponer', 'monto_cambio_reponer');
});

// Botón Para modificar observacion
document.getElementById("boton_modificar_observacion")?.addEventListener('click', (e) => {
    document.getElementById("descripcion_input").value = document.getElementById("descripciones").textContent;
});

// Limpiar modal al cerrar
document.getElementById("modal_registro_gastos")?.addEventListener("hide.bs.modal", () => {
    document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("form_registro_gasto").reset();
    document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja")?.textContent || '';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    // Resetear moneda a Bs.
    let monto = document.getElementById("monto");
    if (monto && monto.getAttribute("monto") === "$") {
        intercambiarMoneda('monto', 'monto_cambio');
    }
    diferencia = 0;
});

// ========== FUNCIONES AUXILIARES ==========
function intercambiarMoneda(idMonto, idCambio) {
    let monto = document.getElementById(idMonto);
    let cambio = document.getElementById(idCambio);
    if (!monto || !cambio) return;

    let temp = monto.value;
    monto.value = cambio.value;
    cambio.value = temp;

    if (monto.getAttribute("monto") === "bs") {
        monto.setAttribute("monto", "$");
        monto.parentElement.querySelector(".icono_moneda").textContent = "$";
        cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
    } else {
        monto.setAttribute("monto", "bs");
        monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
        cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
    }
}

// ========== CONSULTA DE CAJAS CHICAS ==========
async function consultarCajasChicas() {
    let datos = new FormData();
    datos.append("operacion", "consultar_cajas_chicas");

    let respuesta = await Peticiones.enviar(datos);
    
    if (!respuesta.datos || respuesta.datos.length === 0) {
        document.getElementById("span_caja_activa").textContent = "No hay cajas registradas";
        document.getElementById("span_fondo_fijo").textContent = '';
        document.getElementById("mes_select").value = '';
        document.getElementById("mes_select").setAttribute('disabled','');
        return;
    }

    Validador.procesarRespuesta(respuesta, () => {
        let select = document.getElementById("mes_select");
        let fragment = document.createDocumentFragment();
        select.innerHTML = '';

        respuesta.datos.forEach(caja => {
            let option = document.createElement("option");
            option.value = caja.id_caja_chica;

            let [anio, mes] = caja.fecha_creacion.split('-');
            let nombreMes = FormatoFechas.nombreMes(parseInt(mes, 10));
            option.textContent = `${nombreMes} del ${anio}`;

            option.setAttribute("saldo_actual", caja.saldo_calculado || caja.fondo_fijo);
            option.setAttribute("saldo_inicial", caja.fondo_fijo);
            option.setAttribute("activa", caja.estado);
            fragment.appendChild(option);

            descripciones[caja.id_caja_chica] = caja.descripcion || '';
        });
        select.appendChild(fragment);

        let idBuscar = Notificaciones.obtenerIdBusqueda();
        let opcionExiste = idBuscar ? Array.from(select.options).some(opt => opt.value === String(idBuscar)) : false;

        if (idBuscar && opcionExiste) {
            // Si hay una notificación válida, dejamos que el script centralizado dispare el evento
            Notificaciones.resaltarEnSelect('mes_select');
        } else {
            // Si venía de una notificación pero la caja ya no existe, mostramos el error manual
            if (idBuscar && !opcionExiste) {
                Notificaciones.mostrarToast('error', 'No encontrado', 'La caja notificada no existe o se encuentra cerrada.');
                Notificaciones.limpiarUrl();
            }
            
            // Como no hubo evento de notificación, disparamos el cambio normal para cargar la tabla
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }
        } 
    });
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // 1. Cálculos de Monto (Usando tu variable global tasa_dolar)
    let montoBs = parseFloat(data.monto);
    let montoUsd = montoBs / tasa_dolar;
    
    document.getElementById("vp_monto_bs").textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_monto_usd").textContent = `Ref: ${montoUsd.toFixed(2)} $`;

    // 2. Concepto
    document.getElementById("vp_concepto").textContent = data.concepto || 'Sin descripción';

    // 3. Fecha 
    document.getElementById("vp_fecha").textContent = FormatoFechas.formatear(data.fecha, 'DD-MM-YYYY');

    // 4. Estado 
    const estadoEl = document.getElementById("vp_estado");
    estadoEl.textContent = data.estado;
    
    if (data.estado === 'Repuesto') {
        estadoEl.className = "fw-bold text-end text-success";
    } else if (data.estado === 'Pendiente por reposicion') {
        // Un tono naranja/amarillo oscuro para advertir que está pendiente
        estadoEl.className = "fw-bold text-end text-warning text-dark";
    } else {
        estadoEl.className = "fw-bold text-end text-secondary";
    }

    // Mostramos el modal
    modalDetalles.show();
}

// ========== INICIALIZAR TABLA DE MOVIMIENTOS ==========
function inicializarTablaMovimientos() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoFecha = (cell) => FormatoFechas.formatear(cell.getValue(), 'DD-MM-YYYY');
    const formatoMonto = (cell) => {
        const row = cell.getData();
        return `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / tasa_dolar).toFixed(2)} $`;
    };
    const formatoEstado = (cell) => {
        let colores = {
            'Pendiente por reposicion': 'badge bg-warning text-dark',
            'Repuesto': 'badge bg-success'
        };
        return `<span class="${colores[cell.getValue()] || 'badge bg-secondary'}">${cell.getValue()}</span>`;
    }
    
    const formatoBotones = (cell) => {
        const id = cell.getData().id_movimiento_caja;
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
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 130 },
        { title: "Fecha", field: "fecha", formatter: formatoFecha, minWidth: 130, responsive: 0 },
        { title: "Monto", field: "monto", formatter: formatoMonto, minWidth: 160 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false, headerHozAlign: "center", widthGrow: 5,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { target: btn };
                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }
                
                if (btn.classList.contains('modificar')) prepararFormulario(mockEvent);
                if (btn.classList.contains('eliminar')) eventoEliminar(mockEvent);
            }
        }
    ];

    const opcionesExtra = {
        parametrosExtra: { 
            operacion: 'consultar_movimientos_caja',
            caja_chica_id: document.getElementById("mes_select").value 
        }
    };

    tabla_movimientos = Tablas.cargarTabulador(contenedor.id, "", columnas, opcionesExtra);

    Tablas.inicializarBuscadorGlobal(tabla_movimientos, "busqueda_global", columnas);
}

// ========== REGISTRAR GASTO ==========
async function registrar() {
    let datos = new FormData();
    datos.append("fecha", document.getElementById("fecha").value);
    datos.append("concepto", document.getElementById("concepto").value);
    datos.append("caja_chica_id", document.getElementById("mes_select").value);

    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio").value;
    datos.append("monto", monto);
    datos.append("operacion", "registrar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        actualizarSaldos();
        modal_registro_gastos.hide();
        tabla_movimientos.replaceData();
    });
}

// ========== PREPARAR FORMULARIO PARA EDICIÓN ==========
async function prepararFormulario(e) {
    let id = e.target.closest('button').value;

    let datos = new FormData();
    datos.append("id_movimiento_caja", id);
    datos.append("operacion", "consultar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let mov = respuestaServidor.datos;
        document.getElementById("fecha").value = mov.fecha;
        document.getElementById("concepto").value = mov.concepto;
        // El monto se carga en Bs. (asumimos que la base guarda en Bs.)
        document.getElementById("monto").value = mov.monto;
        document.getElementById("monto_cambio").value = (mov.monto / tasa_dolar).toFixed(2);

        if (permisoModificar != 1) {
            boton_formulario.setAttribute("hide", true);
            boton_formulario.setAttribute("disabled", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", mov.id_movimiento_caja);
        boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('titulo_modal_registro_gasto').textContent = "Modificar Gasto de Caja";

        diferencia = parseFloat(mov.monto);

        modal_registro_gastos.show();
    });
}

// ========== MODIFICAR GASTO ==========
async function modificar(id) {
    let datos = new FormData();
    datos.append("fecha", document.getElementById("fecha").value);
    datos.append("concepto", document.getElementById("concepto").value);
    datos.append("id_movimiento_caja", id);

    let montoInput = document.getElementById("monto");

    datos.append("operacion", "modificar_movimiento"); 

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_registro_gastos.hide();
        tabla_movimientos.replaceData();
    });
}

// ========== ELIMINAR GASTO ==========
function eventoEliminar(e) {
    let id = e.target.closest('button').value;

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea eliminar este gasto?",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        icon: "warning"
    }).then(result => {
        if (result.isConfirmed) eliminar(id);
    });
}

async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_movimiento_caja", id);
    datos.append("operacion", "eliminar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        actualizarSaldos();
        tabla_movimientos.replaceData();
    });
}

// ========== REPONER CAJA ==========
async function reponerCaja() {
    let datos = new FormData();
    datos.append("caja_chica_id", document.getElementById("mes_select").value);

    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio_reponer").value;
    datos.append("monto", monto);
    datos.append("operacion", "reponer_caja");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_reposicion_caja.hide();
        consultarCajasChicas(); // Recargar cajas para actualizar saldos
    });
}

// ========== modificar DESCRIPCIÓN DE CAJA ==========
async function modificarObservacion() {
    let id_caja = document.getElementById("mes_select").value;
    let descripcion = document.getElementById("descripcion_input").value;

    let datos = new FormData();
    datos.append("caja_chica_id", id_caja);
    datos.append("descripcion", descripcion);
    datos.append("operacion", "modificar_descripcion");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_observacion.hide();
        consultarCajasChicas(); // Recargar para actualizar descripción en el objeto
    });
}

// ========== ACTUALIZAR SALDOS MOSTRADOS ==========
function actualizarSaldos() {
    let select = document.getElementById("mes_select");
    let option = select.options[select.selectedIndex];
    let saldoActual = option.getAttribute("saldo_actual");
    let saldoInicial = option.getAttribute("saldo_inicial");

    document.getElementById("fondos_caja").textContent = `${saldoActual} Bs. / ${(saldoActual / tasa_dolar).toFixed(2)} $`;
    document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja").textContent;
    document.getElementById("fondos_caja_restantes").textContent = document.getElementById("fondos_caja").textContent;
    document.getElementById("fondos_gastados").textContent = `${saldoInicial - saldoActual} Bs. / ${((saldoInicial - saldoActual) / tasa_dolar).toFixed(2)} $`;
}

// ========== ENVÍO DE FORMULARIOS ==========
function envio(operacion) {
    if (operacion === "modificar") {
        modificar(boton_formulario.getAttribute("id_modificar"));
    } else if (operacion === "Registrar") {
        registrar();
    } else if (operacion === "reponer_caja") {
        reponerCaja();
    } else {
        Alertas.mostrar('error', 'Atención', 'Operación no válida');
    }
}

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Módulo de Caja Chica', description: 'Bienvenido. Aquí puedes administrar los fondos menores del condominio y registrar sus movimientos.', side: "bottom", align: 'center' } },
            { element: '#mes_select', popover: { title: 'Selector de Caja', description: 'Elige el mes/caja que deseas evaluar. Verás automáticamente el fondo fijo y si la caja está activa o cerrada.', side: "bottom", align: 'start' } },
            { element: '#botones_movimientos', popover: { title: 'Acciones de Caja', description: 'Si la caja está activa, aquí podrás Registrar un Nuevo Gasto o Reponer el dinero de la caja.', side: "bottom", align: 'start' } },
            { element: '#tabla_registros_sistema', popover: { title: 'Movimientos Registrados', description: 'Aquí se listan todos los gastos o reposiciones hechas en esta caja. Puedes editar o eliminar los registros.', side: "top", align: 'center' } },
            { element: '#boton_modificar_observacion', popover: { title: 'Descripción de Caja', description: 'Puedes añadir o editar una nota o descripción general para el mes de esta caja chica.', side: "top", align: 'start' } }
        ];

    const stepsModal = [
            { element: '#fecha', popover: { title: 'Fecha', description: 'Indica la fecha en que se realizó este gasto menor.', side: 'bottom', align: 'start' } },
            { element: '#monto', popover: { title: 'Monto del Gasto', description: 'Ingresa la cantidad gastada. Si necesitas ingresarlo en divisas, usa el botón de intercambio.', side: 'bottom', align: 'start' } },
            { element: '#boton_intercambio_monto', popover: { title: 'Cambio de Moneda', description: 'Haz clic aquí para alternar entre Bolívares y Dólares. El sistema calculará el equivalente automáticamente según la Tasa BCV.', side: 'bottom', align: 'center' } },
            { element: '#fondos_restante', popover: { title: 'Control de Fondos', description: 'El sistema te mostrará cuánto dinero queda en la caja para evitar que gastes más de lo disponible.', side: 'top', align: 'start' } },
            { element: '#concepto', popover: { title: 'Concepto', description: 'Escribe de forma clara y precisa en qué se gastó el dinero.', side: 'top', align: 'start' } }, 
            { element: '#boton_gasto_caja', popover: { title: 'Guardar', description: 'Verifica los datos y registra el movimiento en la caja.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_registro_gastos',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
