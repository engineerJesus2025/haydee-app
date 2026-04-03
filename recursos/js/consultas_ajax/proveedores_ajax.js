let tabla_proveedores;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_proveedores"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const form = document.querySelector("#form_proveedores");

// Exponer funciones para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_proveedor; 
        
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

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center",},
        
        { title: "Proveedor", field: "nombre_proveedor", minWidth: 200, responsive: 0, widthGrow: 2, },
        { title: "Servicio", field: "servicio", minWidth: 200 },
        // ---------------------------------------------

        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            widthGrow: 3,
            responsive: 0, download: false, headerHozAlign: "center",
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

    tabla_proveedores = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    Tablas.inicializarBuscadorGlobal(tabla_proveedores, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // 1. Nombre del Proveedor
    document.getElementById("vp_nombre_proveedor").textContent = data.nombre_proveedor || 'N/A';

    // 2. RIF
    document.getElementById("vp_rif").textContent = `RIF: ${data.rif || 'No registrado'}`;

    // 3. Servicio que presta
    document.getElementById("vp_servicio").textContent = data.servicio || 'No especificado';

    // 4. Dirección
    document.getElementById("vp_direccion").textContent = data.direccion || 'Dirección no especificada';

    // Mostramos el modal
    // Asumimos que modalDetalles ya está instanciado en tu archivo principal
    modalDetalles.show();
}
// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('operacion', 'registrar_proveedor');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_proveedores.replaceData();
    });
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'consultar_proveedor');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;

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
    });
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('id_proveedor', id);
    datos.set('operacion', 'modificar_proveedor');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_proveedores.replaceData();
    });
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'eliminar_proveedor');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        tabla_proveedores.replaceData();
    });
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
        { element: '#tabla_proveedores', popover: { title: 'Directorio', description: 'Lista de todos los proveedores registrados. Puedes editar sus datos o eliminarlos si ya no prestan servicio.', side: 'top', align: 'center' } }
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