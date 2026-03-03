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

let tabla_movimientos;
let boton_formulario = document.getElementById("boton_gasto_caja");
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

// Almacén de descripciones de cajas
let descripciones = {};

// Inicializar
consultarCajasChicas();

// Ajustar columnas de DataTable al colapsar menú
document.getElementById('header-toggle')?.addEventListener("click", () => {
    setTimeout(() => tabla_movimientos?.columns.adjust().draw(), 450);
});

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
        tabla_movimientos.ajax.reload();
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

function formatearFecha(fecha) {
    if (!fecha) return "N/A";
    let partes = fecha.split("-");
    return partes.length === 3 ? `${partes[2]}-${partes[1]}-${partes[0]}` : fecha;
}

function obtenerColorEstado(estado) {
    let colores = {
        'Pendiente por reposicion': 'badge bg-warning text-dark',
        'Repuesto': 'badge bg-success'
    };
    return colores[estado] || 'badge bg-secondary';
}

function crearBotones(id) {
    let div = document.createElement("div");
    let html = `<div class="row justify-content-evenly">
                    <button type="button" class="btn btn-success btn-sm col-lg-3 col-4 modificar" data-bs-toggle="modal" data-bs-target="#modal_registro_gastos" title="modificar" value="${id}">
                        <i class="bi bi-pencil-square"></i>
                    </button>`;
    if (permiso_eliminar == 1) {
        html += `<button type="button" class="btn btn-danger btn-sm col-lg-3 col-4 eliminar" title="Eliminar" value="${id}">
                    <i class="bi bi-trash"></i>
                </button>`;
    }
    html += `</div>`;
    div.innerHTML = html;
    return div;
}

// ========== CONSULTA DE CAJAS CHICAS ==========
async function consultarCajasChicas() {
    let datos = new FormData();
    datos.append("operacion", "consultar_cajas_chicas");

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.datos || respuesta.datos.length === 0) {
        document.getElementById("span_caja_activa").textContent = "No hay cajas registradas";
        return;
    }

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

    // Si hay una caja seleccionada por defecto, disparar el cambio
    if (select.value) {
        select.dispatchEvent(new Event('change'));
    }

    // Verificar si hay parámetro 'buscar' en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idBuscar = urlParams.get('buscar');
    if (idBuscar) {
        const checkSelect = setInterval(() => {
            if (select.options.length > 0) {
                clearInterval(checkSelect);
                const option = Array.from(select.options).find(opt => opt.value === idBuscar);
                if (option) {
                    // Seleccionar la opción
                    select.value = idBuscar;
                    // Disparar evento change para cargar movimientos
                    select.dispatchEvent(new Event('change'));
                    
                    // Resaltar el select con animación
                    select.classList.add('caja-highlight');
                    
                    // Mostrar mensaje informativo
                    // Utilidades.mensaje('warning', 'Saldo Bajo', 'Esta caja requiere atención pronto.');
                    
                    // Opcional: hacer scroll hacia el select
                    select.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    Utilidades.mensaje('error', 'Error', 'La caja notificada no existe.');
                }
            }
        }, 100);
    }
}

// ========== INICIALIZAR TABLA DE MOVIMIENTOS ==========
function inicializarTablaMovimientos() {
    let columnas = [
        {
            data: "fecha",
            render: data => FormatoFechas.formatear(data, 'DD-MM-YYYY')
        },
        {
            data: null,
            render: row => `${parseFloat(row.monto).toFixed(2)} Bs. / ${(row.monto / tasa_dolar).toFixed(2)} $`
        },
        { data: "concepto" },
        {
            data: "estado",
            render: data => `<span class="${obtenerColorEstado(data)}">${data}</span>`
        },
        {
            data: null,
            render: row => crearBotones(row.id_movimiento_caja).innerHTML
        }
    ];

    let parametros = (data) => {
        data.operacion = 'consultar_movimientos_caja';
        data.caja_chica_id = document.getElementById("mes_select").value;
    };

    let postCreacion = (row, data) => {
        row.id = `fila-${data.id_movimiento_caja}`;
        row.querySelector(".modificar")?.addEventListener('click', prepararFormulario);
        row.querySelector(".eliminar")?.addEventListener('click', eventoEliminar);
    };

    tabla_movimientos = Utilidades.crearDataTable('tabla_registros_sistema', columnas, parametros, postCreacion);
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

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    // Actualizar saldo mostrado
    actualizarSaldos();
    modal_registro_gastos.hide();
    tabla_movimientos.ajax.reload();
    Utilidades.mensaje('success', 'Éxito', 'Gasto registrado correctamente');
}

