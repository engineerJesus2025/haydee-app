// Variables Globales
let tabla_tipo_gasto;
let id_modificar;
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_modificar = document.querySelector("#permiso_modificar").value;

let boton_formulario = document.querySelector("#boton_formulario"); 
let modal = new bootstrap.Modal(document.getElementById("modal_tipo_gasto"), { focus: false });
let formulario_usar = document.querySelector(`#form_tipo_gasto`); 

document.addEventListener('DOMContentLoaded', () => {
    consultar();
});

// Resetear modal al cerrarlo
document.querySelector(`#modal_tipo_gasto`).addEventListener("hide.bs.modal", () => {
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";
	document.getElementById('titulo_modal').textContent = "Registrar Tipo de Gasto";	
	
	document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
});	

document.getElementById('header-toggle').addEventListener("click", () => {
	setTimeout(() => tabla_tipo_gasto.columns.adjust().draw(), 450);
});

function envio(operacion) {	
	if (operacion === "modificar") {
		modificar(boton_formulario.getAttribute("id_modificar"));
	} else if(operacion === "Registrar"){
		registrar();
	} else {
		Utilidades.mensaje('error', 'Atención', 'Ha ocurrido un error durante la operación');
	}
}

function crearBotones(id) {
	let html = `<div class="row justify-content-center gap-4">
					<button type="button" class="btn btn-success col-lg-2 col-sm-3 col-4 modificar" data-bs-toggle="modal" data-bs-target="#modal_tipo_gasto" title="modificar" value="${id}">
						<i class="bi bi-pencil-square"></i>
					</button>`;
	if (permiso_eliminar) {
		html += `<button type="button" class="btn btn-danger col-lg-2 col-sm-3 col-4 eliminar" title="Eliminar" value="${id}">
					<i class="bi bi-trash"></i>
				</button>`;
	}
	html += `</div>`;
	return html;
}

// ============================================
// CRUD
// ============================================

async function consultar() {
    const parametros = (data) => { data.operacion = 'consulta'; };
    
    const estructura_columnas = [
        { data: "nombre_tipo_gasto" },
        { 
            data: null, 
            render: (row) => crearBotones(row.id_tipo_gasto)
        }
    ];

    const configuracion_tabla = (row, data) => {
        row.id = `fila-${data.id_tipo_gasto}`;
        row.querySelector(".modificar")?.addEventListener('click', modificar_formulario);
        row.querySelector(".eliminar")?.addEventListener('click', eventoEliminar);
    };

    // Usamos el Helper
    tabla_tipo_gasto = Utilidades.crearDataTable('tabla_tipo_gasto', estructura_columnas, parametros, configuracion_tabla);
}

async function registrar() {
	let datos = new FormData(formulario_usar);
	datos.append('operacion', 'registrar'); 
	
	let respuesta = await Utilidades.query(datos, true);
	
	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

    modal.hide();
    tabla_tipo_gasto.ajax.reload(null, false); // Recarga la tabla de forma limpia
	Utilidades.mensaje('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

async function modificar_formulario(e) {
	let id = e.target.value || e.target.parentElement.value; 
	
	let datos = new FormData();
	datos.append("id_tipo_gasto", id);
	datos.append('operacion', 'consulta_especifica');

	let respuesta = await Utilidades.query(datos, true);	
	
    if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Error', respuesta.mensaje);
		return;
	}

    let data = respuesta.datos;
	formulario_usar.querySelector("#nombre_tipo_gasto").value = data.nombre_tipo_gasto;

	if(!permiso_modificar){
		boton_formulario.setAttribute("hidden", true);
		boton_formulario.setAttribute("disabled", true);
	}

	boton_formulario.setAttribute("modificar", true);
	boton_formulario.setAttribute("id_modificar", data.id_tipo_gasto);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Tipo de Gasto";
	id_modificar = id;
}

async function modificar(id) {	
	let datos = new FormData(formulario_usar);
	datos.append("id_tipo_gasto", id);
	datos.append('operacion', 'modificar');

	let respuesta = await Utilidades.query(datos, true);

 	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

    modal.hide();
    tabla_tipo_gasto.ajax.reload(null, false);
	Utilidades.mensaje('success', 'Éxito', 'El registro se ha modificado exitosamente');
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
	datos.append('operacion', 'eliminar');

	let respuesta = await Utilidades.query(datos);
	
	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

    tabla_tipo_gasto.ajax.reload(null, false);
	Utilidades.mensaje('success', 'Éxito', 'El registro ha sido eliminado correctamente');
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - TIPO DE GASTO
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
        { element: '.page-header', popover: { title: 'Tipos de Gastos', description: 'Aquí defines las categorías para clasificar los egresos (Ej: Servicios Públicos, Mantenimiento, Limpieza).', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_tipo_gasto"]', popover: { title: 'Nuevo Tipo', description: 'Crea una nueva categoría para organizar mejor los gastos del condominio.', side: "bottom", align: 'start' } },
        { element: '#tabla_tipo_gasto_wrapper', popover: { title: 'Catálogo', description: 'Lista de categorías existentes. Puedes editar el nombre o eliminarlas si no se están usando.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre_tipo_gasto', popover: { title: 'Nombre de la Categoría', description: 'Escribe un nombre claro y descriptivo (Ej: "Jardinería", "Reparaciones Menores").', side: 'bottom', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la categoría en el sistema.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_tipo_gasto');

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