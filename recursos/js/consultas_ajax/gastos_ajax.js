/**
 * gastos_ajax.js
 * Gestión de Gastos - Peticiones AJAX
 * Dependencias: utilidades.js, validaciones.js, formatoFechas.js
 */

// ============================================================
// VARIABLES GLOBALES DEL MÓDULO
// ============================================================
let tabla_gastos;
let id_modificar = null;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

// Elementos del DOM
const modalGasto = new bootstrap.Modal(document.getElementById("modal_gastos"));
const modalVistaPrevia = new bootstrap.Modal(document.getElementById("modal_vista_previa"));
const modalVistaPreviaDetalles = new bootstrap.Modal(document.getElementById("modal_vista_previa_detalles"));

const formulario = document.getElementById("form_gastos");
const botonFormulario = document.getElementById("boton_formulario");
const contenedorDetalles = document.getElementById("detalles-container");
const plantillaDetalle = document.getElementById("plantilla-detalle-gasto");

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    consultarGastos();

    // Evento para agregar nuevo detalle
    document.getElementById("agregar_detalle")?.addEventListener("click", agregarDetalle);

    // Reset del modal al cerrarse
    document.getElementById("modal_gastos")?.addEventListener("hide.bs.modal", resetModalGasto);

    document.getElementById("modal_vista_previa_detalles")?.addEventListener("hide.bs.modal", e=>{
        document.getElementById('vista_imagen_detalles').style = "max-height: 300px;";
        document.getElementById('mensaje_error_imagen_detalles').classList.add('d-none')
    });

    // Delegación de eventos para botones de la tabla (modificar/eliminar/vista previa)
    document.querySelector("#tabla_gastos tbody")?.addEventListener("click", manejarClickEnTabla);
});

// ============================================================
// FUNCIONES PRINCIPALES
// ============================================================

/**
 * Consulta la lista de gastos e inicializa DataTable
 */
async function consultarGastos() {
    const columnas = [
        { 
            data: null,
            render: row => FormatoFechas.formatear(row.ultima_fecha, 'DD-MM-YYYY')
        },
        {
            data: null,
            render: row => formatearMontoConMoneda(row.monto_total, row.metodo_pago)
        },
        { 
            data: "clasificacion",
            render: data => data ? mayuscula(data) : ''
        },
        { 
            data: "tipo",
            render: data => data ? mayuscula(data) : ''
        },
        { data: "descripcion_gasto" },
        {
            data: null,
            render: row => crearBotones(row.id_gasto).innerHTML
        }
    ];

    const parametros = (data) => {
        data.operacion = 'consulta';
    };

    const postCreacion = (row, data) => {
        row.id = `fila-${data.id_gasto}`;
    };

    tabla_gastos = Utilidades.crearDataTable('tabla_gastos', columnas, parametros, postCreacion);
}

/**
 * Crea los botones de acción para una fila
 */
function crearBotones(id) {
    let div = document.createElement('div');
    div.className = 'row justify-content-evenly';
    
    let html = `
        <button type="button" class="btn btn-primary btn-sm vista-previa" title="Vista previa" value="${id}">
            <i class="bi bi-eye-fill"></i>
        </button>
        <button type="button" class="btn btn-success btn-sm modificar" title="modificar" value="${id}" data-bs-toggle="modal" data-bs-target="#modal_gastos">
            <i class="bi bi-pencil-square"></i>
        </button>`;
    
    if (permiso_eliminar == 1) {
        html += `
        <button type="button" class="btn btn-danger btn-sm eliminar" title="Eliminar" value="${id}">
            <i class="bi bi-trash"></i>
        </button>`;
    }
    
    div.innerHTML = html;
    return div;
}

/**
 * Maneja clics en los botones de la tabla (delegación)
 */
function manejarClickEnTabla(e) {
    const boton = e.target.closest('button');
    if (!boton) return;

    const id = boton.value;

    if (boton.classList.contains('vista-previa')) {
        mostrarVistaPrevia(id);
    } else if (boton.classList.contains('modificar')) {
        prepararFormularioEdicion(id);
    } else if (boton.classList.contains('eliminar')) {
        confirmarEliminar(id);
    }
}

