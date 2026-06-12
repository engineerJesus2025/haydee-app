let tabla_roles;
let id_modificar;
let nombre_anterior;

const modal = new bootstrap.Modal(document.getElementById("modal_roles"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const form = document.querySelector("#form_rol");
const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");

// Exponer funciones para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

document.addEventListener('DOMContentLoaded', consultar);

// CONSULTA Y DATATABLE
async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoNombre = (cell) => {
        let rol = cell.getValue() || "";
        rol = rol.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        const config = obtenerConfigRol(rol);

        return `<div class="d-flex align-items-center fw-bold">
                    <i class="${config.icono} text-${config.color} me-3 fs-5 opacity-75"></i> ${rol}
                </div>`;
    };

    const formatoBotones = (cell) => {
        // Protección especial para el Rol 1 (Administrador Global)
        if (id == 1) {
            return ComponentesUI.crearSoftBadge('secondary', 'bi-lock-fill', 'No Modificable');
        }

        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", headerHozAlign: "center", resizable: false, headerSort: false, },
        { title: "Nombre", field: "nombre", formatter: formatoNombre, minWidth: 200, responsive: 0, widthGrow: 2 },
        {
            title: "Acciones", 
            formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                if (cell.getData().id_rol == 1) return;

                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_rol;
                
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];

    tabla_roles = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    Tablas.inicializarBuscadorGlobal(tabla_roles, "busqueda_global", columnas);
}

// Función asíncrona para Vista Previa
async function mostrarVistaPrevia(data) {
    nombre = data.nombre;
    id_rol = data.id_rol;
    // Asignamos el nombre y mostramos el modal rápidamente con el estado de "Cargando"
    document.getElementById("vp_nombre_rol").textContent = nombre || 'N/A';

    const {icono} = obtenerConfigRol(nombre);
    document.getElementById("vp_icono").className = `bi bi-${icono} me-2`;

    const contenedor = document.getElementById("vp_contenedor_permisos");
    
    // Mostramos loader
    contenedor.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="text-muted mt-2 small">Cargando permisos...</div></div>';
    modalDetalles.show();

    // Hacemos la petición AJAX para traer los permisos específicos
    const formData = new FormData();
    formData.append('operacion', 'consultar_permisos_rol');
    formData.append('id_rol', id_rol);

    // true al final para que sea silencioso y no muestre alertas de "Cargando" del sistema general
    const respuesta = await Peticiones.enviar(formData, "", true); 

    // 3. Renderizamos los datos
    if (respuesta.estatus && respuesta.datos && respuesta.datos.length > 0) {
        
        // Agrupamos los permisos por módulo (Para que no salgan sueltos)
        const agrupados = respuesta.datos.reduce((acc, curr) => {
            if (!acc[curr.modulo]) acc[curr.modulo] = [];
            acc[curr.modulo].push(curr.permiso);
            return acc;
        }, {});

        const coloresPermisos = {
            REGISTRAR: 'primary',
            CONSULTAR: 'info',
            MODIFICAR: 'success',
            ELIMINAR: 'danger',
        };

        // Construimos el HTML
        // Replicamos el diccionario de iconos en JS
        const iconosModulos = {
            'pagos': 'bi-cash-coin',
            'gastos': 'bi-cart-plus',
            'caja_chica': 'bi-bank2',
            'mensualidad': 'bi-piggy-bank-fill',
            'cartelera_virtual': 'bi-tv',
            'apartamentos': 'bi-door-open',
            'solicitud_gasto': 'bi-clipboard-check',
            'presupuesto': 'bi-calculator',
            'anio_fiscal': 'bi-calendar-range',
            'reportes': 'bi-card-checklist',
            'configuracion': 'bi-gear-wide-connected',
            'proveedores': 'bi-truck',
            'bancos': 'bi-bank',
            'tipo_gasto': 'bi-columns-gap',
            'usuarios': 'bi-person-badge-fill',
            'seguridad': 'bi-shield-fill-check',
            'rol': 'bi-person-gear',
            'roles': 'bi-person-gear',
            'bitacora': 'bi-journal-text',
            'permisos': 'bi-key-fill',
            'modulos': 'bi-stack',
            'notificaciones': 'bi-bell-fill',
            'mantenimiento': 'bi-tools'
        };

        // Construimos el HTML
        let html = '';
        for (const [modulo, permisos] of Object.entries(agrupados)) {
            // Badges para los permisos
            const badges = permisos.map(p => {
                const nombreColor = coloresPermisos[p] || 'secondary';
                const textoPermiso = p[0] + p.slice(1).toLowerCase();
                return ComponentesUI.crearSoftBadge(nombreColor, null, textoPermiso);
            }).join(' ');

            // Limpiamos el nombre del módulo (ej: GESTIONAR_GASTOS -> gastos)
            let nombre_limpio = modulo.replace("GESTIONAR_", "").toLowerCase();
            
            // Buscamos el icono (si no existe, usamos la carpeta abierta por defecto)
            let icono = iconosModulos[nombre_limpio] || 'bi-folder2-open';

            // Formateamos el título para mostrarlo bonito (Ej: Gastos)
            let titulo_modulo = nombre_limpio.replace(/_/g, ' ');
            let titulo_capitalizado = titulo_modulo.split(' ').map(palabra => palabra[0].toUpperCase() + palabra.slice(1)).join(' ');

            // Creamos la tarjeta por módulo, inyectando el icono dinámico
            html += `
                <div class="border rounded p-3 card-item ">
                    <div class="fw-bold mb-2 d-flex align-items-center" style="font-size: 1rem; letter-spacing: 0.5px;">
                        <i class="bi ${icono} me-2 text-primary fs-5"></i>
                        ${titulo_capitalizado}
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="font-size: 1rem;">${badges}</div>
                </div>
            `;
        }
        contenedor.innerHTML = html;

    } else {
        // Caso: Rol sin permisos
        contenedor.innerHTML = `
            <div class="alert alert-warning d-flex align-items-center mb-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>Este rol no tiene ningún permiso asignado en el sistema actualmente.</div>
            </div>`;
    }
}

// ===========================================
// ACCIONES (REGISTRAR, MODIFICAR, ELIMINAR)
// ===========================================

async function registrar() {
    const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");
    const permisos = Array.from(checkboxesPermisos)
        .filter(cb => cb.checked)
        .map(cb => {
            // Buscamos la fila (tr) padre para extraer el ID del módulo
            const tr = cb.closest('tr');
            return {
                modulo_id: parseInt(tr.dataset.modulo),
                permiso_id: parseInt(cb.value)
            };
        });

    const formData = new FormData();
    formData.append('operacion', 'registrar_rol');
    formData.append('nombre', document.querySelector('#nombre').value.trim());
    formData.append('permisos', JSON.stringify(permisos));

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_roles.replaceData();
        modal.hide();
    });
}

