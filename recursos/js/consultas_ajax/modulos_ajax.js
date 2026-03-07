/**
 * modulos_ajax.js
 * Gestión de Módulos - Peticiones AJAX
 */

let tablaModulos;
let id_modificar = null;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

const modalModulo = new bootstrap.Modal(document.getElementById("modal_modulo"), { focus: false });
const formulario = document.getElementById("form_modulo");
const botonFormulario = document.getElementById("boton_formulario");

document.addEventListener('DOMContentLoaded', () => {
    consultar();

    document.getElementById("modal_modulo")?.addEventListener("hide.bs.modal", resetModal);
});

async function consultarModulos() {
    const columnas = [
        { data: "id_modulo" },
        { data: "nombre" },
        {
            data: null,
            render: row => crearBotones(row.id_modulo).innerHTML
        }
    ];

    const parametros = (data) => { data.operacion = 'consultar'; };
    const postCreacion = (row, data) => {
        row.id = `fila-${data.id_modulo}`;
        row.lastElementChild.setAttribute('class','row justify-content-around');
    };

    tablaModulos = Utilidades.crearDataTable('tabla_modulos', columnas, parametros, postCreacion);
}

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        // CAMBIAR AQUÍ EL ID SEGÚN EL MÓDULO (id_proveedor, id_modulo, id_permiso, id_tipo_gasto)
        const id = cell.getData().id_modulo; 
        
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) html += `<button type="button" class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>`;
        if (window.permiso_eliminar) html += `<button type="button" class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        
        // --- CAMBIAR ESTOS FIELDS SEGÚN EL MÓDULO ---
        { title: "Modulo", field: "nombre", minWidth: 150, responsive: 0 },
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
    datos.append('id_modulo', id);
    datos.append('operacion', 'consultar_unico');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const modulo = respuesta.datos;
    document.getElementById('id_modulo').value = modulo.id_modulo;
    document.getElementById('nombre').value = modulo.nombre;

    if (permiso_modificar != 1) {
        botonFormulario.setAttribute('hidden', true);
    }

    botonFormulario.setAttribute('modificar', true);
    botonFormulario.setAttribute('id_modificar', modulo.id_modulo);
    botonFormulario.textContent = 'Guardar Cambios';
    document.getElementById('titulo_modal').textContent = 'Modificar Módulo';
    id_modificar = modulo.id_modulo;

    modalModulo.show();
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalModulo.hide();
    tablaModulos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Módulo registrado correctamente');
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_modulo', id);
    formData.append('operacion', 'modificar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalModulo.hide();
    tablaModulos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Módulo modificado correctamente');
}

botonFormulario?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = botonFormulario.hasAttribute('modificar');
    const accion = esEdicion ? 'modificar' : 'Registrar';

    if (await validarFormulario()) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Desea ${accion.toLowerCase()} este módulo?`,
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
    datos.append('id_modulo', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tablaModulos.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'Módulo eliminado correctamente');
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

async function validarFormulario() {
    const nombre = document.getElementById('nombre');
    if (!Validaciones.keyUp(/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{3,50}$/, nombre, nombre.nextElementSibling, 'Nombre inválido (mínimo 3 letras)')) {
        Utilidades.mensaje('error', 'Error', 'El nombre del módulo no es válido');
        return false;
    }
    return true;
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - MÓDULOS
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
        { element: '.page-header', popover: { title: 'Gestión de Módulos', description: 'Este apartado es técnico. Aquí se registran las secciones del sistema (ej: USUARIOS, PAGOS) para luego asignarles permisos.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_modulo"]', popover: { title: 'Nuevo Módulo', description: 'Registra un nuevo componente del sistema en la base de datos.', side: "bottom", align: 'start' } },
        { element: '#tabla_modulos_wrapper', popover: { title: 'Lista de Módulos', description: 'Catálogo de todos los módulos registrados que componen el sistema.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre', popover: { title: 'Nombre Técnico', description: 'Define el identificador del módulo (Ej: GESTIONAR_PAGOS). Se usa internamente para verificar accesos.', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el módulo en el sistema.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_modulo');

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