// ============================================================
// REGISTRO Y EDICIÓN
// ============================================================

/**
 * Recoge los datos del formulario (cabecera + detalles) y los empaqueta en FormData
 */
function recolectarDatosFormulario() {
    const formData = new FormData(); // Vacío, lo llenamos manualmente

    // Campos de cabecera (siempre presentes)
    formData.append('clasificacion', document.getElementById('clasificacion').value);
    formData.append('tipo_gasto', document.getElementById('tipo_gasto').value);
    formData.append('descripcion_gasto', document.getElementById('descripcion_gasto').value);
    formData.append('proveedor', document.getElementById('proveedor').value);
    const solicitud = document.getElementById('solicitud').value;
    if (solicitud) formData.append('solicitud', solicitud);

    // Detalles
    const bloques = contenedorDetalles.querySelectorAll('.detalle-gasto');
    bloques.forEach((bloque, index) => {
        // Campos obligatorios del detalle (siempre se envían)
        formData.append('fecha_detalle[]', bloque.querySelector('.fecha_detalle').value);
        formData.append('monto[]', bloque.querySelector('.monto').value);
        formData.append('metodo_pago[]', bloque.querySelector('.metodo_pago').value);
        formData.append('descripcion_detalle[]', bloque.querySelector('.descripcion_detalle').value);

        // Campos bancarios: se envían siempre (con valor vacío si no aplica)
        const refInput = bloque.querySelector('.referencia');
        formData.append('referencia[]', refInput ? refInput.value : '');

        const bancoInput = bloque.querySelector('.banco');
        formData.append('banco_id[]', bancoInput ? bancoInput.value : '');

        // Imagen existente (siempre añadimos un valor, vacío si no hay input)
        const imgExistente = bloque.querySelector('input[name="imagen_existente[]"]');
        formData.append('imagen_existente[]', imgExistente ? imgExistente.value : '');

        // Imagen nueva (archivo)
        const inputImagen = bloque.querySelector('.imagen');
        if (inputImagen && inputImagen.files.length > 0) {
            formData.append(`imagen_${index}`, inputImagen.files[0]);
        }
    });

    // ID del gasto si es edición
    const idGasto = document.getElementById('boton_formulario').getAttribute('id_modificar');
    if (idGasto) {
        formData.append('id_gasto', idGasto);
    }

    // Operación
    formData.append('operacion', idGasto ? 'modificar' : 'registrar');

    return formData;
}

/**
 * Registra un nuevo gasto
 */
async function registrar() {
    const formData = recolectarDatosFormulario();
    formData.append('operacion', 'registrar');

    const respuesta = await Utilidades.query(formData, true);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalGasto.hide();
    tabla_gastos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Gasto registrado correctamente');
}

/**
 * Prepara el formulario para edición cargando los datos del gasto
 */
