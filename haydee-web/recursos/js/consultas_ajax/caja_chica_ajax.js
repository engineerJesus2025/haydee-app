// Aseguramos que la tasa sea numérica y mayor a 0 para evitar división por cero
const tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar")) || 1; 

let diferencia = 0;
let tabla_movimientos;
let descripciones = {}; // Almacén de descripciones

const boton_formulario = document.getElementById("boton_gasto_caja");
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

// Referencias a Modales
const modal_carga = new bootstrap.Modal("#modal_carga", { focus: false });
const modal_registro_gastos = new bootstrap.Modal("#modal_registro_gastos", { focus: false });
const modal_reposicion_caja = new bootstrap.Modal("#modal_reponer_caja");
const modal_inicializar_caja = new bootstrap.Modal("#modal_inicializar_caja", { focus: false });
const modal_revaluar_fondo = new bootstrap.Modal("#modal_revaluar_fondo", { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

// EVENT LISTENERS
document.addEventListener("DOMContentLoaded", () => {
    consultarCajasChicas();
    inicializarEventos();
    configurarAyudaInteractiva();
});

function inicializarEventos() {
    // Selector de Caja
    document.getElementById("mes_select").addEventListener("change", manejarCambioCaja);

    // Intercambio de Moneda (Solo en modal de Gastos)
    document.getElementById("boton_intercambio_monto")?.addEventListener('click', (e) => {
        e.preventDefault();
        intercambiarMoneda('monto', 'monto_cambio');
    });

    // Limpieza de Modales (al cerrarse) 
    document.getElementById("modal_registro_gastos")?.addEventListener("hide.bs.modal", limpiarModalRegistroGastos);
    document.getElementById("modal_inicializar_caja")?.addEventListener("hide.bs.modal", limpiarModalInicializarCaja);

    // Edición Inline de Descripción
    document.getElementById("btn_activar_edicion")?.addEventListener('click', activarEdicionDescripcion);
    document.getElementById("btn_cancelar_edicion")?.addEventListener('click', cancelarEdicionDescripcion);
    document.getElementById("btn_guardar_edicion")?.addEventListener('click', modificarObservacionInline);

    // Preparación de Modales (al abrirse)
    document.getElementById("modal_reponer_caja")?.addEventListener("show.bs.modal", prepararModalReposicion);
    document.getElementById("modal_revaluar_fondo")?.addEventListener("show.bs.modal", prepararModalRevaluacion);

    // --- Inputs Reactivos (Cálculos en vivo) ---
    document.getElementById("limite_ucau")?.addEventListener("input", calcularNuevoFondo);
    document.getElementById("ucau_valor_bs")?.addEventListener("input", calcularNuevoFondo);
    document.getElementById("limite_ucau_inicial")?.addEventListener("input", calcularFondoInicial);
    document.getElementById("ucau_valor_inicial")?.addEventListener("input", calcularFondoInicial);

    // Botón de Inicializar Caja 
    document.getElementById("boton_inicializar_caja").addEventListener("click", procesarInicializacionCaja);
}

// EVENTOS DE LA CAJA
function manejarCambioCaja(e) {
    e.target.classList.remove('caja-highlight');
    let id_caja = e.target.value;
    let option = e.target.options[e.target.selectedIndex];

    // 1. Actualizar Fondo
    let saldoActual = parseFloat(option.getAttribute("saldo_actual")) || 0;
    let spanFondo = document.getElementById("span_fondo_fijo");
    spanFondo.classList.remove("placeholder-glow");
    spanFondo.textContent = `${saldoActual.toFixed(2)} Bs. / ${(saldoActual / tasa_dolar).toFixed(2)} $`;

    // 2. Actualizar Observaciones
    let descripcionActual = descripciones[id_caja] || '';
    let pDescripciones = document.getElementById("descripciones");
    pDescripciones.classList.remove('placeholder-glow');
    
    if (descripcionActual.trim() === '') {
        pDescripciones.innerHTML = '<span class="text-muted fst-italic">Sin observaciones registradas para este mes.</span>';
    } else {
        pDescripciones.textContent = descripcionActual;
    }

    // Actualizar Info UCAU
    let limiteUcau = option.getAttribute("limite_ucau") || 0;
    let ucauBs = option.getAttribute("ucau_valor_bs") || 0;
    document.getElementById("span_info_ucau").innerHTML = `<i class="bi bi-info-circle"></i> Límite: ${limiteUcau} UCAU (Ref: ${ucauBs} Bs/UCAU)`;
    
    document.getElementById("btn_activar_edicion").classList.remove("d-none");
    document.getElementById("modo_edicion_nota").classList.add("d-none");
    document.getElementById("modo_lectura_nota").classList.remove("d-none");

    actualizarSaldos();
    
    // Actualizar Estado de la Caja
    const spanCajaActiva = document.getElementById("span_caja_activa");
    spanCajaActiva.classList.remove("d-flex", "align-items-center");
    
    if (option.getAttribute("activa") === "CERRADO") {
        document.getElementById("botones_movimientos")?.setAttribute("hidden", "");
        spanCajaActiva.className = "badge badge-soft-danger rounded-pill fs-6 px-3 py-2 shadow-sm";
        spanCajaActiva.innerHTML = "<i class='bi bi-lock-fill me-1'></i> Caja Cerrada";
    } else {
        document.getElementById("botones_movimientos")?.removeAttribute("hidden");
        spanCajaActiva.className = "badge badge-soft-success rounded-pill fs-6 px-3 py-2 shadow-sm";
        spanCajaActiva.innerHTML = "<i class='bi bi-unlock-fill me-1'></i> Caja Activa";
    }

    // 5. Recargar tabla
    if (tabla_movimientos) {
        tabla_movimientos.setData("", { 
            operacion: 'consultar_movimientos_caja',
            caja_chica_id: id_caja 
        });
    } else {
        inicializarTablaMovimientos();
    }
}

function limpiarModalRegistroGastos() {
    document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";
    document.getElementById("icono_titulo_modal_gasto").setAttribute("class", "bi bi-cart-check");
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Gasto';
    
    document.getElementById("form_registro_gasto").reset();
    document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja")?.textContent || '';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    let monto = document.getElementById("monto");
    if (monto && monto.getAttribute("monto") === "$") {
        intercambiarMoneda('monto', 'monto_cambio');
    }
    diferencia = 0;
}

function limpiarModalInicializarCaja() {
    document.getElementById("form_inicializar_caja").reset();
    document.querySelectorAll('#form_inicializar_caja .is-valid, #form_inicializar_caja .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
}

function prepararModalReposicion() {
    let select = document.getElementById("mes_select");
    let option = select.options[select.selectedIndex];
    let saldoActual = parseFloat(option.getAttribute("saldo_actual")) || 0;
    let saldoInicial = parseFloat(option.getAttribute("saldo_inicial")) || 0;
    let gastado = saldoInicial - saldoActual;

    document.getElementById("fondos_gastados_reponer").textContent = `${gastado.toFixed(2)} Bs.`;
    document.getElementById("fondos_gastados_reponer_usd").textContent = `Ref: ${(gastado / tasa_dolar).toFixed(2)} $`;
}

function prepararModalRevaluacion() {
    let select = document.getElementById("mes_select");
    let option = select.options[select.selectedIndex];
    
    let limiteActual = option.getAttribute("limite_ucau") || 0;
    let ucauBsActual = option.getAttribute("ucau_valor_bs") || 0;
    let logActual = option.getAttribute("revaluaciones_log") || 'Sin historial de revaluaciones.';

    document.getElementById("limite_ucau").value = limiteActual;
    document.getElementById("ucau_valor_bs").value = ucauBsActual;
    document.getElementById("revaluaciones_log_text").value = logActual;
    
    calcularNuevoFondo();
}

// Edición inline de descripción
function activarEdicionDescripcion() {
    let id_caja = document.getElementById("mes_select").value;
    document.getElementById("descripcion_input_inline").value = descripciones[id_caja] || '';
    
    document.getElementById("modo_lectura_nota").classList.add("d-none");
    document.getElementById("btn_activar_edicion").classList.add("d-none");
    document.getElementById("modo_edicion_nota").classList.remove("d-none");
    document.getElementById("descripcion_input_inline").focus();
}

function cancelarEdicionDescripcion() {
    document.getElementById("modo_edicion_nota").classList.add("d-none");
    document.getElementById("modo_lectura_nota").classList.remove("d-none");
    document.getElementById("btn_activar_edicion").classList.remove("d-none");
}

// AJAX
async function consultarCajasChicas() {
    let datos = new FormData();
    datos.append("operacion", "consultar_cajas_chicas");

    let respuesta = await Peticiones.enviar(datos);
    
    if (!respuesta.datos || respuesta.datos.length === 0) {
        document.getElementById("span_caja_activa").className = "badge badge-soft-warning rounded-pill fs-6 px-3 py-2 shadow-sm";
        document.getElementById("span_caja_activa").innerHTML = "<i class='bi bi-exclamation-triangle-fill me-1'></i> Sistema de Caja Chica Inactivo";
        
        document.getElementById("span_fondo_fijo").innerHTML = `
            <button class="btn btn-warning btn-sm shadow-sm" onclick="abrirModalFondoInicial()">
                <i class="bi bi-safe me-1"></i> Establecer Fondo Inicial
            </button>
            <p class="text-muted mt-2 small" style="font-size: 0.75rem; font-weight: normal;">No existe ninguna caja activa. Debe definir el monto base para iniciar.</p>
        `;
        
        document.getElementById("mes_select").innerHTML = '<option selected disabled>Sistema en blanco</option>';
        document.getElementById("mes_select").setAttribute('disabled','');
        document.getElementById("botones_movimientos")?.classList.add("d-none");
        return;
    }

    document.getElementById("botones_movimientos")?.classList.remove("d-none");

    Validador.procesarRespuesta(respuesta, () => {
        let select = document.getElementById("mes_select");
        let fragment = document.createDocumentFragment();
        select.innerHTML = '';

        respuesta.datos.forEach(caja => {
            let option = document.createElement("option");
            option.value = caja.id_caja_chica;
            let [anio, mes] = caja.fecha_creacion.split('-');
            option.textContent = `${FormatoFechas.nombreMes(parseInt(mes, 10))} del ${anio}`;

            // Atributos para UI
            option.setAttribute("saldo_actual", caja.saldo_calculado || caja.fondo_fijo);
            option.setAttribute("saldo_inicial", caja.fondo_fijo);
            option.setAttribute("activa", caja.estado);
            option.setAttribute("limite_ucau", caja.limite_ucau || 0);
            option.setAttribute("ucau_valor_bs", caja.ucau_valor_bs || 0);
            option.setAttribute("revaluaciones_log", caja.revaluaciones_log || '');
            
            fragment.appendChild(option);
            descripciones[caja.id_caja_chica] = caja.descripcion || '';
        });
        select.appendChild(fragment);

        let idBuscar = Notificaciones.obtenerIdBusqueda();
        let opcionExiste = idBuscar ? Array.from(select.options).some(opt => opt.value === String(idBuscar)) : false;

        if (idBuscar && opcionExiste) {
            Notificaciones.resaltarEnSelect('mes_select');
        } else {
            if (idBuscar && !opcionExiste) {
                Notificaciones.mostrarToast('error', 'No encontrado', 'La caja notificada no existe.');
                Notificaciones.limpiarUrl();
            }
            if (select.value) select.dispatchEvent(new Event('change'));
        } 
    });
}

// Registrar Gasto
async function registrar() {
    let datos = new FormData();
    datos.append("fecha", document.getElementById("fecha").value);
    datos.append("concepto", document.getElementById("concepto").value);
    datos.append("caja_chica_id", document.getElementById("mes_select").value);

    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio").value;
    datos.append("monto", monto);
    datos.append("tasa_dolar", tasa_dolar);
    datos.append("operacion", "registrar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_registro_gastos.hide();
        consultarCajasChicas();
    });
}

// Modificar Gasto
async function prepararFormulario(id) {
    let datos = new FormData();
    datos.append("id_movimiento_caja", id);
    datos.append("operacion", "consultar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let mov = respuestaServidor.datos;
        document.getElementById("fecha").value = mov.fecha;
        document.getElementById("concepto").value = mov.concepto;
        document.getElementById("monto").value = mov.monto;

        const tasaMov = parseFloat(mov.tasa_dolar) || tasa_dolar || 1;
        document.getElementById("monto_cambio").value = (mov.monto / tasaMov).toFixed(2);

        if (permisoModificar != 1) {
            boton_formulario.setAttribute("hide", true);
            boton_formulario.setAttribute("disabled", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", mov.id_movimiento_caja);
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal_registro_gasto').textContent = "Modificar Gasto de Caja";
        document.getElementById("icono_titulo_modal_gasto").setAttribute("class", "bi bi-cart-dash");

        diferencia = parseFloat(mov.monto);
        modal_registro_gastos.show();
    });
}

async function modificar(id) {
    let datos = new FormData();
    datos.append("fecha", document.getElementById("fecha").value);
    datos.append("concepto", document.getElementById("concepto").value);
    datos.append("id_movimiento_caja", id);

    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio").value;
    datos.append("monto", monto);
    datos.append("tasa_dolar", tasa_dolar);
    datos.append("operacion", "modificar_movimiento"); 

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_registro_gastos.hide();
        consultarCajasChicas();
    });
}

// Eliminar Gasto
function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Movimiento?",
        "Esta acción no se puede deshacer.",
        "error",
        async () => { 
            let datos = new FormData();
            datos.append("id_movimiento_caja", id);
            datos.append("operacion", "eliminar_movimiento");
            
            let respuesta = await Peticiones.enviar(datos);
            Validador.procesarRespuesta(respuesta, () => {
                consultarCajasChicas();
            });
        }
    );
}

