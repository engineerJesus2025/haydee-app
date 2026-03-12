let tabla_anio_fiscal;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_anio_fiscal"), { focus: false });
const form = document.querySelector("#form_anio_fiscal");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y TABULATOR
// ============================================
async function consultar() {
    // 1. Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // 2. Formateadores Específicos del Módulo
    const formatoEstado = (cell) => {
        const estado = cell.getValue();
        const clase = estado === 'Cerrada' ? 'badge bg-secondary' : 'badge bg-primary';
        return `<span class="${clase}">${estado}</span>`;
    };

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());

    const formatoFechaCierre = (cell) => {
        const row = cell.getData();
        return row.estado === 'Cerrada' ? FormatoFechas.formatoUsuario(row.fecha_cierre) : '<span class="text-muted fst-italic">Aún sin cerrar</span>';
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_anio_fiscal;
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) {
            html += `<button data-tooltip="true" class="btn btn-success btn-sm modificar" value="${id}" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                    </button>`;
        }
        if (window.permiso_eliminar) {
            html += `<button data-tooltip="true" class="btn btn-danger btn-sm eliminar" value="${id}" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    // 3. Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 100, responsive: 0 },
        { title: "Fecha de Inicio", field: "fecha_inicio", formatter: formatoFecha, minWidth: 150 },
        { title: "Fecha de Cierre", field: "fecha_cierre", formatter: formatoFechaCierre, minWidth: 150 },
        { title: "Descripción", field: "descripcion", minWidth: 150 },
        {
            title: "Acciones",
            formatter: formatoBotones,
            headerSort: false,
            hozAlign: "center",
            vertAlign: "middle",
            minWidth: 100,
            responsive: 0,
            download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;

                // Emulamos el evento para que tus funciones prepararFormulario funcionen sin cambios
                const mockEvent = { currentTarget: btn }; 

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

    // 5. Buscador Global Dinámico
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
    datos.set('operacion', 'registrar');

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Año fiscal registrado correctamente.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (!respuesta?.estatus) {
        Alertas.mostrar('error', 'Error', 'No se pudo cargar el año fiscal.');
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

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Año fiscal actualizado correctamente.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Peticiones.enviar(datos);
    if (respuesta?.estatus) {
        tabla_anio_fiscal.replaceData();
        Alertas.mostrar('success', 'Éxito', 'Año fiscal eliminado correctamente.');
    } else {
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
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
        { element: '#tabla_anio_fiscal', popover: { title: 'Historial', description: 'Lista de periodos anteriores. Aquí puedes ver cuáles están cerrados y cuál está activo actualmente.', side: "top", align: 'center' } }
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