async function prepararFormularioEdicion(id) {
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos, true);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const gasto = respuesta.datos.gasto;
    const detalles = respuesta.datos.detalles;

    // --- NUEVA LÓGICA PARA CLASIFICACIÓN ---
    const selectClasificacion = formulario.querySelector('#clasificacion');
    
    // 1. Limpiamos por si quedó una opción de "Reposición" de una edición anterior
    const opcionExistente = selectClasificacion.querySelector('option[value="Reposicion"]');
    if (opcionExistente) opcionExistente.remove();

    // 2. Verificamos si el registro actual es de tipo Reposición
    // Nota: Asegúrate de que el string coincida exactamente con lo que devuelve tu base de datos
    if (gasto.clasificacion === 'Reposicion' || gasto.clasificacion === 'Reposición') {
        // Creamos la opción dinámicamente
        const opcionRepo = document.createElement('option');
        opcionRepo.value = gasto.clasificacion; // Usamos el valor exacto de la BD
        opcionRepo.textContent = 'Reposición';
        selectClasificacion.appendChild(opcionRepo);
        
        // Opcional pero recomendado: Bloquear el campo para evitar que lo cambien a Fijo/Variable
        selectClasificacion.disabled = true; 
        
        // También podrías bloquear el "tipo de gasto", "proveedor" y "solicitud" de la misma manera
        formulario.querySelector('#tipo_gasto').disabled = true;
        formulario.querySelector('#proveedor').disabled = true;
        formulario.querySelector('#solicitud').disabled = true;
    } else {
        // Si es un gasto normal, nos aseguramos de que los campos estén habilitados
        selectClasificacion.disabled = false;
        formulario.querySelector('#tipo_gasto').disabled = false;
        formulario.querySelector('#proveedor').disabled = false;
        formulario.querySelector('#solicitud').disabled = false;
    }
    // ---------------------------------------

    // Llenar cabecera
    selectClasificacion.value = gasto.clasificacion || '';
    formulario.querySelector('#tipo_gasto').value = gasto.tipo_gasto_id || '';
    formulario.querySelector('#solicitud').value = gasto.solicitud_id || '';
    formulario.querySelector('#descripcion_gasto').value = gasto.descripcion_gasto || '';
    formulario.querySelector('#proveedor').value = gasto.proveedor_id || '';

    // Limpiar y reconstruir detalles
    contenedorDetalles.innerHTML = '';
    if (detalles && detalles.length > 0) {
        detalles.forEach((det, idx) => {
            const nuevoBloque = plantillaDetalle.content.firstElementChild.cloneNode(true);
            
            // Llenar campos
            nuevoBloque.querySelector('.fecha_detalle').value = det.fecha || '';
            nuevoBloque.querySelector('.metodo_pago').value = det.metodo_pago || '';
            nuevoBloque.querySelector('.monto').value = det.monto || '';
            nuevoBloque.querySelector('.descripcion_detalle').value = det.descripcion_detalle_gasto || '';

            // Campos bancarios si aplica
            if (det.metodo_pago === 'Transferencia' || det.metodo_pago === 'Pago Movil') {
                nuevoBloque.querySelector('.referencia').value = det.referencia || '';
                nuevoBloque.querySelector('.banco').value = det.banco_id || '';
                if (det.imagen) {
                    nuevoBloque.querySelector('.nombre_imagen_cargada').textContent = `Comprobante: ${det.imagen}`;
                }
            }

            // Siempre crear input hidden para imagen existente (vacío si no hay)
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'imagen_existente[]';
            hidden.value = det.imagen || '';
            nuevoBloque.appendChild(hidden);

            // Actualizar visibilidad según método de pago
            actualizarVisibilidadCampos(nuevoBloque.querySelector('.metodo_pago'));

            // Agregar botón eliminar detalle si no es el único
            if (idx > 0) {
                const btnEliminar = document.createElement('button');
                btnEliminar.type = 'button';
                btnEliminar.className = 'btn btn-sm btn-outline-danger mb-3';
                btnEliminar.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar este Detalle';
                btnEliminar.onclick = () => nuevoBloque.remove();
                nuevoBloque.querySelector('.card-body').prepend(btnEliminar);
            }

            contenedorDetalles.appendChild(nuevoBloque);
        });
    } else {
        // Si no hay detalles, agregar un detalle vacío
        agregarDetalle();
    }

    // Configurar botón para edición
    botonFormulario.setAttribute('modificar', 'true');
    botonFormulario.setAttribute('id_modificar', id);
    botonFormulario.textContent = 'Guardar Cambios';
    document.getElementById('titulo_modal').textContent = 'Modificar Gasto';
    id_modificar = id;
}

/**
 * Modifica un gasto existente
 */
async function modificar(id) {
    const formData = recolectarDatosFormulario();
    formData.append('id_gasto', id);
    formData.append('operacion', 'modificar');

    const respuesta = await Utilidades.query(formData, true);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalGasto.hide();
    tabla_gastos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Gasto modificado correctamente');
}

// ============================================================
// VISTA PREVIA
// ============================================================

/**
 * Muestra la vista previa de un gasto
 */
