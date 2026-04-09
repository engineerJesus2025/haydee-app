/**
 * permisos_ajax.js
 * Gestión de Permisos - Peticiones AJAX
 */

let tablaPermisos;
let id_modificar = null;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

const modalPermiso = new bootstrap.Modal(document.getElementById("modal_permiso"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const formulario = document.getElementById("form_permiso");
const botonFormulario = document.getElementById("boton_formulario");

document.addEventListener('DOMContentLoaded', () => {
    consultar();

    document.getElementById("modal_permiso")?.addEventListener("hide.bs.modal", resetModal);

    document.querySelector("#tabla_permisos tbody")?.addEventListener("click", manejarClickEnTabla);
});

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_permiso; 
        
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

    const formatoNombre = (cell) =>{
        const nombre = cell.getData().accion; 
        let titulo_modulo = nombre.replace(/_/g, ' ').toLowerCase();
        let titulo_capitalizado = titulo_modulo.split(' ').map(palabra => palabra[0].toUpperCase() + palabra.slice(1)).join(' ');

        return titulo_capitalizado;
    }

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Permiso", field:"accion", formatter: formatoNombre, minWidth: 150, responsive: 0 },

        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 130, responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }
                
                if (btn.classList.contains('modificar')) {
                    prepararFormulario({ currentTarget: btn }); 
                }
                
                if (btn.classList.contains('eliminar')) {
                    const id = btn.value;
                    Swal.fire({ title: '¿Estás seguro?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e01d22', confirmButtonText: 'Eliminar' })
                    .then(result => result.isConfirmed && eliminar(id));
                }
            }
        }
    ];

    tablaPermisos = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    Tablas.inicializarBuscadorGlobal(tablaPermisos, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    const nombre = data.accion; 
    let titulo_modulo = nombre.replace(/_/g, ' ').toLowerCase();
    let titulo_capitalizado = titulo_modulo.split(' ').map(palabra => palabra[0].toUpperCase() + palabra.slice(1)).join(' ');

    document.getElementById("vp_icono").className = "bi bi-shield-lock";
    document.getElementById("vp_titulo").textContent = " Permiso";
    document.getElementById("vp_etiqueta").textContent = "Acción Permitida";
    document.getElementById("vp_valor").textContent = titulo_capitalizado || 'N/A';
    
    modalDetalles.show();
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_permiso', id);
    datos.append('operacion', 'consultar_permiso');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const permiso = respuestaServidor.datos;
        document.getElementById('id_permiso').value = permiso.id_permiso;
        document.getElementById('accion').value = permiso.accion;

        if (permisoModificar != 1) {
            botonFormulario.setAttribute('hidden', true);
        }

        botonFormulario.setAttribute('modificar', true);
        botonFormulario.setAttribute('id_modificar', permiso.id_permiso);
        botonFormulario.textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = 'Modificar Permiso';
        id_modificar = permiso.id_permiso;

        modalPermiso.show();
    });
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar_permiso');

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modalPermiso.hide();
        tablaPermisos.replaceData();
    });
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_permiso', id);
    formData.append('operacion', 'modificar_permiso');

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modalPermiso.hide();
        tablaPermisos.replaceData();
    });
}

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
    datos.append('id_permiso', id);
    datos.append('operacion', 'eliminar_permiso');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tablaPermisos.replaceData();
    });
}

function resetModal() {
    formulario.reset();
    botonFormulario.removeAttribute('modificar');
    botonFormulario.removeAttribute('id_modificar');
    botonFormulario.textContent = 'Guardar';
    document.getElementById('titulo_modal').textContent = 'Registrar Permiso';
    id_modificar = null;
    document.getElementById('id_permiso').value = '';
}


// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Catálogo de Permisos', description: 'Aquí se registran las acciones atómicas del sistema (Ej: REGISTRAR, ELIMINAR, CONSULTAR) que luego se asignan a los Roles.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_permiso"]', popover: { title: 'Nueva Acción', description: 'Crea un nuevo permiso en la base de datos. (Solo para uso técnico/avanzado).', side: "bottom", align: 'start' } },
        { element: '#tabla_permisos', popover: { title: 'Lista de Acciones', description: 'Listado de todos los permisos disponibles en el sistema.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#accion', popover: { title: 'Nombre de la Acción', description: 'Define la palabra clave del permiso (Ej: IMPRIMIR_REPORTE).', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la acción para que pueda ser asignada a un rol posteriormente.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_permiso',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