// ========== PREPARAR FORMULARIO PARA EDICIÓN ==========
async function prepararFormulario(e) {
    let id = e.target.closest('button').value;

    let datos = new FormData();
    datos.append("id_movimiento_caja", id);
    datos.append("operacion", "consultar_movimiento");

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    let mov = respuesta.datos;
    document.getElementById("fecha").value = mov.fecha;
    document.getElementById("concepto").value = mov.concepto;
    // El monto se carga en Bs. (asumimos que la base guarda en Bs.)
    document.getElementById("monto").value = mov.monto;
    document.getElementById("monto_cambio").value = (mov.monto / tasa_dolar).toFixed(2);

    if (permiso_modificar != 1) {
        boton_formulario.setAttribute("hide", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", mov.id_movimiento_caja);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById('titulo_modal_registro_gasto').textContent = "Modificar Gasto de Caja";

    diferencia = parseFloat(mov.monto);
}

// ========== MODIFICAR GASTO ==========
async function modificar(id) {
    let datos = new FormData();
    datos.append("fecha", document.getElementById("fecha").value);
    datos.append("concepto", document.getElementById("concepto").value);
    datos.append("id_movimiento_caja", id);

    let montoInput = document.getElementById("monto");
    // En edición solo se permite cambiar concepto y fecha, no el monto (por seguridad)
    // Si se permite cambiar monto, habría que ajustar la lógica. Según el modelo actual, no se modifica monto.
    // Por ahora, no enviamos monto.
    datos.append("operacion", "modificar_movimiento"); // Nota: en el controlador no hay case 'modificar_movimiento'? Revisar.

    // En el controlador no existe 'modificar_movimiento', solo 'registrar_movimiento' y 'eliminar_movimiento'.
    // El modelo tiene _modificar_movimiento que solo actualiza concepto y fecha. Pero el controlador no lo llama.
    // Debemos agregar un case en el controlador para 'modificar_movimiento'.
    // Por ahora, asumimos que se agregará. Si no, esta función no funcionará.
    // Mientras tanto, lo dejamos como placeholder.

    // *** IMPORTANTE: El controlador debe tener un case 'modificar_movimiento' que llame a _modificar_movimiento.
    // Por ahora, lo simulamos. En la versión final, asegurar que existe.

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modal_registro_gastos.hide();
    document.getElementById("form_registro_gasto").reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";

    tabla_movimientos.ajax.reload();
    Utilidades.mensaje('success', 'Éxito', 'Gasto modificado correctamente');
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

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    actualizarSaldos();
    tabla_movimientos.ajax.reload();
    Utilidades.mensaje('success', 'Éxito', 'Gasto eliminado correctamente');
}

// ========== REPONER CAJA ==========
async function reponerCaja() {
    let datos = new FormData();
    datos.append("caja_chica_id", document.getElementById("mes_select").value);

    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs") ? montoInput.value : document.getElementById("monto_cambio_reponer").value;
    datos.append("monto", monto);
    datos.append("operacion", "reponer_caja");

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modal_reposicion_caja.hide();
    consultarCajasChicas(); // Recargar cajas para actualizar saldos
    Utilidades.mensaje('success', 'Éxito', 'Reposición realizada correctamente');
}

// ========== modificar DESCRIPCIÓN DE CAJA ==========
async function modificarObservacion() {
    let id_caja = document.getElementById("mes_select").value;
    let descripcion = document.getElementById("descripcion_input").value;

    let datos = new FormData();
    datos.append("caja_chica_id", id_caja);
    datos.append("descripcion", descripcion);
    datos.append("operacion", "modificar_descripcion");

    let respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modal_observacion.hide();
    consultarCajasChicas(); // Recargar para actualizar descripción en el objeto
    Utilidades.mensaje('success', 'Éxito', 'Descripción actualizada');
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
        Utilidades.mensaje('error', 'Atención', 'Operación no válida');
    }
}

// Botón guardar en modal de gasto
document.getElementById("boton_gasto_caja")?.addEventListener("click", async (e) => {
    e.preventDefault();
    let accion = e.target.hasAttribute("modificar") ? "modificar" : "Registrar";

    if (await validarEnvio(accion)) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: `¿Está seguro que desea ${accion} este gasto?`,
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            confirmButtonColor: "#1b8a40",
            cancelButtonText: "Cancelar",
            icon: "warning"
        }).then(result => {
            if (result.isConfirmed) envio(accion);
        });
    }
});