async function mostrarVistaPrevia(id) {
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const gasto = respuesta.datos.gasto;

    document.getElementById('vista_fecha').textContent = FormatoFechas.formatear(gasto.ultima_fecha, 'DD-MM-YYYY');
    // Cargar detalles en la tabla secundaria
    await cargarDetallesEnTabla(gasto.id_gasto);

    modalVistaPrevia.show();
}

/**
 * Carga los detalles de un gasto en la tabla de detalles dentro del modal de vista previa
 */
async function cargarDetallesEnTabla(idGasto) {
    if ($.fn.DataTable.isDataTable('#tabla_detalles_gastos')) {
        $('#tabla_detalles_gastos').DataTable().clear().destroy();
    }

    const datos = new FormData();
    datos.append('id_gasto', idGasto);
    datos.append('operacion', 'consultar_detalles');

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const detalles = respuesta.datos || [];

    const tablaDetalles = new DataTable('#tabla_detalles_gastos', {
        data: detalles,
        columns: [
            { data: 'fecha', render: data => FormatoFechas.formatear(data, 'DD-MM-YYYY') },
            { 
                data: null,
                render: row => formatearMontoConMoneda(row.monto, row.metodo_pago)
            },
            { data: 'metodo_pago' },
            { data: 'descripcion_detalle_gasto' },
            {
                data: null,
                render: row => `
                    <button class="btn btn-sm btn-primary ver-detalle" value="${row.id_detalle_gasto}">
                        <i class="bi bi-eye"></i>
                    </button>
                `
            }
        ],
        destroy: true,
        responsive: true,
        language: { url: 'recursos/bootstrap/js/datatable-plugin-es.js' }
    });

    // Evento para ver detalle individual
    $('#tabla_detalles_gastos tbody').off('click', '.ver-detalle').on('click', '.ver-detalle', function() {
        const idDetalle = this.value;
        mostrarVistaPreviaDetalle(idDetalle);
    });
}

/**
 * Muestra la vista previa de un detalle individual
 */
async function mostrarVistaPreviaDetalle(idDetalle) {
    const datos = new FormData();
    datos.append('id_detalle_gasto', idDetalle);
    datos.append('operacion', 'consulta_especifica_detalles');

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const det = respuesta.datos;

    document.getElementById('vista_fecha_detalles').textContent = FormatoFechas.formatear(det.fecha, 'DD-MM-YYYY');
    document.getElementById('vista_monto_detalles').textContent = formatearMontoConMoneda(det.monto, det.metodo_pago);
    document.getElementById('vista_metodo_pago_detalles').textContent = det.metodo_pago || '';
    document.getElementById('vista_nombre_banco_detalles').textContent = det.nombre_banco || 'No hay banco registrado';
    document.getElementById('vista_referencia_detalles').textContent = det.referencia || 'No hay referencia';
    document.getElementById('vista_descripcion_detalles').textContent = det.descripcion_detalle_gasto || '';

    const img = det.imagen ? `recursos/img/gastos/${det.imagen}` : '';
    document.getElementById('vista_imagen_detalles').src = img;

    modalVistaPreviaDetalles.show();
}

// ============================================================
// ELIMINACIÓN
// ============================================================

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e01d22',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) eliminar(id);
    });
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_gastos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Gasto eliminado correctamente');
}

// ============================================================
// FUNCIONES AUXILIARES
// ============================================================

/**
 * Agrega un nuevo bloque de detalle al formulario
 */
function agregarDetalle() {
    const nuevoDetalle = plantillaDetalle.content.firstElementChild.cloneNode(true);

    // Limpiar valores
    nuevoDetalle.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.type !== 'hidden') el.value = '';
    });
    nuevoDetalle.querySelector('.nombre_imagen_cargada').textContent = '';

    // Agregar botón eliminar
    const btnEliminar = document.createElement('button');
    btnEliminar.type = 'button';
    btnEliminar.className = 'btn btn-sm btn-outline-danger mb-3';
    btnEliminar.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar este Detalle';
    btnEliminar.onclick = () => nuevoDetalle.remove();
    nuevoDetalle.querySelector('.card-body').prepend(btnEliminar);

    // Inicializar eventos de método de pago
    const selectMetodo = nuevoDetalle.querySelector('.metodo_pago');
    selectMetodo.addEventListener('change', function() {
        actualizarVisibilidadCampos(this);
    });
    actualizarVisibilidadCampos(selectMetodo);

    contenedorDetalles.appendChild(nuevoDetalle);
}

