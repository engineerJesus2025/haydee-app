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
        // CAMBIAR AQUÍ EL ID SEGÚN EL MÓDULO (id_proveedor, id_modulo, id_permiso, id_tipo_gasto)
        const id = cell.getData().id_tipo_gasto; 
        
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) html += `<button type="button" class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>`;
        if (window.permiso_eliminar) html += `<button type="button" class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center" },
        
        // --- CAMBIAR ESTOS FIELDS SEGÚN EL MÓDULO ---
        { title: "Tipo de Gasto", field: "nombre_tipo_gasto", minWidth: 150, responsive: 0 },
        // ---------------------------------------------

        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 100, 
            responsive: 0, download: false,
            headerHozAlign: "center",
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
    tabla_tipo_gasto = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } }); // NOTA: modulos y permisos usan 'consultar', revisa el tuyo.

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_tipo_gasto.setFilter([filtros]);
        });
    }
}

async function registrar() {
	let datos = new FormData(formulario_usar);
	datos.append('operacion', 'registrar'); 
	
	let respuesta = await Peticiones.enviar(datos, "", true);
	
	if (!respuesta.estatus) {
		Alertas.mostrar('error', 'Atención', respuesta.mensaje);
		return;
	}

    modal.hide();
    tabla_tipo_gasto.replaceData(); // Recarga la tabla de forma limpia
	Alertas.mostrar('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
	
	let datos = new FormData();
	datos.append("id_tipo_gasto", id);
	datos.append('operacion', 'consulta_especifica');

	let respuesta = await Peticiones.enviar(datos, "", true);	
	
    if (!respuesta.estatus) {
		Alertas.mostrar('error', 'Error', respuesta.mensaje);
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

    modal.show();
}

async function modificar(id) {	
	let datos = new FormData(formulario_usar);
	datos.append("id_tipo_gasto", id);
	datos.append('operacion', 'modificar');

	let respuesta = await Peticiones.enviar(datos, "", true);

 	if (!respuesta.estatus) {
		Alertas.mostrar('error', 'Atención', respuesta.mensaje);
		return;
	}

    modal.hide();
    tabla_tipo_gasto.replaceData();
	Alertas.mostrar('success', 'Éxito', 'El registro se ha modificado exitosamente');
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

	let respuesta = await Peticiones.enviar(datos);
	
	if (!respuesta.estatus) {
		Alertas.mostrar('error', 'Atención', respuesta.mensaje);
		return;
	}

    tabla_tipo_gasto.replaceData();
	Alertas.mostrar('success', 'Éxito', 'El registro ha sido eliminado correctamente');
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