// Reponer Caja
async function reponerCaja() {
    let datos = new FormData();
    datos.append("caja_chica_id", document.getElementById("mes_select").value);
    datos.append("operacion", "reponer_caja");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_reposicion_caja.hide();
        consultarCajasChicas();
    });
}

// Revaluar Fondo
async function revaluarFondo() {
    let limite = document.getElementById("limite_ucau").value;
    let ucau = document.getElementById("ucau_valor_bs").value;
    let nota = document.getElementById("nota_revaluacion").value;

    if (!limite || !ucau || parseFloat(limite) <= 0 || parseFloat(ucau) <= 0) {
        Alertas.mostrar('error', 'Faltan datos', 'Debe ingresar un límite y un valor de UCAU válidos.');
        return;
    }

    Alertas.confirmarAccion(
        "¿Revaluar Fondo?",
        "El fondo de la caja se actualizará. Asegúrese de que el efectivo físico coincida con el nuevo monto.",
        "question",
        async () => {
            let datos = new FormData();
            datos.append("id_caja_chica", document.getElementById("mes_select").value);
            datos.append("limite_ucau", limite);
            datos.append("ucau_valor_bs", ucau);
            datos.append("nota_revaluacion", nota);
            datos.append("operacion", "revaluar_fondo");

            let respuesta = await Peticiones.enviar(datos);
            Validador.procesarRespuesta(respuesta, () => {
                modal_revaluar_fondo.hide();
                document.getElementById("form_revaluar_fondo").reset();
                consultarCajasChicas();
            });
        }
    );
}

