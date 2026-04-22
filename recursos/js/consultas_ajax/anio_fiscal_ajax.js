let tabla_anio_fiscal;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_anio_fiscal"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
const form = document.querySelector("#form_anio_fiscal");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y TABULATOR
// ============================================
async function consultar() {
    // Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoEstado = (cell) => {
        const config = obtenerConfigEstadoAnio(cell.getValue());
        
        return `<span class="badge bg-${config.color} bg-opacity-10 ${config.claseTextoBorder} px-3 py-2 shadow-sm text-nowrap" style="font-size: .85rem;">
                    <i class="bi ${config.icono} me-1"></i>
                    ${config.texto}
                </span>`;
    };

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());

    const formatoFechaCierre = (cell) => {
        const row = cell.getData();
        return row.estado === 'Cerrada' ? FormatoFechas.formatoUsuario(row.fecha_cierre) : '<span class="text-muted fst-italic">Aún sin cerrar</span>';
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_anio_fiscal;
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

    // 3. Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 140, responsive: 0 },
        { title: "Fecha de Inicio", field: "fecha_inicio", formatter: formatoFecha, minWidth: 170 },
        { title: "Fecha de Cierre", field: "fecha_cierre", formatter: formatoFechaCierre, minWidth: 170 },
        {
            title: "Acciones",
            formatter: formatoBotones,
            headerSort: false,
            hozAlign: "center",
            vertAlign: "middle",
            minWidth: 130,
            widthGrow: 2,
            responsive: 0,
            download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;

                // Emulamos el evento para que tus funciones prepararFormulario funcionen sin cambios
                const mockEvent = { currentTarget: btn }; 

                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }

                if (btn.classList.contains('modificar')) {
                    prepararFormulario(mockEvent);
                } else if (btn.classList.contains('eliminar')) {
                    const id = btn.value;
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e01d22',
                        confirmButtonText: 'Eliminar'
                    }).then(result => result.isConfirmed && eliminar(id));
                }
            }
        }
    ];

    // 4. Inicializar Tabulator enviando la operación a PHP
    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar_anios_fiscales' }
    };
    
    tabla_anio_fiscal = Tablas.cargarTabulador(contenedor.id, "", columnas, opcionesExtra);

    Tablas.inicializarBuscadorGlobal(tabla_anio_fiscal, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // 1. Estado con Soft Badges
    const config = obtenerConfigEstadoAnio(data.estado);
    const estadoEl = document.getElementById("vp_estado");
    
    // Limpiamos las clases de texto plano e inyectamos el Badge
    estadoEl.className = "mt-2 mb-0"; 
    estadoEl.innerHTML = `
        <span class="badge bg-${config.color} bg-opacity-10 ${config.claseTextoBorder} fs-6 px-3 py-2 shadow-sm text-nowrap">
            <i class="bi ${config.icono} me-1"></i>
            ${config.texto}
        </span>
    `;

    // 2. Descripción
    document.getElementById("vp_descripcion").textContent = data.descripcion || 'Sin descripción';

    // 3. Fechas (Reutilizamos la clase FormatoFechas que ya usas en la tabla)
    document.getElementById("vp_fecha_inicio").textContent = FormatoFechas.formatoUsuario(data.fecha_inicio);
    
    // Mismo criterio de la tabla: si no está cerrada, mostramos un aviso
    document.getElementById("vp_fecha_cierre").textContent = data.estado === 'Cerrada' 
        ? FormatoFechas.formatoUsuario(data.fecha_cierre) 
        : 'Aún sin cerrar';

    // Mostramos el modal
    modalDetalles.show();
}   

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar');

    const respuesta = await Peticiones.enviar(datos, "", true);

    // Le delegamos toda la validación de errores y alertas al Helper
    Validador.procesarRespuesta(respuesta, () => {
        // Esto solo se ejecuta si la respuesta fue exitosa (estatus: true)
        modal.hide();
        tabla_anio_fiscal.replaceData(); // Asumiendo que recarga los datos
    });
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;

        form.querySelector('#fecha_inicio').value = data.fecha_inicio;
        form.querySelector('#fecha_cierre').value = data.fecha_cierre;
        form.querySelector('#estado').value = data.estado;
        form.querySelector('#descripcion').value = data.descripcion;

        document.getElementById('titulo_modal').textContent = 'Modificar Año Fiscal';
        form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
        form.querySelector('#boton_formulario').dataset.id = id;

        // Habilitar campos deshabilitados en registro
        form.querySelector('#fecha_cierre').removeAttribute('disabled');
        form.querySelector('#estado').removeAttribute('disabled');

        modal.show();
    });
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    datos.set('id_anio_fiscal', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_anio_fiscal.replaceData();
    });
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        tabla_anio_fiscal.replaceData();
    });
}


/**
 * Procesa el estado del año fiscal y devuelve su configuración visual (Soft Badge)
 * @param {string} estado - El estado (Ej: 'Abierta', 'Cerrada')
 * @returns {object} Configuración visual
 */
function obtenerConfigEstadoAnio(estado) {
    const est = estado || 'Abierta';
    let color = "success";
    let icono = "bi-check-circle-fill";
    let claseTextoBorder = "text-success border border-success";

    if (est === 'Cerrada') {
        color = "secondary";
        icono = "bi-lock-fill";
        claseTextoBorder = "text-secondary border border-secondary";
    }

    return { 
        color: color, 
        claseTextoBorder: claseTextoBorder, 
        icono: icono, 
        texto: est 
    };
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_anio_fiscal').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Año Fiscal';
    form.querySelector('#boton_formulario').textContent = 'Guardar';
    delete form.querySelector('#boton_formulario').dataset.id;

    // Deshabilitar campos de cierre y estado en registro
    form.querySelector('#fecha_cierre').setAttribute('disabled', '');
    form.querySelector('#estado').setAttribute('disabled', '');
});

// document.getElementById('modal_anio_fiscal').addEventListener('show.bs.modal', () => {
    
// });

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Años Fiscales', description: 'Módulo para gestionar los periodos contables del condominio (Apertura y Cierre).', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_anio_fiscal"]', popover: { title: 'Nuevo Periodo', description: 'Registra el inicio de un nuevo año fiscal para comenzar a procesar movimientos.', side: "bottom", align: 'start' } },
            { element: '#tabla_anio_fiscal', popover: { title: 'Historial', description: 'Lista de periodos anteriores. Aquí puedes ver cuáles están cerrados y cuál está activo actualmente.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#fecha_inicio', popover: { title: 'Fecha de Inicio', description: 'Indica cuándo comienza este nuevo periodo fiscal.', side: 'bottom', align: 'start' } },
            { element: '#fecha_cierre', popover: { title: 'Fecha de Cierre', description: 'Esta fecha se llenará automáticamente o se definirá cuando decidas cerrar el año fiscal en el futuro.', side: 'bottom', align: 'start' } },
            { element: '#estado', popover: { title: 'Estado', description: 'Muestra si el año fiscal está "Abierto" (Activo) o "Cerrado" (Histórico).', side: 'top', align: 'start' } },
            { element: '#descripcion', popover: { title: 'Descripción', description: 'Puedes agregar una etiqueta o nombre para identificar este periodo (ej: "Periodo 2026").', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el año fiscal en el sistema.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_anio_fiscal',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
