// ============================================================
// VARIABLES GLOBALES
// ============================================================
let tabla_pagos;
let modal = new bootstrap.Modal(document.getElementById("modal_pagos"), { focus: false });
let modalVistaPrevia = new bootstrap.Modal(document.getElementById("modal_vista_previa"));
let formulario_usar = document.getElementById("form_pagos");
let boton_formulario = document.getElementById("boton_formulario");

let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
    consultar();

    // Resetear modal al cerrar
    document.getElementById("modal_pagos").addEventListener("hide.bs.modal", resetModalPagos);

    // Evento para agregar nuevos bloques de detalles
    document.getElementById("agregar_detalle")?.addEventListener("click", agregarDetallePago);

    // Cambio de Apartamento (Busca mensualidades)
    document.getElementById("apartamento_id")?.addEventListener("change", cargarMensualidades);

    // Cambio de Mensualidad (Setea el monto)
    document.getElementById("mensualidad_id")?.addEventListener("change", function () {
        let seleccion = this.selectedOptions[0];
        document.getElementById("monto_mensualidad").value = seleccion.getAttribute("data-monto") || 0;
    });

    document.querySelectorAll(".tasa_dolar").forEach(input => input.value = tasa_dolar);

    // Delegación de eventos para calcular Dólares dinámicamente
    formulario_usar.addEventListener("input", calcularDolares);

    // Delegación de eventos para mostrar/ocultar campos según Método de Pago
    formulario_usar.addEventListener("change", mostrarCamposMetodoPago);

});


// ============================================================
// FUNCIONES AUXILIARES DE UI
// ============================================================
function resetModalPagos() {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Pago";
    document.getElementById("mensualidad_id").setAttribute("disabled", "true");
    document.getElementById("mensualidad_id").innerHTML = "<option selected hidden value=''>Escoja primero un Apartamento</option>";

    // Dejar solo el primer bloque de detalle
    const detallesContainer = document.getElementById("detalles_container");
    const bloques = detallesContainer.querySelectorAll(".detalle-pago");
    bloques.forEach((bloque, index) => {
        if (index > 0) bloque.remove();
    });

    // Limpiar el primer bloque
    const primerBloque = detallesContainer.querySelector(".detalle-pago");
    primerBloque.querySelectorAll(".campos-bancarios").forEach(c => c.classList.add("d-none"));
    primerBloque.querySelector(".nombre_imagen_cargada").textContent = "";
    const inputExistente = primerBloque.querySelector("input[name='imagen_existente[]']");
    if (inputExistente) inputExistente.remove();

    // Restablecer tasa
    document.querySelectorAll(".tasa_dolar").forEach(input => input.value = tasa_dolar);
}

function agregarDetallePago() {
    const container = document.getElementById('detalles_container');
    const plantilla = document.getElementById('plantilla-detalle-pago');
    const nuevoDetalle = plantilla.content.firstElementChild.cloneNode(true);

    nuevoDetalle.querySelectorAll('.tasa_dolar').forEach(input => input.value = tasa_dolar);

    // Botón de eliminar detalle
    const eliminarBtn = document.createElement('button');
    eliminarBtn.className = 'btn btn-sm btn-outline-danger mb-3';
    eliminarBtn.type = 'button';
    eliminarBtn.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar este Detalle';
    eliminarBtn.onclick = () => {
        nuevoDetalle.remove();
        renumerarDetalles();
    };
    nuevoDetalle.querySelector('.card-body').prepend(eliminarBtn);

    container.appendChild(nuevoDetalle);
    renumerarDetalles();
}

function renumerarDetalles() {
    const bloques = document.querySelectorAll('#detalles_container .detalle-pago');
    bloques.forEach((bloque, index) => {
        const header = bloque.querySelector('.card-header');
        header.innerHTML = `<i class="bi bi-receipt-cutoff me-2"></i> ${index + 1}) Detalles del Pago`;
    });
}