// Inicializar Caja
function procesarInicializacionCaja(e) {
    e.preventDefault();
    const formInicial = document.getElementById("form_inicializar_caja");
    
    if (parseFloat(document.getElementById("limite_ucau_inicial").value) <= 0 || 
        parseFloat(document.getElementById("ucau_valor_inicial").value) <= 0) {
        Alertas.mostrar('error', 'Faltan datos', 'Debe indicar la UCAU y su valor en Bs.');
        return;
    }

    Alertas.confirmarAccion(
        "¿Establecer Fondo Inicial?",
        "Esta acción inicializará el libro de caja chica para el periodo actual.",
        "warning",
        async () => {
            let datos = new FormData(formInicial);
            datos.append("operacion", "registrar_caja_chica");

            let respuesta = await Peticiones.enviar(datos, "", true);
            Validador.procesarRespuesta(respuesta, () => {
                modal_inicializar_caja.hide();
                consultarCajasChicas(); 
            });
        }
    );
}

// Modificar Descripción Inline
async function modificarObservacionInline() {
    let id_caja = document.getElementById("mes_select").value;
    let descripcionNueva = document.getElementById("descripcion_input_inline").value;

    let btnGuardar = document.getElementById("btn_guardar_edicion");
    let textoOriginal = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

    let datos = new FormData();
    datos.append("caja_chica_id", id_caja);
    datos.append("descripcion", descripcionNueva);
    datos.append("operacion", "modificar_descripcion");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        descripciones[id_caja] = descripcionNueva;
        
        let pDescripciones = document.getElementById("descripciones");
        if (descripcionNueva.trim() === '') {
            pDescripciones.innerHTML = '<span class="text-muted fst-italic">Sin observaciones registradas para este mes.</span>';
        } else {
            pDescripciones.textContent = descripcionNueva;
        }
        cancelarEdicionDescripcion();
    });
    
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = textoOriginal;
}

