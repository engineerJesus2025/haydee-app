// Variables Globales
let tabla_tipo_gasto;
let id_modificar;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

let boton_formulario = document.querySelector("#boton_formulario"); 
let modal = new bootstrap.Modal(document.getElementById("modal_tipo_gasto"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
let formulario_usar = document.querySelector(`#form_tipo_gasto`); 

document.addEventListener('DOMContentLoaded', () => {
    consultar();
});

// Resetear modal al cerrarlo
document.querySelector(`#modal_tipo_gasto`).addEventListener("hide.bs.modal", () => {
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	// boton_formulario.textContent = "Registrar";
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Tipo Gasto';
	document.getElementById('titulo_modal').textContent = "Registrar Tipo de Gasto";	
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-clipboard-plus");
	
	document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
});	

function envio(operacion) {	
	if (operacion === "modificar") {
		modificar(boton_formulario.getAttribute("id_modificar"));
	} else if(operacion === "Registrar"){
		registrar();
	} else {
		Alertas.mostrar('error', 'Atención', 'Ha ocurrido un error durante la operación');
	}
}


// ============================================
// CRUD
// ============================================

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_tipo_gasto; 
        
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

    // Formateador para el Tipo de Gasto con detección de palabras clave
    const formatoTipoGasto = (cell) => {
        let nombre = cell.getValue() || "";
        let nombreMinuscula = nombre.toLowerCase();
        
        const icono = obtenerIconoTipoGasto(nombre);

        return `<div class="d-flex align-items-center">
                    <i class="bi bi-${icono} text-primary me-3 opacity-75 fs-5"></i> 
                    <span class="fw-semibold">${nombre}</span>
                </div>`;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center" },
        
        { title: "Tipo de Gasto", field: "nombre_tipo_gasto", formatter: formatoTipoGasto, minWidth: 250, responsive: 0, widthGrow: 2 },
        // ---------------------------------------------

        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }
                
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

    tabla_tipo_gasto = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    Tablas.inicializarBuscadorGlobal(tabla_tipo_gasto, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    nombre = data.nombre_tipo_gasto;
    const icono = obtenerIconoTipoGasto(nombre);

    document.getElementById("vp_icono").className = `bi bi-${icono} me-2`;
    document.getElementById("vp_titulo").textContent = "Tipo de Gasto";
    document.getElementById("vp_etiqueta").textContent = "Nombre de la Categoría";
    document.getElementById("vp_valor").textContent = nombre || 'N/A';
    
    modalDetalles.show();
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

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
	
	let datos = new FormData();
	datos.append("id_tipo_gasto", id);
	datos.append('operacion', 'consultar_tipo_gasto');

	let respuesta = await Peticiones.enviar(datos, "", true);	
	Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos;
    	formulario_usar.querySelector("#nombre_tipo_gasto").value = data.nombre_tipo_gasto;

    	if(!permisoModificar){
    		boton_formulario.setAttribute("hidden", true);
    		boton_formulario.setAttribute("disabled", true);
    	}

    	boton_formulario.setAttribute("modificar", true);
    	boton_formulario.setAttribute("id_modificar", data.id_tipo_gasto);
    	// boton_formulario.textContent = "Guardar Cambios";
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

function eventoEliminar(e) {
    const id = e.target.value || e.target.parentElement.value;
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea eliminar este tipo de gasto?",
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        icon: "warning"
    }).then((resultado) => {
        if (resultado.isConfirmed) eliminar(id);				
    });
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

    nombreMinuscula = nombre.toLowerCase();

    // Asignación inteligente basada en palabras clave
    if (nombreMinuscula.includes("caja chica")) {
        icono = "box-seam";
    } else if (nombreMinuscula.includes("servicio") || nombreMinuscula.includes("gas") || nombreMinuscula.includes("agua") || nombreMinuscula.includes("electricidad")) {
        icono = "lightning-charge";
    } else if (nombreMinuscula.includes("personal") || nombreMinuscula.includes("laboral") || nombreMinuscula.includes("honorario")) {
        icono = "people";
    } else if (nombreMinuscula.includes("mantenimiento") || nombreMinuscula.includes("reparacion")) {
        icono = "tools";
    } else if (nombreMinuscula.includes("limpieza") || nombreMinuscula.includes("suministro")) {
        icono = "stars";
    } else if (nombreMinuscula.includes("administrativo") || nombreMinuscula.includes("financiero") || nombreMinuscula.includes("banco")) {
        icono = "calculator";
    }
    return icono;
}

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Tipos de Gastos', description: 'Aquí defines las categorías para clasificar los egresos (Ej: Servicios Públicos, Mantenimiento, Limpieza).', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_tipo_gasto"]', popover: { title: 'Nuevo Tipo', description: 'Crea una nueva categoría para organizar mejor los gastos del condominio.', side: "bottom", align: 'start' } },
        { element: '#tabla_tipo_gasto', popover: { title: 'Catálogo', description: 'Lista de categorías existentes. Puedes editar el nombre o eliminarlas si no se están usando.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nombre_tipo_gasto', popover: { title: 'Nombre de la Categoría', description: 'Escribe un nombre claro y descriptivo (Ej: "Jardinería", "Reparaciones Menores").', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la categoría en el sistema.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_tipo_gasto',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
