let tabla_anio_fiscal;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_anio_fiscal"), { focus: false });
const form = document.querySelector("#form_anio_fiscal");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

document.getElementById('header-toggle')?.addEventListener('click', () => {
    setTimeout(() => {
        tabla_anio_fiscal?.columns.adjust().draw();
    }, 450);
});

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const parametros = (data) => { data.operacion = 'consultar_anios_fiscales'; };
    const estructura = [
        {
            data: 'estado',
            render: estado => {
                const span = document.createElement('span');
                span.className = estado === 'Cerrada' ? 'badge bg-secondary' : 'badge bg-primary';
                span.textContent = estado;
                return span.outerHTML;
            }
        },
        {
            data: 'fecha_inicio',
            render: fecha => FormatoFechas.formatoUsuario(fecha)
        },
        {
            data: null,
            render: row => row.estado === 'Cerrada' ? FormatoFechas.formatoUsuario(row.fecha_cierre) : 'Aún sin cerrar'
        },
        { data: 'descripcion' },
        {
            data: 'id_anio_fiscal',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    ${window.permiso_modificar ? `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configPost = (row, data) => {
        row.setAttribute('id', `fila-${data.id_anio_fiscal}`);
        row.querySelector('.modificar')?.addEventListener('click', prepararFormulario);
        row.querySelector('.eliminar')?.addEventListener('click', (e) => {
            const id = e.currentTarget.value;
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e01d22',
                confirmButtonText: 'Eliminar'
            }).then(result => result.isConfirmed && eliminar(id));
        });
    };

    tabla_anio_fiscal = Utilidades.crearDataTable(
        'tabla_anio_fiscal',
        estructura,
        parametros,
        configPost
    );
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el año fiscal.');
        return;
    }

    const data = respuesta.datos;

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
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    datos.set('id_anio_fiscal', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
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

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - AÑO FISCAL
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar que la burbuja se ancle bien
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
        { element: '.page-header', popover: { title: 'Años Fiscales', description: 'Módulo para gestionar los periodos contables del condominio (Apertura y Cierre).', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_anio_fiscal"]', popover: { title: 'Nuevo Periodo', description: 'Registra el inicio de un nuevo año fiscal para comenzar a procesar movimientos.', side: "bottom", align: 'start' } },
        { element: '#tabla_anio_fiscal_wrapper', popover: { title: 'Historial', description: 'Lista de periodos anteriores. Aquí puedes ver cuáles están cerrados y cuál está activo actualmente.', side: "top", align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#fecha_inicio', popover: { title: 'Fecha de Inicio', description: 'Indica cuándo comienza este nuevo periodo fiscal.', side: 'bottom', align: 'start' } },
        { element: '#fecha_cierre', popover: { title: 'Fecha de Cierre', description: 'Esta fecha se llenará automáticamente o se definirá cuando decidas cerrar el año fiscal en el futuro.', side: 'bottom', align: 'start' } },
        { element: '#estado', popover: { title: 'Estado', description: 'Muestra si el año fiscal está "Abierto" (Activo) o "Cerrado" (Histórico).', side: 'top', align: 'start' } },
        { element: '#descripcion', popover: { title: 'Descripción', description: 'Puedes agregar una etiqueta o nombre para identificar este periodo (ej: "Periodo 2026").', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el año fiscal en el sistema.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_anio_fiscal');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalHTML && modalHTML.classList.contains('show')) {
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    if (modalHTML) {
        modalHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});