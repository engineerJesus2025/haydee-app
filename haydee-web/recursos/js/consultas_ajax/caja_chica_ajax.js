let peticionesActivas = 0; // Se mantiene para el control manual de modales
let tasa_dolar = localStorage.getItem("tasa_dolar") || 0;
let diferencia = 0;

let modal_carga = new bootstrap.Modal("#modal_carga", { focus: false });
let modal_registro_gastos = new bootstrap.Modal("#modal_registro_gastos", { focus: false });
let modal_reposicion_caja = new bootstrap.Modal("#modal_reponer_caja");
let modal_inicializar_caja = new bootstrap.Modal("#modal_inicializar_caja", { focus: false });
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

    // FONDO ACTUAL
    let saldoActual = option.getAttribute("saldo_actual") || 0;
    let spanFondo = document.getElementById("span_fondo_fijo");
    spanFondo.classList.remove("placeholder-glow"); // Apagamos la animación
    spanFondo.textContent = `${saldoActual} Bs. / ${(saldoActual / tasa_dolar).toFixed(2)} $`;

    // OBSERVACIONES
    let descripcionActual = descripciones[id_caja] || '';
    let pDescripciones = document.getElementById("descripciones");
    
    pDescripciones.classList.remove('placeholder-glow'); // Apagamos la animación
    
    if (descripcionActual.trim() === '') {
        pDescripciones.innerHTML = '<span class="text-muted fst-italic">Sin observaciones registradas para este mes.</span>';
    } else {
        pDescripciones.textContent = descripcionActual;
    }
    
    // Mostramos el botón de editar que estaba oculto durante el skeleton
    document.getElementById("btn_activar_edicion").classList.remove("d-none");
    
    // Asegurarnos de que siempre empiece en modo lectura al cambiar de mes
    document.getElementById("modo_edicion_nota").classList.add("d-none");
    document.getElementById("modo_lectura_nota").classList.remove("d-none");

    actualizarSaldos();
    
    // --- ESTADO DE LA CAJA ---
    const spanCajaActiva = document.getElementById("span_caja_activa");
    spanCajaActiva.classList.remove("d-flex", "align-items-center"); // Quitamos las clases del spinner
    
    if (option.getAttribute("activa") === "Cerrada") {
        document.getElementById("botones_movimientos")?.setAttribute("hidden", "");
        spanCajaActiva.className = "badge badge-soft-danger rounded-pill fs-6 px-3 py-2 shadow-sm";
        spanCajaActiva.innerHTML = "<i class='bi bi-lock-fill me-1'></i> Caja Cerrada";
    } else {
        document.getElementById("botones_movimientos")?.removeAttribute("hidden");
        spanCajaActiva.className = "badge badge-soft-success rounded-pill fs-6 px-3 py-2 shadow-sm";
        spanCajaActiva.innerHTML = "<i class='bi bi-unlock-fill me-1'></i> Caja Activa";
    }

    // Recargar tabla de movimientos
    if (tabla_movimientos) {
        const parametrosExtra = { 
            operacion: 'consultar_movimientos_caja',
            caja_chica_id: id_caja 
        }
        tabla_movimientos.setData("", parametrosExtra);
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

// Limpiar modal al cerrar
document.getElementById("modal_registro_gastos")?.addEventListener("hide.bs.modal", () => {
    document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";
    document.getElementById("icono_titulo_modal_gasto").setAttribute("class","bi bi-cart-check");
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    // boton_formulario.textContent = "Registrar";
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Gasto';
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

// Activar modo edición
document.getElementById("btn_activar_edicion")?.addEventListener('click', () => {
    let id_caja = document.getElementById("mes_select").value;
    document.getElementById("descripcion_input_inline").value = descripciones[id_caja] || '';
    
    document.getElementById("modo_lectura_nota").classList.add("d-none");
    document.getElementById("btn_activar_edicion").classList.add("d-none");
    document.getElementById("modo_edicion_nota").classList.remove("d-none");
    document.getElementById("descripcion_input_inline").focus();
});

// Cancelar edición
document.getElementById("btn_cancelar_edicion")?.addEventListener('click', () => {
    document.getElementById("modo_edicion_nota").classList.add("d-none");
    document.getElementById("modo_lectura_nota").classList.remove("d-none");
    document.getElementById("btn_activar_edicion").classList.remove("d-none");
});

document.getElementById("btn_guardar_edicion")?.addEventListener('click', modificarObservacionInline);

document.getElementById("boton_intercambio_monto_inicial")?.addEventListener('click', (e) => {
    e.preventDefault();
    intercambiarMoneda('fondo_fijo_inicial', 'fondo_fijo_inicial_cambio'); //
});

document.getElementById("modal_inicializar_caja")?.addEventListener("hide.bs.modal", () => {
    document.getElementById("form_inicializar_caja").reset();
    document.querySelectorAll('#form_inicializar_caja .is-valid, #form_inicializar_caja .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    
    let fondoInput = document.getElementById("fondo_fijo_inicial");
    if (fondoInput && fondoInput.getAttribute("monto") === "$") {
        intercambiarMoneda('fondo_fijo_inicial', 'fondo_fijo_inicial_cambio'); //
    }
});

document.getElementById("boton_inicializar_caja").addEventListener("click", async function(e) {
    e.preventDefault();
    
    const formInicial = document.getElementById("form_inicializar_caja");
    const inputFondo = document.getElementById("fondo_fijo_inicial");

    if (!inputFondo.value || parseFloat(inputFondo.value) <= 0) {
        EstadoInputs.marcarError(inputFondo, 'El monto inicial es obligatorio y debe ser mayor a 0');
        Alertas.mostrar('error', 'Monto Requerido', 'El monto del fondo inicial es obligatorio y debe ser mayor a cero.');
        return;
    }

    if (!Validador.evaluarInput(inputFondo, Patrones.monto, "Máximo 12 enteros y 2 decimales")) {
        Alertas.mostrar('error', 'Formato de Monto Inválido', 'El monto ingresado no cumple con el formato permitido (Ej: 1500.50). Máximo 2 decimales.');
        return;
    }

    Alertas.confirmarAccion(
        "¿Establecer Fondo Inicial?",
        "Esta acción inicializará el libro de caja chica para el periodo actual.",
        "warning",
        async () => {
            let datos = new FormData(formInicial);
            datos.append("operacion", "registrar_caja_chica");

            // --- REGLA DE SOBERANÍA MONETARIA ---
            // Si el input principal está en '$', significa que los Bolívares se movieron al input de cambio.
            // Extraemos el valor correcto para que la Base de Datos siempre reciba Bolívares.
            let fondoRealBs = (inputFondo.getAttribute("monto") === "bs") 
                ? inputFondo.value 
                : document.getElementById("fondo_fijo_inicial_cambio").value;
                
            datos.set("fondo_fijo", fondoRealBs); // Sobrescribimos el parámetro de forma segura

            let respuesta = await Peticiones.enviar(datos, "", true);
            Validador.procesarRespuesta(respuesta, () => {
                modal_inicializar_caja.hide();
                consultarCajasChicas(); //
            });
        }
    );
});

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

//  CONSULTA DE CAJAS CHICAS 
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
            <p class="text-muted mt-2 small" style="font-size: 0.75rem; font-weight: normal;">No existe ninguna caja activa. Debe definir el monto base para iniciar las operaciones contables.</p>
        `;
        
        document.getElementById("mes_select").innerHTML = '<option selected disabled>Sistema en blanco</option>';
        document.getElementById("mes_select").setAttribute('disabled','');
        
        const contenedorBotones = document.getElementById("botones_movimientos");
        if (contenedorBotones) {
            contenedorBotones.classList.add("d-none");
        }
        return;
    }

    const contenedorBotones = document.getElementById("botones_movimientos");
    if (contenedorBotones) {
        contenedorBotones.classList.remove("d-none");
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
    let montoBs = parseFloat(data.monto);
    const tasaVp = parseFloat(data.tasa_dolar) || parseFloat(tasa_dolar) || 1;
    let montoUsd = montoBs / tasaVp;

    document.getElementById("vp_monto_bs").textContent = `${montoBs.toFixed(2)} Bs.`;
    document.getElementById("vp_monto_usd").textContent = `Ref: ${montoUsd.toFixed(2)} $`;

    // Concepto
    document.getElementById("vp_concepto").textContent = data.concepto || 'Sin descripción';

    // Fecha 
    document.getElementById("vp_fecha").textContent = FormatoFechas.formatear(data.fecha, 'DD-MM-YYYY');

    // Estado 
    const config = obtenerConfigEstadoCaja(data.estado);
    const estadoEl = document.getElementById("vp_estado");
    
    estadoEl.innerHTML = ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    
    if (data.estado === 'REPUESTO') {
        estadoEl.className = "fw-bold text-end text-success";
    } else if (data.estado === 'PENDIENTE') {
        estadoEl.className = "fw-bold text-end text-warning text-dark";
    } else {
        estadoEl.className = "fw-bold text-end text-secondary";
    }

    // Mostramos el modal
    modalDetalles.show();
}

// INICIALIZAR TABLA DE MOVIMIENTOS
function inicializarTablaMovimientos() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoFecha = (cell) => FormatoFechas.formatear(cell.getValue(), 'DD/MM/YYYY');
    const formatoMonto = (cell) => {
    const row = cell.getData();
        // Usamos la tasa de la BD. Si no existe, recurre a la global o a 1
        const tasaHistorica = parseFloat(row.tasa_dolar) || parseFloat(tasa_dolar) || 1;
        return `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / tasaHistorica).toFixed(2)} $`;
    };
    const formatoEstado = (cell) => {
        const config = obtenerConfigEstadoCaja(cell.getValue());

        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    }
    
    const formatoBotones = (cell) => {
        const row = cell.getData();
        const selectCaja = document.getElementById("mes_select");
        const estadoCaja = selectCaja.options[selectCaja.selectedIndex].getAttribute("activa");
        
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
            
        // Solo si tiene permiso, la caja está ABIERTA y el movimiento NO está REPUESTO
        if (estadoCaja.toUpperCase() !== 'CERRADO' && row.estado.toUpperCase() !== 'REPUESTO') {
            if (permisoModificar) {
                html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar gasto">
                            <i class="bi bi-pencil"></i>
                            <span class="d-none d-lg-inline ms-2">Editar</span>
                        </button>`;
            }
            if (permisoEliminar) {
                html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Anular gasto">
                            <i class="bi bi-trash"></i>
                            <span class="d-none d-lg-inline ms-2">Borrar</span>
                        </button>`;
            }
        }
        
        html += `</div>`;
        return html;
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
    datos.append("tasa_dolar", tasa_dolar);
    datos.append("operacion", "registrar_movimiento");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        actualizarSaldos();
        modal_registro_gastos.hide();
        tabla_movimientos.replaceData();
    });
}

// ========== PREPARAR FORMULARIO PARA EDICIÓN ==========
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

        // Cálculo con tasa histórica del movimiento
        const tasaMov = parseFloat(mov.tasa_dolar) || parseFloat(tasa_dolar) || 1;
        document.getElementById("monto_cambio").value = (mov.monto / tasaMov).toFixed(2);

        if (permisoModificar != 1) {
            boton_formulario.setAttribute("hide", true);
            boton_formulario.setAttribute("disabled", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", mov.id_movimiento_caja);
        // boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal_registro_gasto').textContent = "Modificar Gasto de Caja";
        document.getElementById("icono_titulo_modal_gasto").setAttribute("class","bi bi-cart-dash");

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
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio").value;
    datos.append("monto", monto);
    datos.append("tasa_dolar", tasa_dolar);
    datos.append("operacion", "modificar_movimiento"); 

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal_registro_gastos.hide();
        tabla_movimientos.replaceData();
    });
}

// ========== ELIMINAR GASTO ==========
function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Movimiento?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id); }
    );
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

// ========== MODIFICAR DESCRIPCIÓN DE CAJA ==========
async function modificarObservacionInline() {
    let id_caja = document.getElementById("mes_select").value;
    let descripcionNueva = document.getElementById("descripcion_input_inline").value;

    // Deshabilitar botón y mostrar un pequeño spinner de carga nativo de Bootstrap
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
        // Actualizar el diccionario local para que no se pierda al cambiar de mes
        descripciones[id_caja] = descripcionNueva;
        
        // Actualizar la vista 
        let pDescripciones = document.getElementById("descripciones");
        if (descripcionNueva.trim() === '') {
            pDescripciones.innerHTML = '<span class="text-muted fst-italic">Sin observaciones registradas para este mes.</span>';
        } else {
            pDescripciones.textContent = descripcionNueva;
        }

        // Ocultar inputs y volver al modo lectura
        document.getElementById("modo_edicion_nota").classList.add("d-none");
        document.getElementById("modo_lectura_nota").classList.remove("d-none");
        document.getElementById("btn_activar_edicion").classList.remove("d-none");
    });
    
    // Restaurar el botón a su estado original
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = textoOriginal;
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


/**
 * Procesa el estado de un movimiento de caja y devuelve su configuración visual
 */
function obtenerConfigEstadoCaja(valor) {
    let est = valor || "";
    // Evaluamos contra los nuevos valores del Enum (Mayúsculas)
    let color = "secondary";
    let icono = "bi-circle";
    let textoVisual = est;

    if (est === "PENDIENTE") {
        color = "warning";
        icono = "bi-arrow-clockwise";
        textoVisual = "Por Reponer"; 
    } else if (est === "REPUESTO") {
        color = "success";
        icono = "bi-check-circle-fill";
        textoVisual = "Repuesto";
    }

    return { color, icono, texto: textoVisual };
}

function abrirModalFondoInicial() {
    const formInicial = document.getElementById("form_inicializar_caja");
    formInicial.reset();
    formInicial.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    
    modal_inicializar_caja.show();
}

// MÓDULO DE AYUDA INTERACTIVA
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
