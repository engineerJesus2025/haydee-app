// Variables Globales
let tabla_tipo_gasto;
let id_modificar;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

let boton_formulario = document.querySelector("#boton_formulario"); 
let modal = new bootstrap.Modal(document.getElementById("modal_tipo_gasto"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
let formulario_usar = document.querySelector(`#form_tipo_gasto`); 

// Selectores Maestro-Detalle
const contenedorConceptos = document.getElementById('contenedor_conceptos');
const plantillaConcepto = document.getElementById('plantilla_concepto');

document.addEventListener('DOMContentLoaded', () => {
    consultar();
    
    // Evento para añadir conceptos vacios manualmente
    document.getElementById('btn_agregar_concepto')?.addEventListener('click', () => agregarFilaConcepto());

    // Delegacion de eventos para eliminar conceptos dentro del contenedor
    contenedorConceptos?.addEventListener('click', (e) => {
        const btnEliminar = e.target.closest('.btn_eliminar_concepto');
        if (btnEliminar) {
            e.preventDefault();
            const filas = contenedorConceptos.querySelectorAll('.fila-concepto');
            // al menos una fila vacía por usabilidad
            if (filas.length > 1) {
                btnEliminar.closest('.fila-concepto').remove();
            } else {
                Alertas.mostrar('warning', 'Atención', 'Debe registrar al menos un concepto para la partida.');
            }
        }
    });
});

// Resetear modal al cerrarlo
document.querySelector(`#modal_tipo_gasto`).addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");   
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Tipo Gasto';
    document.getElementById('titulo_modal').textContent = "Registrar Tipo de Gasto";    
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-clipboard-plus");
    
    // Limpiar filas dinámicas de conceptos
    if (contenedorConceptos) {
        contenedorConceptos.querySelectorAll(".fila-concepto").forEach((div,index)=>{
            if (index !== 0) contenedorConceptos.removeChild(div);
            if (index === 0) div.querySelector('input[name="nombre_concepto[]"]').value = '';
        });
    }

    document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
}); 

// Inyectar fila clonada del Template HTML
function agregarFilaConcepto(id = "", nombre = "") {
    if (!plantillaConcepto || !contenedorConceptos) return;

    const clon = plantillaConcepto.content.cloneNode(true);
    
    // Mapear inputs internos de la fila clonada
    clon.querySelector('input[name="id_concepto[]"]').value = id;
    const inputNombre = clon.querySelector('input[name="nombre_concepto[]"]');
    inputNombre.value = nombre;

    contenedorConceptos.appendChild(clon);
}

function envio(operacion) { 
    if (operacion === "modificar") {
        modificar(boton_formulario.getAttribute("id_modificar"));
    } else if(operacion === "Registrar"){
        registrar();
    } else {
        Alertas.mostrar('error', 'Atención', 'Ha ocurrido un error durante la operación');
    }
}

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Desglose">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar esta estructura">
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
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center" },
        { 
            title: "Partida de Gasto", 
            field: "nombre_tipo_gasto", 
            minWidth: 250, 
            responsive: 0, 
            widthGrow: 2, 
            formatter: (cell) => {
                const valor = cell.getValue();
                const icono = obtenerIconoTipoGasto(valor);
                return `<div class="fw-bold"><i class="bi bi-${icono} me-2 text-primary fs-5"></i>${valor.toUpperCase()}</div>`;
            }
        },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_tipo_gasto;
                
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(id);
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];

    tabla_tipo_gasto = Tablas.cargarTabulador(contenedor.id, "", columnas, { 
        parametrosExtra: { operacion: 'consultar' }
    });

    Tablas.inicializarBuscadorGlobal(tabla_tipo_gasto, "busqueda_global", columnas);
}

// Consulta para armar la lista del modal Ver Más
async function mostrarVistaPrevia(id) {
    const listaHtml = document.getElementById("vp_lista_conceptos");
    if (!listaHtml) return;

    listaHtml.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
    modalDetalles.show();

    let datos = new FormData();
    datos.append("id_tipo_gasto", id);
    datos.append('operacion', 'consultar_tipo_gasto');

    const respuesta = await Peticiones.enviar(datos, "", true);
    if (respuesta.estatus && respuesta.datos) {
        const data = respuesta.datos;
        document.getElementById("vp_valor").textContent = data.nombre_tipo_gasto || 'N/A';
        
        if (data.conceptos && data.conceptos.length > 0) {
            listaHtml.innerHTML = data.conceptos.map(c => `
                <li class="list-group-item d-flex align-items-center fw-semibold py-2">
                    <i class="bi bi-dot text-primary fs-4 me-1"></i>${c.nombre_concepto}
                </li>
            `).join('');
        } else {
            listaHtml.innerHTML = '<li class="list-group-item text-center text-muted py-2">No posee conceptos configurados.</li>';
        }
    } else {
        listaHtml.innerHTML = '<li class="list-group-item text-center text-danger py-2">Error al recuperar desglose.</li>';
    }
}