async function modificar() {
    const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");
    const permisos = Array.from(checkboxesPermisos)
        .filter(cb => cb.checked)
        .map(cb => {
            // Buscamos la fila (tr) padre para extraer el ID del módulo
            const tr = cb.closest('tr');
            return {
                modulo_id: parseInt(tr.dataset.modulo),
                permiso_id: parseInt(cb.value)
            };
        });

    const formData = new FormData();
    formData.append('operacion', 'modificar_rol');
    formData.append('id_rol', id_modificar);
    formData.append('nombre', document.querySelector('#nombre').value.trim());
    formData.append('permisos', JSON.stringify(permisos));

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_roles.replaceData();
        modal.hide();
    });
}

function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Rol?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id); }
    );
}

async function eliminar(id) {
    const formData = new FormData();
    formData.append('operacion', 'eliminar_rol');
    formData.append('id_rol', id);

    const respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_roles.replaceData();
    });
}

async function prepararFormulario(id) {
    id_modificar = id;

    const formData = new FormData();
    formData.append('operacion', 'consultar_rol');
    formData.append('id_rol', id);

    const respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const datos = respuestaServidor.datos;
        
        const inputNombre = document.querySelector("#nombre");
        inputNombre.value = datos.rol.nombre;
        nombre_anterior = datos.rol.nombre; 

        document.querySelectorAll("[name='permisos[]']").forEach(cb => cb.checked = false);

        if (datos.permisos && datos.permisos.length > 0) {
            datos.permisos.forEach(p => {
                const tr = document.querySelector(`tr[data-modulo='${p.modulo_id}']`);
                if (tr) {
                    const cb = tr.querySelector(`[name='permisos[]'][value='${p.permiso_id}']`);
                    if (cb) cb.checked = true;
                }
            });
            
            document.querySelectorAll('tr[data-modulo]').forEach(tr => {
                actualizarSwitchSeleccionarTodo(tr);
                
                const tieneMarcados = Array.from(tr.querySelectorAll("[name='permisos[]']")).some(cb => cb.checked);
                
                if (tieneMarcados) {
                    const collapseEl = tr.querySelector('.accordion-collapse');
                    if (collapseEl && !collapseEl.classList.contains('show')) {
                        // Instanciamos el Collapse de Bootstrap y lo mostramos
                        new bootstrap.Collapse(collapseEl, { show: true });
                    }
                }
            });
        }

        document.querySelector("#titulo_modal").textContent = "Modificar Rol";
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-house-door-fill");
        // document.querySelector("#boton_formulario").innerHTML = `<i class="bi bi-floppy me-1"></i> Guardar Cambios`;
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.querySelector("#boton_formulario").setAttribute("modificar", "true");
        EstadoInputs.limpiar(inputNombre);

        modal.show();
    });
}