// FUNCIONES UTILITARIAS Y DE UI
function actualizarSaldos() {
    let select = document.getElementById("mes_select");
    if (!select || select.selectedIndex === -1) return;
    
    let option = select.options[select.selectedIndex];
    if (!option) return;

    let saldoActual = parseFloat(option.getAttribute("saldo_actual")) || 0;
    let saldoInicial = parseFloat(option.getAttribute("saldo_inicial")) || 0;
    let gastado = saldoInicial - saldoActual;

    let textoBs = `${saldoActual.toFixed(2)} Bs. / ${(saldoActual / tasa_dolar).toFixed(2)} $`;
    
    // Actualizar solo los elementos que existen en el modal de registro de gastos
    const elFondosCaja = document.getElementById("fondos_caja");
    const elFondosRestante = document.getElementById("fondos_restante");
    
    if (elFondosCaja) elFondosCaja.textContent = textoBs;
    if (elFondosRestante) elFondosRestante.textContent = textoBs;
}

function calcularNuevoFondo() {
    let limite = parseFloat(document.getElementById("limite_ucau").value) || 0;
    let ucau = parseFloat(document.getElementById("ucau_valor_bs").value) || 0;
    let total = limite * ucau;
    document.getElementById("nuevo_fondo_calculado").textContent = total.toFixed(2) + ' Bs.';
}