async function registrar() {
    let datos = new FormData(formulario_usar);
    datos.append('operacion', 'registrar_tipo_gasto'); 
    
    let respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_tipo_gasto.replaceData();
    });
}

async function prepararFormulario(id) {
    let datos = new FormData();
    datos.append("id_tipo_gasto", id);
    datos.append('operacion', 'consultar_tipo_gasto');

    let respuesta = await Peticiones.enviar(datos, "", true);   
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos;
        formulario_usar.querySelector("#nombre_tipo_gasto").value = data.nombre_tipo_gasto;

        // Población dinámica de los renglones (Conceptos) en el formulario
        if (contenedorConceptos) {
            contenedorConceptos.innerHTML = "";
            if (data.conceptos && data.conceptos.length > 0) {
                data.conceptos.forEach(c => agregarFilaConcepto(c.id_concepto, c.nombre_concepto));
            } else {
                agregarFilaConcepto(); // Si está limpio, dejamos una fila base vacía
            }
        }

        if(!permisoModificar){
            boton_formulario.setAttribute("hidden", true);
            boton_formulario.setAttribute("disabled", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", data.id_tipo_gasto);
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = "Modificar Tipo de Gasto";
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-clipboard-pulse");

        id_modificar = id;
        modal.show();
    });
}

async function modificar(id) {  
    let datos = new FormData(formulario_usar);
    datos.append("id_tipo_gasto", id);
    datos.append('operacion', 'modificar_tipo_gasto');

    let respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_tipo_gasto.replaceData();
    });
}

function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Tipo de Gasto?",
        "Esta acción deshabilitará la partida y todos sus conceptos asociados en cascada.",
        "error",
        () => { eliminar(id); }
    );
}

async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_tipo_gasto", id);
    datos.append('operacion', 'eliminar_tipo_gasto');

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_tipo_gasto.replaceData();
    });
}

function obtenerIconoTipoGasto(nombre){
    let icono = "tag";
    let nombreMinuscula = nombre.toLowerCase();

    if (nombreMinuscula.includes("caja chica")) {
        icono = "box-seam";
    } else if (nombreMinuscula.includes("servicio") || nombreMinuscula.includes("gas") || nombreMinuscula.includes("agua") || nombreMinuscula.includes("electricidad") || nombreMinuscula.includes("público")) {
        icono = "lightning-charge";
    } else if (nombreMinuscula.includes("personal") || nombreMinuscula.includes("laboral") || nombreMinuscula.includes("honorario") || nombreMinuscula.includes("trabajadora")) {
        icono = "people";
    } else if (nombreMinuscula.includes("mantenimiento") || nombreMinuscula.includes("reparacion")) {
        icono = "tools";
    } else if (nombreMinuscula.includes("limpieza") || nombreMinuscula.includes("suministro")) {
        icono = "stars";
    } else if (nombreMinuscula.includes("administrativo") || nombreMinuscula.includes("financiero") || nombreMinuscula.includes("banco") || nombreMinuscula.includes("comision")) {
        icono = "calculator";
    }
    return icono;
}

document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Tipos de Gastos', description: 'Aquí defines las categorías principales y sus conceptos específicos para clasificar todos los egresos del condominio de manera clara.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_tipo_gasto"]', popover: { title: 'Nuevo Catálogo', description: 'Crea una partida principal junto con sus conceptos vinculados.', side: "bottom", align: 'start' } },
        { element: '#tabla_tipo_gasto', popover: { title: 'Catálogo General', description: 'Lista de todas las partidas principales. Utiliza el botón "Ver" para desglosar y consultar los conceptos de cada una.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nombre_tipo_gasto', popover: { title: 'Partida Principal', description: 'Escribe el nombre de la partida macro (Ej: "Servicios Públicos", "Gastos de Personal").', side: 'bottom', align: 'start' } },
        { element: '#btn_agregar_concepto', popover: { title: 'Añadir Conceptos', description: 'Agrega subcategorías específicas (Ej: "Electricidad CORPOELEC", "Agua HIDROLARA") pertenecientes a esta partida.', side: 'left', align: 'center' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la estructura completa de manera atómica en la base de datos.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_tipo_gasto',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});