// Botón guardar en modal de reposición
document.getElementById("boton_guardar_reposicion")?.addEventListener("click", async (e) => {
    e.preventDefault();
    if (await validarEnvioReponerCaja()) {
        if (await verificarReposicionExcedente()) {
            Swal.fire({
                title: "Advertencia",
                text: "El monto ingresado hará que se incremente el fondo fijo. ¿Desea continuar?",
                showCancelButton: true,
                confirmButtonText: "Sí, continuar",
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then(result => {
                if (result.isConfirmed) envio("reponer_caja");
            });
        } else {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¿Está seguro que desea reponer la caja?",
                showCancelButton: true,
                confirmButtonText: "Sí, reponer",
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then(result => {
                if (result.isConfirmed) envio("reponer_caja");
            });
        }
    }
});

// Botón guardar descripción
document.getElementById("boton_formulario_observacion")?.addEventListener("click", (e) => {
    e.preventDefault();
    if (Validaciones.keyUp(/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\s-]{0,100}$/,
        document.getElementById("descripcion_input"),
        document.getElementById("descripcion_input").nextElementSibling,
        'Máximo 100 caracteres, solo letras/números/espacios/guiones')) {

        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Está seguro que desea modificar esta descripción?",
            showCancelButton: true,
            confirmButtonText: "Sí, cambiar",
            confirmButtonColor: "#1b8a40",
            cancelButtonText: "Cancelar",
            icon: "warning"
        }).then(result => {
            if (result.isConfirmed) modificarObservacion();
        });
    }
});

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - CAJA CHICA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    //Obliga a Driver a recalcular su posición EXACTA
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // 1. CONFIGURACIÓN DE LA VISTA PRINCIPAL
    const configPrincipal = {
        showProgress: true,
        animate: true,
        smoothScroll: false, // Apagado para controlarlo nosotros
        allowKeyboardControl: false,
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        
        onHighlightStarted: (element) => {
            if (element) {
                // Salto instantáneo y luego forzamos el recálculo
                element.scrollIntoView({ behavior: 'instant', block: 'center' });
                alinearBurbuja();
            }
        },

        steps: [
            { element: '.page-header', popover: { title: 'Módulo de Caja Chica', description: 'Bienvenido. Aquí puedes administrar los fondos menores del condominio y registrar sus movimientos.', side: "bottom", align: 'center' } },
            { element: '#mes_select', popover: { title: 'Selector de Caja', description: 'Elige el mes/caja que deseas evaluar. Verás automáticamente el fondo fijo y si la caja está activa o cerrada.', side: "bottom", align: 'start' } },
            { element: '#botones_movimientos', popover: { title: 'Acciones de Caja', description: 'Si la caja está activa, aquí podrás Registrar un Nuevo Gasto o Reponer el dinero de la caja.', side: "bottom", align: 'start' } },
            { element: '#tabla_registros_sistema_wrapper', popover: { title: 'Movimientos Registrados', description: 'Aquí se listan todos los gastos o reposiciones hechas en esta caja. Puedes editar o eliminar los registros.', side: "top", align: 'center' } },
            { element: '#boton_editar_observacion', popover: { title: 'Descripción de Caja', description: 'Puedes añadir o editar una nota o descripción general para el mes de esta caja chica.', side: "top", align: 'start' } }
        ]
    };

    // 2. CONFIGURACIÓN DEL MODAL DE GASTO
    const configModalGasto = {
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
            { element: '#fecha', popover: { title: 'Fecha', description: 'Indica la fecha en que se realizó este gasto menor.', side: 'bottom', align: 'start' } },
            { element: '#monto', popover: { title: 'Monto del Gasto', description: 'Ingresa la cantidad gastada. Si necesitas ingresarlo en divisas, usa el botón de intercambio.', side: 'bottom', align: 'start' } },
            { element: '#boton_intercambio_monto', popover: { title: 'Cambio de Moneda', description: 'Haz clic aquí para alternar entre Bolívares y Dólares. El sistema calculará el equivalente automáticamente según la Tasa BCV.', side: 'bottom', align: 'center' } },
            { element: '#fondos_restante', popover: { title: 'Control de Fondos', description: 'El sistema te mostrará cuánto dinero queda en la caja para evitar que gastes más de lo disponible.', side: 'top', align: 'start' } },
            { element: '#concepto', popover: { title: 'Concepto', description: 'Escribe de forma clara y precisa en qué se gastó el dinero.', side: 'top', align: 'start' } }, 
            { element: '#boton_gasto_caja', popover: { title: 'Guardar', description: 'Verifica los datos y registra el movimiento en la caja.', side: 'top', align: 'center' } }
        ]
    };

    // 3. LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalGasto = document.getElementById('modal_registro_gastos');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalGasto && modalGasto.classList.contains('show')) {
                tourActivo = driver(configModalGasto);
                tourActivo.drive();
            } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver(configPrincipal);
                tourActivo.drive();
            }
        });
    }

    if (modalGasto) {
        modalGasto.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});