function calcularFondoInicial() {
    let limite = parseFloat(document.getElementById("limite_ucau_inicial").value) || 0;
    let ucau = parseFloat(document.getElementById("ucau_valor_inicial").value) || 0;
    let total = limite * ucau;
    
    document.getElementById("fondo_inicial_calculado").textContent = total.toFixed(2) + ' Bs.';
    document.getElementById("fondo_fijo_inicial_hidden").value = total.toFixed(2);
}

function abrirModalFondoInicial() {
    const formInicial = document.getElementById("form_inicializar_caja");
    formInicial.reset();
    formInicial.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    modal_inicializar_caja.show();
}

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

function mostrarVistaPrevia(data) {
    let montoBs = parseFloat(data.monto);
    const tasaVp = parseFloat(data.tasa_dolar) || tasa_dolar || 1;
    
    document.getElementById("vp_monto_bs").textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_monto_usd").textContent = `Ref: ${(montoBs / tasaVp).toFixed(2)} $`;
    document.getElementById("vp_concepto").textContent = data.concepto || 'Sin descripción';
    document.getElementById("vp_fecha").textContent = FormatoFechas.formatear(data.fecha, 'DD-MM-YYYY');

    const config = obtenerConfigEstadoCaja(data.estado);
    const estadoEl = document.getElementById("vp_estado");
    estadoEl.innerHTML = ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    
    if (data.estado === 'REPUESTO') estadoEl.className = "fw-bold text-end text-success";
    else if (data.estado === 'PENDIENTE') estadoEl.className = "fw-bold text-end text-warning text-dark";
    else estadoEl.className = "fw-bold text-end text-secondary";

    modalDetalles.show();
}

