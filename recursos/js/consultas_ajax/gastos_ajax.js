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
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

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

    // 2. COLUMNAS
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Tipo", field: "clasificacion", formatter: formatoMayuscula, minWidth: 130 }, 
        { title: "Fecha", field: "ultima_fecha", formatter: formatoFecha, minWidth: 130, responsive: 0 },
        { title: "Monto", field: "monto_total", formatter: formatoMonto, minWidth: 130 },
        { 
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 140, responsive: 0, 
            widthGrow: 2,
            download: false, headerHozAlign: "center",
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

    // BUSCADOR
    Tablas.inicializarBuscadorGlobal(tabla_gastos, "busqueda_global", columnas);
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
    formData.append('tipo_gasto_id', document.getElementById('tipo_gasto_id').value);
    formData.append('descripcion_gasto', document.getElementById('descripcion_gasto').value);
    formData.append('proveedor_id', document.getElementById('proveedor_id').value);
    const solicitud = document.getElementById('solicitud').value;
    if (solicitud) formData.append('solicitud', solicitud);

    // Detalles
    const bloques = contenedorDetalles.querySelectorAll('.detalle-gasto');
    bloques.forEach((bloque, index) => {
        // Campos obligatorios del detalle (siempre se envían)
        formData.append('fecha_detalle[]', bloque.querySelector('.fecha_detalle').value);
        formData.append('monto[]', bloque.querySelector('.monto').value);
        formData.append('metodo_pago[]', bloque.querySelector('.metodo_pago').value);
        formData.append('descripcion_detalle_gasto[]', bloque.querySelector('.descripcion_detalle_gasto').value);

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
    formData.append('operacion', 'registrar_gasto');

    const respuesta = await Peticiones.enviar(formData,'', true);
    Validador.procesarRespuesta(respuesta, () => {
        modalGasto.hide();
        tabla_gastos.replaceData();
    });
}

/**
 * Prepara el formulario para edición cargando los datos del gasto
 */
async function prepararFormularioEdicion(e) {
    let id = e.target.value || e.target.parentElement.value; 
    const datos = new FormData();
    datos.append('id_gasto', id);
    datos.append('operacion', 'consultar_gasto');

    const respuesta = await Peticiones.enviar(datos, '',true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const gasto = respuestaServidor.datos.gasto;
        const detalles = respuestaServidor.datos.detalles;

        const selectClasificacion = formulario.querySelector('#clasificacion');
        
        // Limpiamos por si quedó una opción de "Reposición" de una edición anterior
        const opcionExistente = selectClasificacion.querySelector('option[value="Reposicion"]');
        if (opcionExistente) opcionExistente.remove();

        // Verificamos si el registro actual es de tipo Reposición
        if (gasto.clasificacion === 'Reposicion' || gasto.clasificacion === 'Reposición') {
            // Creamos la opción dinámicamente
            const opcionRepo = document.createElement('option');
            opcionRepo.value = gasto.clasificacion; // Usamos el valor exacto de la BD
            opcionRepo.textContent = 'Reposición';
            selectClasificacion.appendChild(opcionRepo);
            
            // Debatible: Bloquear el campo para evitar que lo cambien a Fijo/Variable
            selectClasificacion.disabled = true; 
            
            // También debatible: bloquear el "tipo de gasto", "proveedor" y "solicitud" de la misma manera
            formulario.querySelector('#tipo_gasto_id').disabled = true;
            formulario.querySelector('#proveedor_id').disabled = true;
            formulario.querySelector('#solicitud').disabled = true;
        } else {
            // Si es un gasto normal, nos aseguramos de que los campos estén habilitados
            selectClasificacion.disabled = false;
            formulario.querySelector('#tipo_gasto_id').disabled = false;
            formulario.querySelector('#proveedor_id').disabled = false;
            formulario.querySelector('#solicitud').disabled = false;
        }
        // ---------------------------------------

        // Llenar cabecera
        selectClasificacion.value = gasto.clasificacion || '';
        formulario.querySelector('#tipo_gasto_id').value = gasto.tipo_gasto_id || '';
        formulario.querySelector('#solicitud').value = gasto.solicitud_id || '';
        formulario.querySelector('#descripcion_gasto').value = gasto.descripcion_gasto || '';
        formulario.querySelector('#proveedor_id').value = gasto.proveedor_id || '';

        // Limpiar y reconstruir detalles
        contenedorDetalles.innerHTML = '';
        if (detalles && detalles.length > 0) {
            detalles.forEach((det, idx) => {
                const nuevoBloque = plantillaDetalle.content.firstElementChild.cloneNode(true);
                
                // Llenar campos
                nuevoBloque.querySelector('.fecha_detalle').value = det.fecha || '';
                nuevoBloque.querySelector('.metodo_pago').value = det.metodo_pago || '';
                nuevoBloque.querySelector('.monto').value = det.monto || '';
                nuevoBloque.querySelector('.descripcion_detalle_gasto').value = det.descripcion_detalle_gasto || '';

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

        modalGasto.show();
    });
}

/**
 * Modifica un gasto existente
 */
async function modificar(id) {
    const formData = recolectarDatosFormulario();
    formData.append('id_gasto', id);
    formData.append('operacion', 'modificar_gasto');

    const respuesta = await Peticiones.enviar(formData, '',true);
    Validador.procesarRespuesta(respuesta, () => {
        modalGasto.hide();
        tabla_gastos.replaceData();
    });
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
    datos.append('operacion', 'consultar_gasto');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos.gasto;
        console.log(data)
        // 1. Clasificación (Con colores para distinguir rápidamente si es Fijo o Variable)
        const clasificacionEl = document.getElementById("vp_clasificacion");
        clasificacionEl.textContent = data.clasificacion || 'N/A';
        
        if (data.clasificacion === 'fijo') {
            clasificacionEl.className = "badge bg-info text-dark fs-6 px-3 py-2 shadow-sm";
        } else if (data.clasificacion === 'variable') {
            clasificacionEl.className = "badge bg-warning text-dark fs-6 px-3 py-2 shadow-sm";
        } else if (data.clasificacion === 'reposicion') {
            clasificacionEl.className = "badge bg-success fs-6 px-3 py-2 shadow-sm";
        }
        else {
            clasificacionEl.className = "badge bg-secondary fs-6 px-3 py-2 shadow-sm";
        }

        // 2. Tipo de Gasto / Categoría
        // Nota: Ajusta "nombre_tipo_gasto" si en tu consulta SQL lo llamaste diferente
        document.getElementById("vp_tipo_gasto").textContent = data.nombre_tipo_gasto || 'No especificada';

        // 3. Proveedor (Si es nulo, mostramos un texto por defecto)
        document.getElementById("vp_proveedor").textContent = data.nombre_proveedor || 'Sin proveedor (No aplica)';

        // 4. Descripción
        document.getElementById("vp_descripcion").textContent = data.descripcion_gasto || 'Sin descripción detallada.';

        // document.getElementById('vista_fecha').textContent = FormatoFechas.formatoUsuario(gasto.ultima_fecha);

        modalVistaPrevia.show();

        setTimeout(async () => {
            await cargarDetallesEnTabla(data.id_gasto);
        }, 200);
    });
}

/**
 * Carga los detalles de un gasto en la tabla de detalles dentro del modal de vista previa
 */
async function cargarDetallesEnTabla(idGasto) {
    const datos = new FormData();
    datos.append('id_gasto', idGasto);
    datos.append('operacion', 'consultar_detalles');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => { 
        const detalles = respuestaServidor.datos || [];

        const columnas = [
            { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
            { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 120, responsive: 0 },
            { title: "Monto", field: "monto", formatter: (cell) => formatearMontoConMoneda(cell.getValue(), cell.getData().metodo_pago), minWidth: 120 },
            { title: "Método", field: "metodo_pago", minWidth: 120 },
            {
                title: "Acciones",
                headerSort: false,
                hozAlign: "center",
                minWidth: 100,
                headerHozAlign: "center",
                formatter: (cell) => `<button data-tooltip="true" class="btn btn-sm btn-primary ver-detalle" value="${cell.getData().id_detalle_gasto}">
                                        <i class="bi bi-eye"></i>
                                        <span class="d-none d-lg-inline ms-2">Ver</span>
                                      </button>`,
                cellClick: function(e, cell) {
                    const btn = e.target.closest('button');
                    if (!btn) return;
                    if (btn.classList.contains('ver-detalle')) {
                        mostrarVistaPreviaDetalle(btn.value); // Llama a la otra ventana modal
                    }
                },
                responsive: 0
            }
        ];

        Tablas.cargarTabuladorEstatico(
            "tabla_detalles_gastos", 
            detalles, 
            columnas, 
            { cssClass: "tabla-vista-previa", paginaSize: 5 }
        );
    });
}

/**
 * Muestra la vista previa de un detalle individual
 */
async function mostrarVistaPreviaDetalle(idDetalle) {
    const datos = new FormData();
    datos.append('id_detalle_gasto', idDetalle);
    datos.append('operacion', 'consulta_especifica_detalles');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const det = respuestaServidor.datos;

        document.getElementById('vista_fecha_detalles').textContent = FormatoFechas.formatoUsuario(det.fecha, 'DD-MM-YYYY');
        document.getElementById('vista_monto_detalles').textContent = formatearMontoConMoneda(det.monto, det.metodo_pago);
        document.getElementById('vista_metodo_pago_detalles').textContent = det.metodo_pago || '';
        document.getElementById('vista_nombre_banco_detalles').textContent = det.nombre_banco || 'No hay banco registrado';
        document.getElementById('vista_referencia_detalles').textContent = det.referencia || 'No hay referencia';
        document.getElementById('vista_descripcion_detalles').textContent = det.descripcion_detalle_gasto || '';

        const img = det.imagen ? `recursos/img/gastos/${det.imagen}` : '';
        document.getElementById('vista_imagen_detalles').src = img;

        modalVistaPreviaDetalles.show();
    });
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
    Validador.procesarRespuesta(respuesta, () => {    
        tabla_gastos.replaceData();
    });
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
    formulario.querySelector('#tipo_gasto_id').disabled = false;
    formulario.querySelector('#proveedor_id').disabled = false;
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
    return metodoPago && metodoPago.toLowerCase().includes('efectivo') ? `${formateado} Bs` : `${formateado} Bs`;
}

