// roles_ajax.js
let data_table;
let id_modificar;
let nombre_anterior;

const modal = new bootstrap.Modal(document.getElementById("modal_roles"), { focus: false });
const form = document.querySelector("#form_rol");
const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");

// Exponer funciones para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const parametros = (data) => { data.operacion = 'consulta'; };
    const estructura = [
        { data: 'nombre' },
        {
            data: 'id_rol',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    ${window.permiso_modificar ? `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configPost = (row, data) => {
        if (data.id_rol == 1) {
            row.querySelectorAll('.modificar, .eliminar').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        } else {
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
        }
    };

    data_table = Utilidades.crearDataTable('tabla_roles', estructura, parametros, configPost);
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar_rol');

    const permisosSeleccionados = [];
    document.querySelectorAll("[data-modulo]").forEach(moduloTr => {
        const moduloId = moduloTr.dataset.modulo;
        const checks = moduloTr.querySelectorAll("[name='permisos[]']:checked");
        if (checks.length > 0) {
            permisosSeleccionados.push({
                modulo_id: moduloId,
                permisos: Array.from(checks).map(c => c.value)
            });
        }
    });
    datos.set('permisos', JSON.stringify(permisosSeleccionados));

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Rol registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar el rol.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    // Cargar datos del rol
    const datosRol = new FormData();
    datosRol.append('id_rol', id);
    datosRol.append('operacion', 'consulta_especifica');
    const respRol = await Utilidades.query(datosRol);
    if (!respRol?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el rol.');
        return;
    }

    // Cargar permisos asignados al rol
    const datosPermisos = new FormData();
    datosPermisos.append('id_rol', id);
    datosPermisos.append('operacion', 'consulta_permisos');
    const respPermisos = await Utilidades.query(datosPermisos);
    if (!respPermisos?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudieron cargar los permisos.');
        return;
    }

    // Asignar nombre del rol
    form.querySelector('#nombre').value = respRol.datos.nombre;
    nombre_anterior = respRol.datos.nombre;

    // Desmarcar todos los checkboxes primero
    checkboxesPermisos.forEach(cb => cb.checked = false);

    // Crear un Set con identificadores compuestos "modulo_id:permiso_id"
    const permisosAsignados = new Set(
        respPermisos.datos.map(p => `${p.modulo_id}:${p.permiso_id}`)
    );

    console.log('Permisos asignados (compuestos):', Array.from(permisosAsignados));

    // Marcar los checkboxes correspondientes
    checkboxesPermisos.forEach(checkbox => {
        // El checkbox debe tener atributos data-modulo y value (permiso_id)
        const moduloId = checkbox.closest('[data-modulo]')?.dataset.modulo;
        const permisoId = checkbox.value;
        
        if (!moduloId) return; // Si no encuentra el módulo, salir
        
        const identificador = `${moduloId}:${permisoId}`;
        
        if (permisosAsignados.has(identificador)) {
            checkbox.checked = true;
            
            // Expandir el acordeón que contiene este checkbox
            const accordionItem = checkbox.closest('.accordion-item');
            if (accordionItem) {
                const accordionCollapse = accordionItem.querySelector('.accordion-collapse');
                const accordionButton = accordionItem.querySelector('.accordion-button');
                
                if (accordionCollapse) {
                    accordionCollapse.classList.add('show');
                }
                if (accordionButton) {
                    accordionButton.classList.remove('collapsed');
                    accordionButton.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });

    // Actualizar interfaz del modal
    document.getElementById('titulo_modal').textContent = 'Modificar Rol';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    datos.set('id_rol', id);
    datos.set('operacion', 'modificar');

    const permisosSeleccionados = [];
    document.querySelectorAll("[data-modulo]").forEach(moduloTr => {
        const moduloId = moduloTr.dataset.modulo;
        const checks = moduloTr.querySelectorAll("[name='permisos[]']:checked");
        if (checks.length > 0) {
            permisosSeleccionados.push({
                modulo_id: moduloId,
                permisos: Array.from(checks).map(c => c.value)
            });
        }
    });
    datos.set('permisos', JSON.stringify(permisosSeleccionados));

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Rol actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar el rol.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_rol', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Rol eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar el rol.');
    }
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_roles').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Rol';
    form.querySelector('#boton_formulario').textContent = 'Guardar';
    delete form.querySelector('#boton_formulario').dataset.id;
    document.querySelectorAll('.accordion-collapse').forEach(acc => {
        acc.classList.remove('show');
        acc.previousElementSibling?.querySelector('button')?.classList.add('collapsed');
        acc.previousElementSibling?.querySelector('button')?.setAttribute('aria-expanded', 'false');
    });
});

document.querySelectorAll('.seleccionar_todo').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const checks = this.closest('tr').querySelectorAll("[name='permisos[]']");
        checks.forEach(cb => cb.checked = this.checked);
    });
});

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - ROLES
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar que la burbuja se ancle con precisión
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
        { element: '.page-header', popover: { title: 'Gestión de Roles', description: 'Aquí defines los perfiles de usuario y qué permisos tiene cada uno dentro del sistema.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_roles"]', popover: { title: 'Nuevo Rol', description: 'Crea un nuevo perfil (ej: "Secretaria", "Vigilante") para asignar permisos específicos.', side: "bottom", align: 'start' } },
        { element: '#tabla_roles_wrapper', popover: { title: 'Lista de Roles', description: 'Aquí ves los roles existentes. El rol de "Administrador Global" y "Propietario" suelen venir predefinidos.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre', popover: { title: 'Nombre del Rol', description: 'Escribe un nombre identificativo para este grupo de permisos (Ej: Tesorero).', side: 'bottom', align: 'start' } },
        { element: '#tabla_permisos', popover: { title: 'Matriz de Permisos', description: 'Aquí configuras el acceso. Puedes marcar "Seleccionar Todo" para dar acceso completo a un módulo, o desplegar el botón "PERMISOS" para seleccionar acciones específicas (Registrar, Modificar, Eliminar).', side: 'top', align: 'center' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Guarda la configuración del rol.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_roles');

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