function inicializarTablaMovimientos() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoFecha = (cell) => FormatoFechas.formatear(cell.getValue(), 'DD/MM/YYYY');
    const formatoMonto = (cell) => {
        const row = cell.getData();
        const tasaHistorica = parseFloat(row.tasa_dolar) || tasa_dolar || 1;
        return `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / tasaHistorica).toFixed(2)} $`;
    };
    const formatoEstado = (cell) => {
        const config = obtenerConfigEstadoCaja(cell.getValue());
        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    };
    
    const formatoBotones = (cell) => {
        const row = cell.getData();
        const selectCaja = document.getElementById("mes_select");
        const estadoCaja = selectCaja.options[selectCaja.selectedIndex].getAttribute("activa");
        
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i><span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
            
        if (estadoCaja.toUpperCase() !== 'CERRADO' && row.estado.toUpperCase() !== 'REPUESTO') {
            if (permisoModificar) {
                html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar gasto">
                            <i class="bi bi-pencil"></i><span class="d-none d-lg-inline ms-2">Editar</span>
                        </button>`;
            }
            if (permisoEliminar) {
                html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Anular gasto">
                            <i class="bi bi-trash"></i><span class="d-none d-lg-inline ms-2">Borrar</span>
                        </button>`;
            }
        }
        return html + `</div>`;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 170, widthGrow: 3 },
        { title: "Fecha", field: "fecha", formatter: formatoFecha, minWidth: 130, responsive: 0 },
        { title: "Monto", field: "monto", formatter: formatoMonto, minWidth: 160 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false, headerHozAlign: "center", widthGrow: 5,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_movimiento_caja;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];

    tabla_movimientos = Tablas.cargarTabulador(contenedor.id, "", columnas, {
        parametrosExtra: { 
            operacion: 'consultar_movimientos_caja',
            caja_chica_id: document.getElementById("mes_select").value 
        }
    });

    Tablas.inicializarBuscadorGlobal(tabla_movimientos, "busqueda_global", columnas);
}

function obtenerConfigEstadoCaja(valor) {
    let est = valor || "";
    let color = "secondary", icono = "bi-circle", textoVisual = est;

    if (est === "PENDIENTE") {
        color = "warning"; icono = "bi-arrow-clockwise"; textoVisual = "Por Reponer"; 
    } else if (est === "REPUESTO") {
        color = "success"; icono = "bi-check-circle-fill"; textoVisual = "Repuesto";
    }
    return { color, icono, texto: textoVisual };
}

function envio(operacion) {
    if (operacion === "modificar") modificar(boton_formulario.getAttribute("id_modificar"));
    else if (operacion === "Registrar") registrar();
    else if (operacion === "reponer_caja") reponerCaja();
    else if (operacion === "revaluar_fondo") revaluarFondo();
    else Alertas.mostrar('error', 'Atención', 'Operación no válida');
}

function configurarAyudaInteractiva() {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Módulo de Caja Chica', description: 'Bienvenido. Aquí puedes administrar los fondos menores del condominio y registrar sus movimientos.', side: "bottom", align: 'center' } },
        { element: '#mes_select', popover: { title: 'Selector de Caja', description: 'Elige el mes/caja que deseas evaluar. Verás automáticamente el fondo fijo y si la caja está activa o cerrada.', side: "bottom", align: 'start' } },
        { element: '#botones_movimientos', popover: { title: 'Acciones de Caja', description: 'Si la caja está activa, aquí podrás Registrar un Nuevo Gasto o Reponer el dinero de la caja.', side: "bottom", align: 'start' } },
        { element: '#tabla_registros_sistema', popover: { title: 'Movimientos Registrados', description: 'Aquí se listan todos los gastos o reposiciones hechas en esta caja. Puedes editar o eliminar los registros.', side: "top", align: 'center' } },
        { element: '#btn_activar_edicion', popover: { title: 'Descripción de Caja', description: 'Puedes añadir o editar una nota o descripción general para el mes de esta caja chica.', side: "top", align: 'start' } }
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
}