function obtenerConfigRol(nombre) {
    let color = "secondary";
    let icono = "bi-person-badge";
    let nombreUpper = (nombre || "Desconocido").toUpperCase();

    switch (nombreUpper) {
        case 'ADMINISTRADOR GLOBAL': icono = 'bi-shield-lock-fill'; color = "warning"; break;
        case 'ADMINISTRADOR': icono = 'bi-shield-check'; color = "primary"; break;
        case 'PROPIETARIO': icono = 'bi-house-door-fill'; color = "success"; break;
        case 'CONTADOR':  icono = 'bi-calculator-fill'; color = "danger"; break;
        case 'PRESIDENTE':  icono = 'bi-person-workspace'; color = "info"; break;
    }
    return { icono, color, texto: nombre };
}

// ============================================
// EVENTOS Y LÓGICA DE INTERFAZ
// ============================================

// Función para verificar y activar/desactivar el switch "Seleccionar Todo"
function actualizarSwitchSeleccionarTodo(tr) {
    const checks = tr.querySelectorAll("[name='permisos[]']");
    const switchTodo = tr.querySelector('.seleccionar_todo');
    if (checks.length === 0 || !switchTodo) return;
    
    const todosMarcados = Array.from(checks).every(cb => cb.checked);
    switchTodo.checked = todosMarcados;
}

// Evento al limpiar el modal
document.getElementById('modal_roles').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Rol';
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-house-door");
    // document.querySelector('#boton_formulario').textContent = 'Guardar';
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Rol';
    document.querySelector("#boton_formulario").removeAttribute('modificar'); // Limpiamos el atributo modificar
    
    // --- Cerrar todos los acordeones abiertos ---
    document.querySelectorAll('.accordion-collapse.show').forEach(acc => {
        // Obtenemos la instancia de Bootstrap si existe, o creamos una para ocultarlo
        let bsCollapse = bootstrap.Collapse.getInstance(acc);
        if (bsCollapse) {
            bsCollapse.hide();
        } else {
            // Respaldo manual por si acaso
            acc.classList.remove('show');
        }
        
        // Restaurar la apariencia del botón del acordeón
        const btn = acc.closest('.accordion-item').querySelector('.accordion-button');
        if (btn) {
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        }
        
        // Limpiamos la animación de la fila (si la hubiera)
        const tr = acc.closest('tr');
        if(tr) tr.classList.remove('fila-resaltada');
    });
});

// Evento para el switch "Seleccionar Todo"
document.querySelectorAll('.seleccionar_todo').forEach(switchCheckbox => {
    switchCheckbox.addEventListener('change', function() {
        const tr = this.closest('tr');
        const checks = tr.querySelectorAll("[name='permisos[]']");
        
        checks.forEach(cb => cb.checked = this.checked);

        if (this.checked) {
            // 1. Abrir acordeón (ya lo tienes)
            const collapseEl = tr.querySelector('.accordion-collapse');
            if (collapseEl && !collapseEl.classList.contains('show')) {
                new bootstrap.Collapse(collapseEl, { show: true });
            }

            // 2. Feedback visual: Reiniciamos la animación quitando y poniendo la clase
            tr.classList.remove('fila-resaltada');
            void tr.offsetWidth; // Truco de JS para forzar el redibujado y que la animación se repita
            tr.classList.add('fila-resaltada');
        }
    });
});

// Evento para checkboxes individuales
document.querySelectorAll("[name='permisos[]']").forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        actualizarSwitchSeleccionarTodo(this.closest('tr'));
    });
});

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión de Roles', description: 'Aquí defines los perfiles de usuario y qué permisos tiene cada uno dentro del sistema.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_roles"]', popover: { title: 'Nuevo Rol', description: 'Crea un nuevo perfil (ej: "Secretaria", "Vigilante") para asignar permisos específicos.', side: "bottom", align: 'start' } },
        { element: '#tabla_roles', popover: { title: 'Lista de Roles', description: 'Aquí ves los roles existentes. El rol de "Administrador Global" y "Propietario" suelen venir predefinidos.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nombre', popover: { title: 'Nombre del Rol', description: 'Escribe un nombre identificativo para este grupo de permisos (Ej: Tesorero).', side: 'bottom', align: 'start' } },
        { element: '#tabla_permisos', popover: { title: 'Matriz de Permisos', description: 'Aquí configuras el acceso. Puedes marcar "Seleccionar Todo" para dar acceso completo a un módulo, o desplegar el botón "PERMISOS" para seleccionar acciones específicas (Registrar, Modificar, Eliminar).', side: 'top', align: 'center' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Guarda la configuración del rol.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_roles',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
