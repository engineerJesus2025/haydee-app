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
const modalGasto = new bootstrap.Modal(document.getElementById("modal_gastos"), { focus: false });
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
    consultar();

    // Evento para agregar nuevo detalle
    document.getElementById("agregar_detalle")?.addEventListener("click", agregarDetalle);

    // Reset del modal al cerrarse
    document.getElementById("modal_gastos")?.addEventListener("hide.bs.modal", resetModalGasto);

    document.getElementById("modal_vista_previa_detalles")?.addEventListener("hide.bs.modal", e=>{
        document.getElementById('vista_imagen_detalles').style = "max-height: 300px;";
        document.getElementById('mensaje_error_imagen_detalles').classList.add('d-none')
    });

    // Delegación de eventos para el cambio de Método de Pago
    contenedorDetalles.addEventListener('change', (e) => {
        // Verificamos si el elemento que cambió fue un select de método de pago
        if (e.target.classList.contains('metodo_pago')) {
            manejarVisibilidadBancaria(e.target);
        }
    });
});

// ============================================================
// FUNCIONES PRINCIPALES
// ============================================================

async function consultar() {
    // 1. FORMATOS VISUALES
    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());
    const formatoMonto = (cell) => formatearMontoConMoneda(cell.getValue(), cell.getData().metodo_pago);
    const formatoMayuscula = (cell) => cell.getValue() ? cell.getValue().charAt(0).toUpperCase() + cell.getValue().slice(1).toLowerCase() : '';

    const formatoBotones = (cell) => {
        const id = cell.getData().id_gasto;
        let html = `<div class="d-flex justify-content-center gap-2">
            <button data-tooltip="true" type="button" class="btn btn-primary btn-sm vista-previa" title="Previsualizar contenido del registro" value="${id}"><i class="bi bi-eye-fill"></i></button>
            <button data-tooltip="true" type="button" class="btn btn-success btn-sm modificar" title="Modificar los detalles de este registro" value="${id}" data-bs-toggle="modal" data-bs-target="#modal_gastos"><i class="bi bi-pencil-square"></i></button>`;
        if (permiso_eliminar == 1) {
            html += `<button data-tooltip="true" type="button" class="btn btn-danger btn-sm eliminar" title="Quitar este elemento del sistema" value="${id}"><i class="bi bi-trash"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    // 2. COLUMNAS
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Fecha", field: "ultima_fecha", formatter: formatoFecha, minWidth: 100, responsive: 0 },
        { title: "Monto", field: "monto_total", formatter: formatoMonto, minWidth: 120 },
        { title: "Tipo Gasto", field: "tipo", formatter: formatoMayuscula, minWidth: 150 }, 
        { title: "Descripción", field: "descripcion_gasto", minWidth: 200 }, 
        { 
            title: "Acciones", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 140, responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { target: btn };
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(mockEvent);
                if (btn.classList.contains('modificar')) prepararFormularioEdicion(mockEvent);
                if (btn.classList.contains('eliminar')) confirmarEliminar(mockEvent);
            }
        }       
    ];

    tabla_gastos = Tablas.cargarTabulador("tabla_gastos", "", columnas);

    // 3. BUSCADOR
    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas.filter(col => col.field).map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_gastos.setFilter([filtros]);
        });
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

    const respuesta = await Peticiones.enviar(formData,'', true);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalGasto.hide();
    tabla_gastos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'Gasto registrado correctamente');
}

/**
 * Prepara el formulario para edición cargando los datos del gasto
 */
async function prepararFormularioEdicion(e) {
    let id = e.target.value || e.target.parentElement.value; 
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos, '',true);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
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

    const respuesta = await Peticiones.enviar(formData, '',true);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalGasto.hide();
    tabla_gastos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'Gasto modificado correctamente');
}

// ============================================================
// VISTA PREVIA
// ============================================================

/**
 * Muestra la vista previa de un gasto
 */
async function mostrarVistaPrevia(e) {
    let id = e.target.value || e.target.parentElement.value; 
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    const gasto = respuesta.datos.gasto;

    document.getElementById('vista_fecha').textContent = FormatoFechas.formatoUsuario(gasto.ultima_fecha);

    modalVistaPrevia.show();

    setTimeout(async () => {
        await cargarDetallesEnTabla(gasto.id_gasto);
    }, 200);
}

/**
 * Carga los detalles de un gasto en la tabla de detalles dentro del modal de vista previa
 */
async function cargarDetallesEnTabla(idGasto) {
    const datos = new FormData();
    datos.append('id_gasto', idGasto);
    datos.append('operacion', 'consultar_detalles');

    const respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    const detalles = respuesta.datos || [];

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 100, responsive: 0 },
        { title: "Monto", field: "monto", formatter: (cell) => formatearMontoConMoneda(cell.getValue(), cell.getData().metodo_pago), minWidth: 120 },
        { title: "Método", field: "metodo_pago", minWidth: 120 },
        { title: "Descripción", field: "descripcion_detalle_gasto", minWidth: 200 },
        {
            title: "Acciones",
            headerSort: false,
            hozAlign: "center",
            headerHozAlign: "center",
            formatter: (cell) => `<button data-tooltip="true" class="btn btn-sm btn-primary ver-detalle" value="${cell.getData().id_detalle_gasto}"><i class="bi bi-eye"></i></button>`,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                if (btn.classList.contains('ver-detalle')) {
                    mostrarVistaPreviaDetalle(btn.value); // Llama a la otra ventana modal
                }
            },
            minWidth: 100,
            responsive: 0
        }
    ];

    Tablas.cargarTabuladorEstatico(
        "tabla_detalles_gastos", 
        detalles, 
        columnas, 
        { cssClass: "tabla-vista-previa", paginaSize: 5 }
    );
}

/**
 * Muestra la vista previa de un detalle individual
 */
async function mostrarVistaPreviaDetalle(idDetalle) {
    const datos = new FormData();
    datos.append('id_detalle_gasto', idDetalle);
    datos.append('operacion', 'consulta_especifica_detalles');

    const respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    const det = respuesta.datos;

    document.getElementById('vista_fecha_detalles').textContent = FormatoFechas.formatoUsuario(det.fecha, 'DD-MM-YYYY');
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

function confirmarEliminar(e) {
    let id = e.target.value || e.target.parentElement.value; 
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

    const respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_gastos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'Gasto eliminado correctamente');
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

// Nueva función para manejar la visibilidad
function manejarVisibilidadBancaria(selectMetodo) {
    // Buscar el contenedor padre de esta fila/bloque específico
    const bloque = selectMetodo.closest('.row'); // Ajusta '.row' si tu contenedor de bloque usa otra clase
    
    // Obtener los contenedores a ocultar/mostrar
    const camposBancarios = bloque.querySelectorAll('.grupo_bancario');
    const campoImagen = bloque.querySelector('.grupo_imagen');
    
    // Si es transferencia o pago móvil, mostramos. Si no, ocultamos.
    const requiereBanco = (selectMetodo.value === 'Transferencia' || selectMetodo.value === 'Pago Movil');

    if (requiereBanco) {
        camposBancarios.forEach(campo => campo.classList.remove('d-none'));
        if(campoImagen) campoImagen.classList.remove('d-none');
    } else {
        camposBancarios.forEach(campo => campo.classList.add('d-none'));
        if(campoImagen) campoImagen.classList.add('d-none');
        
        // Opcional pero recomendado: Limpiar los valores ocultos para evitar enviar basura al servidor
        const inputRef = bloque.querySelector('.referencia');
        const selectBanco = bloque.querySelector('.banco');
        const inputImg = bloque.querySelector('.imagen');
        
        if(inputRef) inputRef.value = '';
        if(selectBanco) selectBanco.value = '';
        if(inputImg) inputImg.value = '';
    }
}

/**
 * Actualiza la visibilidad de los campos bancarios según el método de pago
 */
function actualizarVisibilidadCampos(selectMetodo) {
    const bloque = selectMetodo.closest('.detalle-gasto');
    const grupoImg = bloque.querySelector('.grupo_imagen');
    const grupoBanco = bloque.querySelectorAll('.grupo_bancario');
    const valor = selectMetodo.value.toLowerCase();

    const mostrar = (valor === 'transferencia' || valor === 'pago movil');

    grupoBanco.forEach(input=>{
        input.classList.toggle('d-none', !mostrar);
    });

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

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS)
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

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
            { element: '.page-header', popover: { title: 'Módulo de Gastos', description: 'Bienvenido. Desde aquí puedes gestionar y controlar todas las salidas de dinero.', side: "bottom", align: 'start' } },
            { element: '[data-bs-target="#modal_gastos"]', popover: { title: 'Nuevo Gasto', description: 'Haz clic en este botón para abrir el formulario y registrar un nuevo gasto.', side: "right", align: 'start' } },
            { element: '#tabla_gastos_wrapper', popover: { title: 'Tabla de Registros', description: 'Aquí se listan tus gastos. Usa el buscador interno y los botones de acción para Ver, Editar o Eliminar.', side: "top", align: 'center' } }
        ]
    };

    // 2. CONFIGURACIÓN DEL MODAL
    const configModal = {
        showProgress: true,
        animate: true,
        smoothScroll: false, // Apagamos el scroll de Driver para usar el nuestro
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        
        // Simplemente bajamos el modal al elemento, sin pelear con Bootstrap
        onHighlightStarted: (element) => {
            if (element) {
                // block: 'center' deja el elemento cómodamente en el medio de la vista
                element.scrollIntoView({ behavior: 'auto', block: 'center' });
            }
        },

        steps: [
            { element: '#clasificacion', popover: { title: 'Clasificación', description: 'Indica si este gasto es Fijo (mensual/recurrente) o Variable (esporádico).', side: 'bottom', align: 'start' } },
            { element: '#tipo_gasto', popover: { title: 'Tipo de Gasto', description: 'Selecciona la categoría exacta a la que pertenece este gasto.', side: 'bottom', align: 'start' } },
            { element: '#descripcion_gasto', popover: { title: 'Descripción', description: 'Redacta el motivo general del gasto con claridad. (Debe tener al menos 10 caracteres).', side: 'top', align: 'start' } },
            { element: '#proveedor', popover: { title: 'Datos del Proveedor', description: 'Selecciona la empresa o persona a la que se le pagó, y vincula una Solicitud si el gasto proviene de una.', side: 'top', align: 'start' } },
            { element: '.detalle-gasto', popover: { title: 'Detalles del Pago', description: 'En este bloque registrarás cómo y cuándo pagaste este gasto.', side: 'top', align: 'center' } },
            { element: '.metodo_pago', popover: { title: 'Método Dinámico', description: '¡Importante! Si eliges "Transferencia" o "Pago Móvil", aparecerán automáticamente los campos para que ingreses la Referencia, el Banco y la imagen del Comprobante.', side: 'top', align: 'start' } },
            { element: '#agregar_detalle', popover: { title: 'Pagos Fraccionados', description: '¿Pagaste una parte en efectivo y otra por transferencia? Usa este botón para añadir tantos métodos de pago como necesites.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Una vez valides que todo está correcto, haz clic aquí para registrar el gasto en el sistema.', side: 'top', align: 'center' } }
        ]
    };

    // 3. Lógica del Botón
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalGastos = document.getElementById('modal_gastos');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalGastos && window.getComputedStyle(modalGastos).display === 'block') {
                modalGastos.scrollTo(0, 0); // Iniciamos el tour desde arriba
                tourActivo = driver(configModal);
                tourActivo.drive();
            } else {
                tourActivo = driver(configPrincipal);
                tourActivo.drive();
            }
        });
    }

    // Limpieza de memoria si cierran el modal
    if (modalGastos) {
        modalGastos.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});