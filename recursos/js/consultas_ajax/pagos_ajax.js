// ============================================================
// VARIABLES GLOBALES
// ============================================================
let tabla_pagos;
let modal = new bootstrap.Modal(document.getElementById("modal_pagos"));
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
    document.getElementById("modal_pagos").addEventListener("hidden.bs.modal", resetModalPagos);

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

    // Delegación de botones de la tabla principal
    document.querySelector("#tabla_pagos tbody").addEventListener("click", manejarBotonesTabla);
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

    let respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus && respuesta.mensaje) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
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
    const columnas = [
        { data: 'ultima_fecha', render: data => FormatoFechas.formatear(data, 'DD-MM-YYYY') },
        { 
            data: null,
            render: row => `${row.monto_total} Bs.` // o según método de pago
        },
        { 
            data: null,
            render: data => {
                let fecha_part = data.periodos.split('/');
                return FormatoFechas.nombreMes(new Date(`${fecha_part[0]}/01/${fecha_part[1]}`).getMonth() + 1) + ' del ' + new Date(`${fecha_part[0]}/01/${fecha_part[1]}`).getFullYear()
            } 
        },        
        { data: "estado", render: data => obtenerBadgeEstado(data) },
        { data: "apartamento", render: data => `Nro: ${data || 'N/A'}` },
        { data: null, render: row => crearBotones(row.id_pago).innerHTML }
    ];

    const parametros = (data) => { data.operacion = 'consulta'; };
    const postCreacion = (row, data) => { 
        row.id = `fila-${data.id_pago}`; 
        row.lastElementChild.setAttribute('class','row');
    };

    tabla_pagos = Utilidades.crearDataTable('tabla_pagos', columnas, parametros, postCreacion);
}

function obtenerBadgeEstado(estado) {
    let color = "secondary";
    if (estado === "Procesado") color = "success";
    if (estado === "PENDIENTE" || estado === "No verificado") color = "warning";
    if (estado === "ANULADO" || estado === "RECHAZADO") color = "danger";
    return `<span class="badge bg-${color}">${estado}</span>`;
}

function crearBotones(id) {
    let div = document.createElement("div");
    div.className = "row justify-content-evenly";

    let html = `
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-primary btn-sm vista-previa" title="Vista previa" value="${id}">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
        <form class="col-lg-3 col-6 mt-2 mt-lg-0" action="?pagina=reportes_controlador.php&accion=recibo_pago" method="POST">
            <input type="hidden" name="select_reporte" value="${id}">
            <button type="submit" class="btn btn-outline-light btn-sm" style="background-color:#3939a9;" title="Recibo">
                <i class="bi bi-card-checklist"></i>
            </button>
        </form>
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-success btn-sm modificar" title="modificar" value="${id}">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>`;

    if (permiso_eliminar == 1) {
        html += `
        <div class="col-lg-3 col-6 mt-2 mt-lg-0">
            <button type="button" class="btn btn-danger btn-sm eliminar" title="Anular" value="${id}">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    }
    div.innerHTML = html;
    return div;
}

function manejarBotonesTabla(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = btn.value;

    if (btn.classList.contains('vista-previa')) {
        mostrarVistaPrevia(id);
    } else if (btn.classList.contains('modificar')) {
        prepararEdicion(id);
    } else if (btn.classList.contains('eliminar')) {
        confirmarEliminar(id);
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
    let respuesta = await Utilidades.query(formData, true);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    modal.hide();
    tabla_pagos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
}

async function prepararEdicion(id) {
    let datos = new FormData();
    datos.append('id_pago', id);
    datos.append('operacion', 'consulta_especifica');

    let respuesta = await Utilidades.query(datos, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
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
    let respuesta = await Utilidades.query(formData, true);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    modal.hide();
    tabla_pagos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
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

    let respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    tabla_pagos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
}

// ============================================================
// VISTA PREVIA
// ============================================================
async function mostrarVistaPrevia(id) {
    let formData = new FormData();
    formData.append("operacion", "consulta_especifica");
    formData.append("id_pago", id);

    let respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) return;

    let data = respuesta.datos;

    document.getElementById("vista_fecha").textContent = FormatoFechas.formatear(data.detalles[0]?.fecha || '', 'DD-MM-YYYY');
    document.getElementById("vista_monto_mensualidad").textContent = `${data.monto_mensualidad} Bs`;
    document.getElementById("vista_estado").innerHTML = obtenerBadgeEstado(data.estado);
    document.getElementById("vista_apartamento").textContent = data.nro_apartamento || 'N/A';
    document.getElementById("vista_observacion").textContent = data.observacion;

    // 1. Destruir el DataTable previo si existe para evitar errores
    if ($.fn.DataTable.isDataTable('#tabla_detalles_pagos')) {
        $('#tabla_detalles_pagos').DataTable().clear().destroy();
    }

    const tbody = document.querySelector("#tabla_detalles_pagos tbody");
    tbody.innerHTML = "";

    // 2. Llenar el cuerpo de la tabla
    data.detalles.forEach(det => {
        let tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${FormatoFechas.formatear(det.fecha, 'DD-MM-YYYY')}</td>
            <td>${det.monto} Bs</td>
            <td>${det.monto_dolar} $</td>
            <td>${det.tipo_pago}</td>
            <td>${det.nombre_banco || '<span class="text-muted">N/A</span>'}</td>
            <td>${det.referencia || '<span class="text-muted">N/A</span>'}</td>
            <td class="text-center">
                ${(det.imagen && det.imagen !== 'default.png') ? 
                `<a href="recursos/img/pagos/${det.imagen}" target="_blank" class="btn btn-sm btn-info" title="Ver comprobante"><i class="bi bi-image"></i></a>` : 
                '<span class="text-muted">N/A</span>'}
            </td>
        `;
        tbody.appendChild(tr);
    });

    // 3. Reinicializar DataTable
    new DataTable('#tabla_detalles_pagos', {
        destroy: true,
        responsive: true,
        language: { url: 'recursos/bootstrap/js/datatable-plugin-es.js' },
        paging: false, // Como son detalles de un solo pago, la paginación suele sobrar
        searching: false, // Ocultar el buscador interno
        info: false // Ocultar el texto "Mostrando 1 a N"
    });

    modalVistaPrevia.show();
}

// Vinculación del validador (Se ejecutará desde pagos_validar.js)
async function validarEnvio(accion) {
    // La validación real se centralizará
    return true; 
}