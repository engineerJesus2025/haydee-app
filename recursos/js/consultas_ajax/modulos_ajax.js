/**
 * modulos_ajax.js
 * Gestión de Módulos - Peticiones AJAX
 */

let tablaModulos;
let id_modificar = null;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

const modalModulo = new bootstrap.Modal(document.getElementById("modal_modulo"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const formulario = document.getElementById("form_modulo");
const botonFormulario = document.getElementById("boton_formulario");

document.addEventListener('DOMContentLoaded', () => {
    consultar();
    document.getElementById("modal_modulo")?.addEventListener("hide.bs.modal", resetModal);
});

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_modulo; 
        
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
        const nombre = cell.getData().nombre; 
        let titulo_modulo = nombre.replace(/_/g, ' ').toLowerCase();
        let titulo_capitalizado = titulo_modulo.split(' ').map(palabra => palabra[0].toUpperCase() + palabra.slice(1)).join(' ');

        return titulo_capitalizado;
    }

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        
        { title: "Módulo", field:"nombre" , formatter: formatoNombre, minWidth: 150, responsive: 0 },

        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", 
            minWidth: 130, responsive: 0, download: false, headerHozAlign: "center",
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

    const filtroNombre = (data, valorBuscado) => {
        if (!data.nombre) return false;
        let tituloFormateado = data.nombre.replace(/_/g, ' ').toLowerCase();
        return tituloFormateado.includes(valorBuscado);
    };

    tablaModulos = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    Tablas.inicializarBuscadorGlobal(tablaModulos, "busqueda_global", columnas, filtroNombre);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    const nombre = data.nombre; 
    let titulo_modulo = nombre.replace(/_/g, ' ').toLowerCase();
    let titulo_capitalizado = titulo_modulo.split(' ').map(palabra => palabra[0].toUpperCase() + palabra.slice(1)).join(' ');

    document.getElementById("vp_icono").className = "bi bi-box-seam";
    document.getElementById("vp_titulo").textContent = " Módulo";
    document.getElementById("vp_etiqueta").textContent = "Nombre Técnico";
    document.getElementById("vp_valor").textContent = titulo_capitalizado || 'N/A';
    
    modalDetalles.show();
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_modulo', id);
    datos.append('operacion', 'consultar_modulo');

    const respuesta = await Peticiones.enviar(datos, '', true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const modulo = respuestaServidor.datos;
        document.getElementById('id_modulo').value = modulo.id_modulo;
        document.getElementById('nombre').value = modulo.nombre;

        if (permisoModificar != 1) {
            botonFormulario.setAttribute('hidden', true);
        }

        botonFormulario.setAttribute('modificar', true);
        botonFormulario.setAttribute('id_modificar', modulo.id_modulo);
        botonFormulario.textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = 'Modificar Módulo';
        id_modificar = modulo.id_modulo;

        modalModulo.show();
    });
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar_modulo');

    const respuesta = await Peticiones.enviar(formData, '', true);
    Validador.procesarRespuesta(respuesta, () => {
        modalModulo.hide();
        tablaModulos.replaceData();
    });
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_modulo', id);
    formData.append('operacion', 'modificar_modulo');

    const respuesta = await Peticiones.enviar(formData, '', true);
    Validador.procesarRespuesta(respuesta, () => {
        modalModulo.hide();
        tablaModulos.replaceData();
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
    datos.append('id_modulo', id);
    datos.append('operacion', 'eliminar_modulo');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tablaModulos.replaceData();
    });
}

function resetModal() {
    formulario.reset();
    botonFormulario.removeAttribute('modificar');
    botonFormulario.removeAttribute('id_modificar');
    botonFormulario.textContent = 'Guardar';
    document.getElementById('titulo_modal').textContent = 'Registrar Módulo';
    id_modificar = null;
    document.getElementById('id_modulo').value = '';
}


// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión de Módulos', description: 'Este apartado es técnico. Aquí se registran las secciones del sistema (ej: USUARIOS, PAGOS) para luego asignarles permisos.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_modulo"]', popover: { title: 'Nuevo Módulo', description: 'Registra un nuevo componente del sistema en la base de datos.', side: "bottom", align: 'start' } },
        { element: '#tabla_modulos', popover: { title: 'Lista de Módulos', description: 'Catálogo de todos los módulos registrados que componen el sistema.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nombre', popover: { title: 'Nombre Técnico', description: 'Define el identificador del módulo (Ej: GESTIONAR_PAGOS). Se usa internamente para verificar accesos.', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el módulo en el sistema.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_modulo',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