function calcularDolares(e) {
    if (e.target.matches(".monto") || e.target.matches(".tasa_dolar")) {
        const tarjeta = e.target.closest(".detalle-pago");
        const inputBs = tarjeta.querySelector(".monto");
        const inputTasa = tarjeta.querySelector(".tasa_dolar");
        const inputDolar = tarjeta.querySelector(".monto_dolar");

        let bolivares = parseFloat(inputBs.value) || 0;
        let tasa = parseFloat(inputTasa.value) || 0;

        if (tasa > 0) {
            inputDolar.value = (bolivares / tasa).toFixed(2);
        } else {
            inputDolar.value = "0.00";
        }
    }
}

function mostrarCamposMetodoPago(e) {
    if (e.target.matches(".tipo_pago_admin")) {
        actualizarVisibilidadMetodo(e.target);
    }
}

function actualizarVisibilidadMetodo(selectElement) {
    const metodo = selectElement.value;
    const tarjeta = selectElement.closest(".detalle-pago");
    const camposBancarios = tarjeta.querySelectorAll(".campos-bancarios");
    const campoMonto = tarjeta.querySelector(".campo-monto");

    campoMonto.classList.remove("d-none");

    if (metodo === "Efectivo" || metodo === "Divisa") {
        camposBancarios.forEach(c => c.classList.add("d-none"));
    } else {
        camposBancarios.forEach(c => c.classList.remove("d-none"));
    }
}

// ============================================================
// CARGA ASÍNCRONA DE DATOS (DEPENDENCIAS)
// ============================================================
async function cargarMensualidades() {
    let id_apartamento = this.value;
    if (!id_apartamento) return;

    let formData = new FormData();
    formData.append("operacion", "consultar_mensualidades");
    formData.append("apartamento_id", id_apartamento);

    let respuesta = await Peticiones.enviar(formData, "", true);
    if (!respuesta.estatus && respuesta.mensaje) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }
    console.log(respuesta)
    let select_mensualidades = document.getElementById("mensualidad_id");
    select_mensualidades.innerHTML = "<option value=''>Seleccione una mensualidad</option>";

    const meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    let fragment = document.createDocumentFragment();

    (respuesta.datos || []).forEach(m => {
        let monto_base = parseFloat(m.monto);
        let pendiente_base = parseFloat(m.pendiente);
        
        let hoy = new Date();
        let fechaReferencia = new Date(m.anio, m.mes - 1, 1);
        let diferenciaDias = Math.floor((hoy - fechaReferencia) / (1000 * 60 * 60 * 24));
        
        let recargo = (diferenciaDias > m.limite_mensualidad) ? monto_base * (m.porcentaje_interes / 100) : 0;
        let monto_total = monto_base + recargo;
        let total_pagado = monto_base - pendiente_base;
        let saldo_deuda = Math.max(0, monto_total - total_pagado);

        if (saldo_deuda > 0) {
            let opcion = document.createElement("option");
            opcion.value = m.id_mensualidad;
            opcion.textContent = `${meses[parseInt(m.mes)]}/${m.anio} - Restante: ${saldo_deuda.toFixed(2)} Bs`;
            opcion.setAttribute("data-monto", monto_total.toFixed(2));
            fragment.appendChild(opcion);
        }
    });

    select_mensualidades.appendChild(fragment);
    select_mensualidades.removeAttribute('disabled');
}

