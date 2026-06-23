let tabla_anio_fiscal;
let id_modificar;

const modal = new bootstrap.Modal(document.getElementById("modal_anio_fiscal"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
const form = document.querySelector("#form_anio_fiscal");

window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

document.addEventListener('DOMContentLoaded', consultar);

async function consultar() {
    // Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoEstado = (cell) => {
        const config = obtenerConfigEstadoAnio(cell.getValue());
        
        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    };

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());

    const formatoFechaCierre = (cell) => {
        const row = cell.getData();
        return row.estado === 'CERRADO' ? FormatoFechas.formatoUsuario(row.fecha_cierre) : '<span class="text-muted fst-italic">Aún sin cerrar</span>';
    };

    const formatoBotones = (cell) => {
        const row = cell.getData();
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar los detalles">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        // Condición: Solo mostrar Eliminar si NO está Abierto
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Quitar este elemento">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    // Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Estado", field: "estado", formatter: formatoEstado, minWidth: 140, responsive: 0 },
        { title: "Fecha de Inicio", field: "fecha_inicio", formatter: formatoFecha, minWidth: 170 },
        { title: "Fecha de Cierre", field: "fecha_cierre", formatter: formatoFechaCierre, minWidth: 170 },
        {
            title: "Acciones",
            formatter: formatoBotones,
            headerSort: false,
            hozAlign: "center",
            vertAlign: "middle",
            minWidth: 130,
            widthGrow: 2,
            responsive: 0,
            download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_anio_fiscal;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id) ;
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];

    // Inicializar Tabulator enviando la operación a PHP
    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar_anios_fiscales' }
    };
    
    tabla_anio_fiscal = Tablas.cargarTabulador(contenedor.id, "", columnas, opcionesExtra);

    Tablas.inicializarBuscadorGlobal(tabla_anio_fiscal, "busqueda_global", columnas);
}

function mostrarVistaPrevia(data) {
    const config = obtenerConfigEstadoAnio(data.estado);
    const estadoEl = document.getElementById("vp_estado");
    
    estadoEl.innerHTML = ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    document.getElementById("vp_descripcion").textContent = data.descripcion || 'Sin descripción';
    document.getElementById("vp_fecha_inicio").textContent = FormatoFechas.formatoUsuario(data.fecha_inicio);
    
    document.getElementById("vp_fecha_cierre").textContent = data.estado === 'CERRADO' 
        ? FormatoFechas.formatoUsuario(data.fecha_cierre) 
        : 'Aún sin cerrar';

    modalDetalles.show();
} 

async function registrar() {
    const datos = new FormData(form);

    let fecha_cierre = form.querySelector('#fecha_cierre').value;
    let estado = form.querySelector('#estado').value.toUpperCase();

    datos.append('fecha_cierre', fecha_cierre);
    datos.append('estado', estado);
    datos.append('operacion', 'registrar');

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_anio_fiscal.replaceData(); 
    });
}

async function prepararFormulario(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos,    "", true);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;

        let estado = data.estado.charAt(0) + data.estado.toLowerCase().slice(1);

        form.querySelector('#fecha_inicio').value = data.fecha_inicio;
        form.querySelector('#fecha_cierre').value = data.fecha_cierre;
        form.querySelector('#estado').value = estado;
        form.querySelector('#descripcion').value = data.descripcion;

        document.getElementById('titulo_modal').textContent = 'Modificar Año Fiscal';
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-calendar4-week");
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.querySelector('#boton_formulario').dataset.id = id;

        // Habilitar campos deshabilitados en registro
        form.querySelector('#fecha_cierre').removeAttribute('disabled');

        modal.show();
    });
}

async function modificar() {
    const id = document.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);

    let fecha_cierre = form.querySelector('#fecha_cierre').value;
    let estado = form.querySelector('#estado').value.toUpperCase();

    datos.append('fecha_cierre', fecha_cierre);
    datos.append('estado', estado);
    datos.set('id_anio_fiscal', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_anio_fiscal.replaceData();
    });
}

function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Año Fiscal?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id); }
    );
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        tabla_anio_fiscal.replaceData();
    });
}


/**
 * Procesa el estado del año fiscal y devuelve su configuración visual
 */
function obtenerConfigEstadoAnio(estado) {
    const est = estado || 'ABIERTO';
    let color = "success";
    let icono = "bi-check-circle-fill";
    let textoVisual = "Abierto"; 

    if (est === 'CERRADO') {
        color = "secondary";
        icono = "bi-lock-fill";
        textoVisual = "Cerrado"; 
    }

    return { color, icono, texto: textoVisual };
}

// EVENTOS DEL MODAL
document.getElementById('modal_anio_fiscal').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Año Fiscal';
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-calendar3");
    // document.querySelector('#boton_formulario').textContent = 'Guardar';
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Año Fiscal'
    delete document.querySelector('#boton_formulario').dataset.id;

    // Deshabilitar campos de cierre  en registro
    form.querySelector('#fecha_cierre').setAttribute('disabled', '');
});

// MÓDULO DE AYUDA INTERACTIVA
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Años Fiscales', description: 'Módulo para gestionar los periodos contables del condominio (Apertura y Cierre).', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_anio_fiscal"]', popover: { title: 'Nuevo Periodo', description: 'Registra el inicio de un nuevo año fiscal para comenzar a procesar movimientos.', side: "bottom", align: 'start' } },
            { element: '#tabla_anio_fiscal', popover: { title: 'Historial', description: 'Lista de periodos anteriores. Aquí puedes ver cuáles están cerrados y cuál está activo actualmente.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#fecha_inicio', popover: { title: 'Fecha de Inicio', description: 'Indica cuándo comienza este nuevo periodo fiscal.', side: 'bottom', align: 'start' } },
            { element: '#fecha_cierre', popover: { title: 'Fecha de Cierre', description: 'Esta fecha se llenará automáticamente o se definirá cuando decidas cerrar el año fiscal en el futuro.', side: 'bottom', align: 'start' } },
            { element: '#estado', popover: { title: 'Estado', description: 'Muestra si el año fiscal está "Abierto" (Activo) o "Cerrado" (Histórico).', side: 'top', align: 'start' } },
            { element: '#descripcion', popover: { title: 'Descripción', description: 'Puedes agregar una etiqueta o nombre para identificar este periodo (ej: "Periodo 2026").', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el año fiscal en el sistema.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_anio_fiscal',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
