/**
 * permisos_ajax.js
 * Gestión de Permisos - Peticiones AJAX
 */

let tablaPermisos;
let id_modificar = null;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

const modalPermiso = new bootstrap.Modal(document.getElementById("modal_permiso"), { focus: false });
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
        // CAMBIAR AQUÍ EL ID SEGÚN EL MÓDULO (id_proveedor, id_modulo, id_permiso, id_tipo_gasto)
        const id = cell.getData().id_permiso; 
        
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) html += `<button type="button" class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>`;
        if (window.permiso_eliminar) html += `<button type="button" class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        
        // --- CAMBIAR ESTOS FIELDS SEGÚN EL MÓDULO ---
        { title: "Permiso", field: "accion", minWidth: 150, responsive: 0 },
        // ---------------------------------------------

        {
            title: "ACCIONES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 100, responsive: 0, download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                // NOTA: En modulo_ajax y permiso_ajax usabas prepararEdicion(id)
                // En tipo_gasto_ajax usabas modificar_formulario(e)
                // En proveedores usabas prepararFormulario(e)
                // Asegúrate de llamar a la función que le corresponde a cada archivo.
                
                if (btn.classList.contains('modificar')) {
                    prepararFormulario({ currentTarget: btn }); // Para proveedores
                }
                
                if (btn.classList.contains('eliminar')) {
                    const id = btn.value;
                    Swal.fire({ title: '¿Estás seguro?', text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e01d22', confirmButtonText: 'Eliminar' })
                    .then(result => result.isConfirmed && eliminar(id));
                }
            }
        }
    ];

    // Cambiar 'tabla_proveedores' por la variable que maneje la tabla de ese archivo
    tablaPermisos = Utilidades.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } }); // NOTA: modulos y permisos usan 'consultar', revisa el tuyo.

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_anio_fiscal.setFilter([filtros]);
        });
    }
}


async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_permiso', id);
    datos.append('operacion', 'consultar_unico');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const permiso = respuesta.datos;
    document.getElementById('id_permiso').value = permiso.id_permiso;
    document.getElementById('accion').value = permiso.accion;

    if (permiso_modificar != 1) {
        botonFormulario.setAttribute('hidden', true);
    }

    botonFormulario.setAttribute('modificar', true);
    botonFormulario.setAttribute('id_modificar', permiso.id_permiso);
    botonFormulario.textContent = 'Guardar Cambios';
    document.getElementById('titulo_modal').textContent = 'Modificar Permiso';
    id_modificar = permiso.id_permiso;

    modalPermiso.show();
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalPermiso.hide();
    tablaPermisos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Permiso registrado correctamente');
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_permiso', id);
    formData.append('operacion', 'modificar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalPermiso.hide();
    tablaPermisos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Permiso modificado correctamente');
}

botonFormulario?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = botonFormulario.hasAttribute('modificar');
    const accion = esEdicion ? 'modificar' : 'Registrar';

    if (await validarFormulario()) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Desea ${accion.toLowerCase()} este permiso?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1b8a40',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                if (esEdicion) {
                    modificar(botonFormulario.getAttribute('id_modificar'));
                } else {
                    registrar();
                }
            }
        });
    }
});

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
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tablaPermisos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Permiso eliminado correctamente');
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

async function validarFormulario() {
    const accion = document.getElementById('accion');
    if (!Validaciones.keyUp(/^[A-Za-z_]{3,50}$/, accion, accion.nextElementSibling, 'Acción inválida (solo letras y guión bajo)')) {
        Utilidades.mensaje('error', 'Error', 'El nombre de la acción no es válido');
        return false;
    }
    return true;
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - PERMISOS
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar la precisión de la burbuja
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // Configuración base
    const configBase = {
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
        }
    };

    // 1. TOUR VISTA PRINCIPAL
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Catálogo de Permisos', description: 'Aquí se registran las acciones atómicas del sistema (Ej: REGISTRAR, ELIMINAR, CONSULTAR) que luego se asignan a los Roles.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_permiso"]', popover: { title: 'Nueva Acción', description: 'Crea un nuevo permiso en la base de datos. (Solo para uso técnico/avanzado).', side: "bottom", align: 'start' } },
        { element: '#tabla_permisos_wrapper', popover: { title: 'Lista de Acciones', description: 'Listado de todos los permisos disponibles en el sistema.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#accion', popover: { title: 'Nombre de la Acción', description: 'Define la palabra clave del permiso (Ej: IMPRIMIR_REPORTE).', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la acción para que pueda ser asignada a un rol posteriormente.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_permiso');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalHTML && modalHTML.classList.contains('show')) {
                // Si el modal está abierto
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                // Si estamos en la vista principal
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    // Limpieza de seguridad al cerrar modal
    if (modalHTML) {
        modalHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});