/**
 * Actualiza la visibilidad de los campos bancarios según el método de pago
 */
function actualizarVisibilidadCampos(selectMetodo) {
    const bloque = selectMetodo.closest('.detalle-gasto');
    const grupoRef = bloque.querySelector('.grupo_referencia');
    const grupoBanco = bloque.querySelector('.grupo_banco');
    const grupoImg = bloque.querySelector('.grupo_imagen');
    const valor = selectMetodo.value.toLowerCase();

    const mostrar = (valor === 'transferencia' || valor === 'pago movil');

    grupoRef.classList.toggle('d-none', !mostrar);
    grupoBanco.classList.toggle('d-none', !mostrar);
    grupoImg.classList.toggle('d-none', !mostrar);

    // Habilitar/deshabilitar campos para que no se envíen si están ocultos
    bloque.querySelector('.referencia').disabled = !mostrar;
    bloque.querySelector('.banco').disabled = !mostrar;
    bloque.querySelector('.imagen').disabled = !mostrar;

    if (!mostrar) {
        bloque.querySelector('.referencia').value = '';
        bloque.querySelector('.banco').value = '';
        bloque.querySelector('.imagen').value = null;
    }
}

/**
 * Resetea el modal de gastos a su estado inicial
 */
function resetModalGasto() {
    formulario.reset();
    
    // --- NUEVO: Rehabilitar campos y limpiar opción dinámica ---
    const selectClasificacion = formulario.querySelector('#clasificacion');
    selectClasificacion.disabled = false;
    formulario.querySelector('#tipo_gasto').disabled = false;
    formulario.querySelector('#proveedor').disabled = false;
    formulario.querySelector('#solicitud').disabled = false;

    const opcionExistente = selectClasificacion.querySelector('option[value="Reposicion"], option[value="Reposición"]');
    if (opcionExistente) opcionExistente.remove();
    // -----------------------------------------------------------

    botonFormulario.removeAttribute('modificar');
    botonFormulario.removeAttribute('id_modificar');
    botonFormulario.textContent = 'Registrar';
    document.getElementById('titulo_modal').textContent = 'Registrar Gasto';

    // Eliminar bloques de detalle extras, dejando solo uno
    const bloques = contenedorDetalles.querySelectorAll('.detalle-gasto');
    bloques.forEach((bloque, index) => {
        if (index > 0) bloque.remove();
    });

    formulario.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    formulario.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));

    // Limpiar el primer bloque
    const primerBloque = contenedorDetalles.querySelector('.detalle-gasto');
    if (primerBloque) {
        primerBloque.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.type !== 'hidden') el.value = '';
        });
        nombreImagen = primerBloque.querySelector('.nombre_imagen_cargada');
        if (nombreImagen) nombreImagen.textContent = '';

        const btnEliminar = primerBloque.querySelector('.btn-outline-danger');
        if (btnEliminar) btnEliminar.remove();

        formulario.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
        formulario.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));

        // Restablecer visibilidad según método por defecto
        const selectMetodo = primerBloque.querySelector('.metodo_pago');
        actualizarVisibilidadCampos(selectMetodo);
    }

    id_modificar = null;
}

/**
 * Formatea un monto con moneda según el método de pago
 */
function formatearMontoConMoneda(monto, metodoPago) {
    const num = parseFloat(monto);
    if (isNaN(num)) return '0,00 Bs';
    const formateado = num.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return metodoPago && metodoPago.toLowerCase().includes('efectivo') ? `$ ${formateado}` : `${formateado} Bs`;
}

/**
 * Capitaliza la primera letra de un texto
 */
function mayuscula(texto) {
    if (!texto) return '';
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
}