/**
 * Capitaliza la primera letra de un texto
 */
function mayuscula(texto) {
    if (!texto) return '';
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
}

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Módulo de Gastos', description: 'Bienvenido. Desde aquí puedes gestionar y controlar todas las salidas de dinero.', side: "bottom", align: 'start' } },
            { element: '[data-bs-target="#modal_gastos"]', popover: { title: 'Nuevo Gasto', description: 'Haz clic en este botón para abrir el formulario y registrar un nuevo gasto.', side: "right", align: 'start' } },
            { element: '#tabla_gastos', popover: { title: 'Tabla de Registros', description: 'Aquí se listan tus gastos. Usa el buscador interno y los botones de acción para Ver, Editar o Eliminar.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#clasificacion', popover: { title: 'Clasificación', description: 'Indica si este gasto es Fijo (mensual/recurrente) o Variable (esporádico).', side: 'bottom', align: 'start' } },
            { element: '#tipo_gasto_id', popover: { title: 'Tipo de Gasto', description: 'Selecciona la categoría exacta a la que pertenece este gasto.', side: 'bottom', align: 'start' } },
            { element: '#descripcion_gasto', popover: { title: 'Descripción', description: 'Redacta el motivo general del gasto con claridad. (Debe tener al menos 10 caracteres).', side: 'top', align: 'start' } },
            { element: '#proveedor_id', popover: { title: 'Datos del Proveedor', description: 'Selecciona la empresa o persona a la que se le pagó, y vincula una Solicitud si el gasto proviene de una.', side: 'top', align: 'start' } },
            { element: '.detalle-gasto', popover: { title: 'Detalles del Pago', description: 'En este bloque registrarás cómo y cuándo pagaste este gasto.', side: 'top', align: 'center' } },
            { element: '.metodo_pago', popover: { title: 'Método Dinámico', description: '¡Importante! Si eliges "Transferencia" o "Pago Móvil", aparecerán automáticamente los campos para que ingreses la Referencia, el Banco y la imagen del Comprobante.', side: 'top', align: 'start' } },
            { element: '#agregar_detalle', popover: { title: 'Pagos Fraccionados', description: '¿Pagaste una parte en efectivo y otra por transferencia? Usa este botón para añadir tantos métodos de pago como necesites.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Una vez valides que todo está correcto, haz clic aquí para registrar el gasto en el sistema.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_gastos',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