// ============================================================
// CONSULTAS PRINCIPALES Y DATATABLE
// ============================================================
async function consultar() {
    const formatoMonto = (cell) => `${cell.getValue()} Bs.`;
    const formatoPeriodo = (cell) => {
        let data = cell.getValue();
        if (!data) return "N/A";
        let [mes, anio] = data.split('/');
        return `${FormatoFechas.nombreMes(parseInt(mes))} del ${anio}`;
    };
    const formatoEstado = (cell) => {
        let estado = cell.getValue();
        let color = "secondary";
        if (estado === "PROCESADO") color = "success";
        if (estado === "PENDIENTE" || estado === "No verificado") color = "warning text-dark";
        if (estado === "ANULADO" || estado === "RECHAZADO") color = "danger";
        return `<span class="badge bg-${color}">${estado}</span>`;
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_pago;
        let html = `<div class="d-flex justify-content-center gap-2">
            <button data-tooltip="true" type="button" class="btn btn-primary btn-sm vista-previa" title="Previsualizar contenido del registro" value="${id}"><i class="bi bi-eye-fill"></i></button>
            <button data-tooltip="true" type="button" class="btn btn-info btn-sm text-white recibo-pago" style="background-color:#3939a9;" title="Descargar Recibo de Pago (PDF)" value="${id}"><i class="bi bi-card-checklist"></i></button>
            <button data-tooltip="true" type="button" class="btn btn-success btn-sm modificar" title="Modificar los detalles de este registro" value="${id}"><i class="bi bi-pencil-square"></i></button>`;
        if (permiso_eliminar == 1) {
            html += `<button data-tooltip="true" type="button" class="btn btn-danger btn-sm eliminar" title="Anular" value="${id}"><i class="bi bi-trash"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Período", field: "periodos", formatter: formatoPeriodo, minWidth: 150 },
        { title: "Monto", field: "monto_total", formatter: formatoMonto, minWidth: 120 },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 120 },
        { title: "Apartamento", field: "apartamento", formatter: (cell) => `Nro: ${cell.getValue() || 'N/A'}`, minWidth: 120 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 160, responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = btn.value;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(id);
                if (btn.classList.contains('modificar')) prepararEdicion(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
                
                // Formulario dinámico para el PDF sin ensuciar la tabla
                if (btn.classList.contains('recibo-pago')) {
                    let form = document.createElement('form');
                    form.action = "?pagina=reportes&accion=recibo_pago";
                    form.method = "POST";
                    form.target = "_blank"; // Opcional: abre en otra pestaña
                    
                    let input = document.createElement('input');
                    input.type = "hidden";
                    input.name = "select_reporte";
                    input.value = id;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }
            }
        }
    ];

    tabla_pagos = Tablas.cargarTabulador("tabla_pagos", "", columnas);

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas.filter(col => col.field).map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_pagos.setFilter([filtros]);
        });
    }
}

// ============================================================
// RECOLECTOR DTO (Prepara el FormData unificado)
// ============================================================
function recolectarDatosFormData(operacion, id_pago = null) {
    let formData = new FormData();
    formData.append("operacion", operacion);
    if (id_pago) formData.append("id_pago", id_pago);

    // Cabecera
    formData.append("apartamento_id", document.getElementById("apartamento_id").value);
    formData.append("mensualidad_id", document.getElementById("mensualidad_id").value);
    formData.append("monto_mensualidad", document.getElementById("monto_mensualidad").value);
    formData.append("estado", document.getElementById("estado")?.value);
    formData.append("observacion", document.getElementById("observacion").value);

    // Detalles (ConstructorDetalles PHP espera arrays paralelos para escalar, y archivos mapeados por índice)
    const bloques = document.querySelectorAll("#detalles_container .detalle-pago");
    
    bloques.forEach((bloque, index) => {
        formData.append("fecha[]", bloque.querySelector(".fecha_admin").value);
        formData.append("monto[]", bloque.querySelector(".monto").value);
        formData.append("monto_dolar[]", bloque.querySelector(".monto_dolar").value);
        
        let tipo = bloque.querySelector(".tipo_pago_admin").value;
        formData.append("tipo_pago[]", tipo);

        if (tipo === "Transferencia" || tipo === "Pago Movil") {
            formData.append("referencia[]", bloque.querySelector(".referencia").value);
            formData.append("banco_id[]", bloque.querySelector(".banco_admin").value);
            
            // Adjuntar archivo físico al índice correspondiente
            let inputImagen = bloque.querySelector(".imagen");
            if (inputImagen && inputImagen.files.length > 0) {
                formData.append(`imagen_${index}`, inputImagen.files[0]);
            }
            
            // Adjuntar imagen existente (si aplica, para edición)
            let inputExistente = bloque.querySelector("input[name='imagen_existente[]']");
            formData.append("imagen_existente[]", inputExistente ? inputExistente.value : "");
        } else {
            // Rellenar vacíos para mantener alineación de arrays en PHP
            formData.append("referencia[]", "");
            formData.append("banco_id[]", "");
            formData.append("imagen_existente[]", "");
        }
    });

    return formData;
}

// ============================================================
// ACCIONES (REGISTRAR, modificar, ELIMINAR)
// ============================================================
async function registrar() {
    let formData = recolectarDatosFormData("registrar");
    let respuesta = await Peticiones.enviar(formData, "", true);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    modal.hide();
    tabla_pagos.replaceData();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

async function prepararEdicion(id) {
    let datos = new FormData();
    datos.append('id_pago', id);
    datos.append('operacion', 'consulta_especifica');

    let respuesta = await Peticiones.enviar(datos, "", true);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    let cabecera = respuesta.datos;
    
    // Llenar Cabecera
    if (document.getElementById("estado")?.value) document.getElementById("estado").value = cabecera.estado;
    document.getElementById("observacion").value = cabecera.observacion;
    document.getElementById("apartamento_id").value = cabecera.apartamento_id;
    
    // Forzamos la carga de mensualidades y seteamos la seleccionada manualmente
    let mensualidadSelect = document.getElementById("mensualidad_id");
    mensualidadSelect.innerHTML = `<option value="${cabecera.mensualidad_id}" selected>Mensualidad Vinculada</option>`;
    mensualidadSelect.removeAttribute("disabled");
    document.getElementById("monto_mensualidad").value = cabecera.monto_mensualidad;

    // Llenar Detalles
    const container = document.getElementById("detalles_container");
    container.innerHTML = ""; // Limpiar
    const plantilla = document.getElementById("plantilla-detalle-pago");

    cabecera.detalles.forEach((det, index) => {
        let nuevoBloque = plantilla.content.firstElementChild.cloneNode(true);

        if (index > 0) {
            let btnEliminar = document.createElement("button");
            btnEliminar.className = "btn btn-sm btn-outline-danger mb-3";
            btnEliminar.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar';
            btnEliminar.onclick = () => { nuevoBloque.remove(); renumerarDetalles(); };
            nuevoBloque.querySelector('.card-body').prepend(btnEliminar);
        }

        nuevoBloque.querySelector(".fecha_admin").value = det.fecha;
        nuevoBloque.querySelector(".monto").value = det.monto;
        nuevoBloque.querySelector(".monto_dolar").value = det.monto_dolar;
        nuevoBloque.querySelector(".tasa_dolar").value = tasa_dolar;
        
        let selectTipo = nuevoBloque.querySelector(".tipo_pago_admin");
        selectTipo.value = det.tipo_pago;

        // Disparar lógica de mostrar/ocultar
        actualizarVisibilidadMetodo(selectTipo);

        if (det.tipo_pago === "Transferencia" || det.tipo_pago === "Pago Movil") {
            nuevoBloque.querySelector(".referencia").value = det.referencia;
            nuevoBloque.querySelector(".banco_admin").value = det.banco_id || "";

            if (det.imagen && det.imagen !== "default.png") {
                nuevoBloque.querySelector(".nombre_imagen_cargada").textContent = `Comprobante: ${det.imagen}`;
                let inputOculto = document.createElement("input");
                inputOculto.type = "hidden";
                inputOculto.name = "imagen_existente[]";
                inputOculto.value = det.imagen;
                nuevoBloque.appendChild(inputOculto);
            }
        }

        container.appendChild(nuevoBloque);
    });

    renumerarDetalles();

    boton_formulario.setAttribute("modificar", "true");
    boton_formulario.setAttribute("id_modificar", id);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById("titulo_modal").textContent = "Modificar Pago";
    
    modal.show();
}

async function modificar(id) {
    let formData = recolectarDatosFormData("modificar", id);
    let respuesta = await Peticiones.enviar(formData, "", true);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    modal.hide();
    tabla_pagos.replaceData();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

function confirmarEliminar(id) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Este pago será anulado/eliminado permanentemente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        confirmButtonText: "Sí, anular",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            eliminar(id);
        }
    });
}

async function eliminar(id) {
    let formData = new FormData();
    formData.append("operacion", "eliminar");
    formData.append("id_pago", id);

    let respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    tabla_pagos.replaceData();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

// ============================================================
// VISTA PREVIA
// ============================================================
async function mostrarVistaPrevia(id) {
    let formData = new FormData();
    formData.append("operacion", "consulta_especifica");
    formData.append("id_pago", id);

    let respuesta = await Peticiones.enviar(formData, "", true);
    if (!respuesta.estatus) return;

    let data = respuesta.datos;
    
    // Llenar datos de cabecera
    document.getElementById("vista_fecha").textContent = FormatoFechas.formatoUsuario(data.detalles[0]?.fecha || '');
    document.getElementById("vista_monto_mensualidad").textContent = `${data.monto_mensualidad} Bs`;
    document.getElementById("vista_apartamento").textContent = data.nro_apartamento || 'N/A';
    document.getElementById("vista_observacion").textContent = data.observacion;

    // Crear el badge de estado dinámicamente
    let colorEstado = "secondary";
    if (data.estado === "PROCESADO") colorEstado = "success";
    if (data.estado === "PENDIENTE" || data.estado === "No verificado") colorEstado = "warning text-dark";
    if (data.estado === "ANULADO" || data.estado === "RECHAZADO") colorEstado = "danger";
    document.getElementById("vista_estado").innerHTML = `<span class="badge bg-${colorEstado}">${data.estado}</span>`;

    // 1. Definir columnas de Tabulator
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 100, responsive: 0 },
        { title: "Monto BS", field: "monto", formatter: (cell) => `${cell.getValue()} Bs`, minWidth: 100 },
        { title: "Monto $", field: "monto_dolar", formatter: (cell) => `${cell.getValue()} $`, minWidth: 100 },
        { title: "Método", field: "tipo_pago", minWidth: 120 },
        { title: "Banco", field: "nombre_banco", formatter: (cell) => cell.getValue() || '<span class="text-muted">N/A</span>', minWidth: 120 },
        { title: "Referencia", field: "referencia", formatter: (cell) => cell.getValue() || '<span class="text-muted">N/A</span>', minWidth: 120 },
        { 
            title: "Comprobante", 
            headerSort: false, 
            hozAlign: "center",
            headerHozAlign: "center",
            formatter: (cell) => {
                const img = cell.getData().imagen;
                if (img && img !== 'default.png') {
                    return `<a data-tooltip="true" href="recursos/img/pagos/${img}" target="_blank" class="btn btn-sm btn-info" title="Ver comprobante"><i class="bi bi-image"></i></a>`;
                }
                return '<span class="text-muted">N/A</span>';
            },
            minWidth: 120,
            responsive: 0 
        }
    ];

    modalVistaPrevia.show();

    setTimeout(() => {
        Tablas.cargarTabuladorEstatico(
            "tabla_detalles_pagos", 
            data.detalles, 
            columnas, 
            { cssClass: "tabla-vista-previa", paginaSize: 5 }
        );
    }, 200);
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - PAGOS
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Función clave para recalcular coordenadas cuando el modal hace scroll
    const forzarRecalculo = () => {
        window.dispatchEvent(new Event('resize'));
    };

    // 1. CONFIGURACIÓN DE LA VISTA PRINCIPAL
    const configPrincipal = {
        showProgress: true,
        animate: true,
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        steps: [
            { element: '.page-header', popover: { title: 'Módulo de Pagos', description: 'Bienvenido. Desde aquí puedes registrar, verificar y gestionar los pagos de las mensualidades del condominio.', side: "bottom", align: 'start' } },
            { element: '[data-bs-target="#modal_pagos"]', popover: { title: 'Nuevo Pago', description: 'Haz clic aquí para abrir el formulario y reportar un nuevo pago de un apartamento.', side: "right", align: 'start' } },
            { element: '#tabla_pagos', popover: { title: 'Tabla de Registros', description: 'Aquí verás el historial de pagos. Usa los botones de acción para Ver detalles, Generar Recibo (PDF), Editar o Anular un pago.', side: "top", align: 'center' } }
        ]
    };

    // 2. CONFIGURACIÓN DEL MODAL DE PAGOS
    const configModal = {
        showProgress: true,
        animate: true,
        smoothScroll: false, // Apagado para usar nuestro scroll matemático
        allowKeyboardControl: false, // Para que Bootstrap no pelee por el enfoque
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        
        onHighlightStarted: (element) => {
            if (element) {
                // Scroll instantáneo y recálculo de coordenadas
                element.scrollIntoView({ behavior: 'auto', block: 'center' });
                setTimeout(forzarRecalculo, 50);
            }
        },
        onPopoverRender: () => {
            const modal = document.getElementById('modal_pagos');
            if (modal) modal.addEventListener('scroll', forzarRecalculo);
        },
        onDestroyed: () => {
            const modal = document.getElementById('modal_pagos');
            if (modal) modal.removeEventListener('scroll', forzarRecalculo);
        },

        // Recorrido adaptado a los elementos de pagos_modal.php
        steps: [
            { element: '#apartamento_id', popover: { title: 'Apartamento', description: 'Primero, selecciona el apartamento que está realizando el pago.', side: 'bottom', align: 'start' } },
            { element: '#mensualidad_id', popover: { title: 'Mensualidad a Pagar', description: 'Al elegir el apartamento, el sistema buscará sus meses pendientes. Selecciona cuál se está pagando.', side: 'bottom', align: 'start' } },
            { element: '#monto_mensualidad', popover: { title: 'Deuda Total', description: 'Aquí aparecerá reflejada automáticamente la deuda total (con recargos si aplica) de esa mensualidad.', side: 'bottom', align: 'start' } },
            { element: '.detalle-pago', popover: { title: 'Detalles de la Transacción', description: 'En este bloque cargarás la información exacta de cómo y cuándo se hizo el pago.', side: 'top', align: 'start' } },
            { element: '.tipo_pago_admin', popover: { title: 'Método Dinámico', description: 'Si escoges "Transferencia" o "Pago Móvil", se desplegarán abajo los campos para registrar el Banco, Referencia y Capture.', side: 'top', align: 'start' } },
            { element: '.tasa_dolar', popover: { title: 'Tasa BCV y Conversión', description: 'El sistema usa la Tasa BCV guardada. Al colocar el Monto en Bs, se calcularán los dólares automáticamente.', side: 'top', align: 'start' } },
            { element: '#agregar_detalle', popover: { title: 'Pagos Mixtos', description: '¿Pagó una parte en divisas y otra en pago móvil? Usa este botón para añadir varios métodos de pago a una misma mensualidad.', side: 'top', align: 'center' } },
            { element: '#observacion', popover: { title: 'Observación', description: 'Puedes añadir una nota aclaratoria sobre este pago si lo consideras necesario.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Procesar Pago', description: 'Verifica que todo esté correcto y haz clic aquí para registrar el pago en el sistema.', side: 'top', align: 'center' } }
        ]
    };

    // 3. LÓGICA DEL BOTÓN FLOTANTE INTELIGENTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalPagos = document.getElementById('modal_pagos');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            // Verificamos si el modal de pagos está abierto en pantalla
            if (modalPagos && window.getComputedStyle(modalPagos).display === 'block') {
                modalPagos.scrollTo(0, 0); // Iniciamos el tour desde arriba
                tourActivo = driver(configModal);
                tourActivo.drive();
            } else {
                tourActivo = driver(configPrincipal);
                tourActivo.drive();
            }
        });
    }

    // Cancelar el tour si el usuario cierra la ventana de golpe
    if (modalPagos) {
        modalPagos.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});
