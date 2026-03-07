let tabla_proveedores;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_proveedores"), { focus: false });
const form = document.querySelector("#form_proveedores");

// Exponer funciones para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        // CAMBIAR AQUÍ EL ID SEGÚN EL MÓDULO (id_proveedor, id_modulo, id_permiso, id_tipo_gasto)
        const id = cell.getData().id_proveedor; 
        
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) html += `<button type="button" class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>`;
        if (window.permiso_eliminar) html += `<button type="button" class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        
        // --- CAMBIAR ESTOS FIELDS SEGÚN EL MÓDULO ---
        { title: "PROVEEDOR", field: "nombre_proveedor", minWidth: 150, responsive: 0 },
        { title: "SERVICIO", field: "servicio", minWidth: 150 },
        { title: "RIF", field: "rif", minWidth: 120 },
        { title: "DIRECCIÓN", field: "direccion", minWidth: 200 },
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
    tabla_proveedores = Utilidades.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } }); // NOTA: modulos y permisos usan 'consultar', revisa el tuyo.

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

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('operacion', 'registrar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_proveedores.replaceData();
        Utilidades.mensaje('success', 'Éxito', 'Proveedor registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'consultar_proveedor');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el proveedor.');
        return;
    }

    const data = respuesta.datos;

    form.querySelector('#nombre_proveedor').value = data.nombre_proveedor;
    form.querySelector('#servicio').value = data.servicio;
    const tipoDoc = data.rif.charAt(0);
    const rifNum = data.rif.slice(1);
    form.querySelector('#tipo_documento').value = tipoDoc;
    form.querySelector('#rif').value = rifNum;
    form.querySelector('#rif').removeAttribute('disabled');
    form.querySelector('#direccion').value = data.direccion;

    document.getElementById('titulo_modal').textContent = 'Modificar Proveedor';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('id_proveedor', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_proveedores.replaceData();
        Utilidades.mensaje('success', 'Éxito', 'Proveedor actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        tabla_proveedores.replaceData();
        Utilidades.mensaje('success', 'Éxito', 'Proveedor eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_proveedores').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Proveedor';
    form.querySelector('#boton_formulario').textContent = 'Registrar';
    form.querySelector('#rif').setAttribute('disabled','');
    delete form.querySelector('#boton_formulario').dataset.id;
});

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - PROVEEDORES
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar que la burbuja se ancle con precisión milimétrica
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
        { element: '.page-header', popover: { title: 'Gestión de Proveedores', description: 'Aquí administras el directorio de empresas y personas que prestan servicios al condominio.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_proveedores"]', popover: { title: 'Nuevo Proveedor', description: 'Registra un nuevo prestador de servicios (ej: Hidrolara, Corpoelec, Jardineros) para poder asignarle gastos.', side: "bottom", align: 'start' } },
        { element: '#tabla_proveedores_wrapper', popover: { title: 'Directorio', description: 'Lista de todos los proveedores registrados. Puedes editar sus datos o eliminarlos si ya no prestan servicio.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre_proveedor', popover: { title: 'Razón Social', description: 'Escribe el nombre de la empresa o la persona natural.', side: 'bottom', align: 'start' } },
        { element: '#servicio', popover: { title: 'Tipo de Servicio', description: 'Indica qué servicio presta (Ej: Agua, Electricidad, Limpieza, Mantenimiento).', side: 'bottom', align: 'start' } },
        { element: '.rif', popover: { title: 'Identificación Fiscal', description: 'Selecciona el tipo de documento (J, V, G, E) e ingresa el número de RIF o Cédula.', side: 'top', align: 'start' } },
        { element: '#direccion', popover: { title: 'Ubicación', description: 'Dirección fiscal o física del proveedor.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra al proveedor en el sistema.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_proveedores');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalHTML && modalHTML.classList.contains('show')) {
                // Si el modal está abierto, lanzamos el tour del formulario
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                // Si estamos en la tabla principal
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    // Limpieza de seguridad al cerrar el modal
    if (modalHTML) {
        modalHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});