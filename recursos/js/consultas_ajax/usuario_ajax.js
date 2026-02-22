/**
 * Script AJAX para la gestión de Usuarios
 * Dependencias: utilidades.js (Objeto Utilidades)
 */

let id_modificar, correo_an;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let boton_formulario = document.querySelector("#boton_formulario"); 
let modal = new bootstrap.Modal("#modal_usuario");
let formulario_usar = document.querySelector(`#form_usuario`); 
let tabla_usuarios;

// Inicializamos la tabla
consultar();

// Resetear modal al cerrarlo
document.querySelector(`#modal_usuario`).addEventListener("hide.bs.modal", () => {
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Usuario";		
	
	formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Confirmar Contraseña";
	formulario_usar.querySelector("#confir_contra").placeholder = "Confirmar Contraseña";
	formulario_usar.querySelector("#contra").placeholder = "Contraseña";

	correo_an = null;

	document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
	document.querySelectorAll('input').forEach(input => {
		if (input.id.includes('contra')) {
			input.nextElementSibling.classList.remove('border-danger', 'text-danger', 'border-success', 'text-success');
			input.nextElementSibling.firstElementChild.classList.replace('bi-eye-slash', 'bi-eye');
		}
	});
});

// Mostrar/Ocultar contraseñas
document.querySelectorAll('.contra').forEach(boton => {
	boton.addEventListener('click', e => {
		e.preventDefault();
		let i = (e.target.firstElementChild == null) ? e.target : e.target.firstElementChild;

		if (i.classList.contains('bi-eye')){
			i.parentElement.previousElementSibling.setAttribute('type','text');
			i.classList.replace('bi-eye','bi-eye-slash');
		} else {
			i.parentElement.previousElementSibling.setAttribute('type','password');
			i.classList.replace('bi-eye-slash','bi-eye');
		}
	});
});

document.getElementById('header-toggle').addEventListener("click", e => {
	setTimeout(() => tabla_usuarios.columns.adjust().draw(), 450);
});

function envio(operacion) {	
	if (operacion === "Editar") {
		modificar(boton_formulario.getAttribute("id_modificar"));
	} else if(operacion === "Registrar"){
		registrar();
	} else {
		Utilidades.mensaje('error', 'Atención', 'Ha ocurrido un error durante la operación, inténtelo nuevamente');
	}
}

function crearBotones(id) {
	let div = document.createElement("div");
	let html = `<div class="row justify-content-evenly">
					<button type="button" class="btn btn-success btn-sm col-lg-3 col-4 editar" data-bs-toggle="modal" data-bs-target="#modal_usuario" title="Editar" value="${id}">
						<i class="bi bi-pencil-square"></i>
					</button>`;
	if (permiso_eliminar) {
		html += `<button type="button" class="btn btn-danger btn-sm col-lg-3 col-4 eliminar" title="Eliminar" value="${id}">
					<i class="bi bi-trash"></i>
				</button>`;
	}
	html += `</div>`;
	div.innerHTML = html;
	return div;
}

function definirColorBadge(nombre_rol){
	const colores = {
		'Administrador Global': "badge bg-warning text-dark",
		'Administrador': "badge bg-primary",
		'Propietario': "badge bg-success",
		'Contador': "badge bg-danger",
		'Presidente': "badge bg-info text-dark"
	};
	return colores[nombre_rol] || "badge bg-secondary";
}

async function consultar() {
	const paramentros_consulta = (data) => { data.operacion = 'consulta'; };
	const estructura_tabla_usuarios = [
 		{ data: "nombre" },
		{ data: "apellido" },
        { data: "correo" },
		{ 
            data: null,
            render: (row) => `<span class="${definirColorBadge(row.nombre_rol)}">${row.nombre_rol}</span>`
        },      
        { 
            data: null, 
            render: (row) => crearBotones(row.id_usuario).innerHTML
        } 		
 	];

 	const configuraciones_tabla_usuarios = (row, data) => {
 		Array.from(row.children).forEach(td => td.classList.add('align-middle'));
		row.id = `fila-${data.id_usuario}`; 		
 		row.querySelector(".editar")?.addEventListener('click', preparar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click', eventoEliminar);
 	}

 	// tabla_usuarios = crearDataTable('tabla_usuario', estructura_tabla_usuarios, paramentros_consulta, configuraciones_tabla_usuarios);
 	tabla_usuarios = Utilidades.crearDataTable('tabla_usuario', estructura_tabla_usuarios, paramentros_consulta, configuraciones_tabla_usuarios);
}

async function registrar() {
	let datos = new FormData(formulario_usar); // Recoge todos los inputs automáticamente
	datos.append('operacion', 'registrar');
	
	let respuesta = await Utilidades.query(datos);

	modal.hide();
	formulario_usar.reset();

	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

	tabla_usuarios.ajax.reload(null, false);
	Utilidades.mensaje('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

async function preparar_formulario(e) {
	let datos = new FormData();
	let id = e.target.value || e.target.parentElement.value; 
	
	datos.append("id_usuario", id);
	datos.append('operacion', 'consulta_especifica');

	let respuesta = await Utilidades.query(datos);	
	
	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Error', respuesta.mensaje);
		return;
	}

	let data = respuesta.datos; // <-- Extraemos la data del sobre

	formulario_usar.querySelector("#nombre").value = data.nombre;
	formulario_usar.querySelector("#apellido").value = data.apellido;	
	formulario_usar.querySelector("#correo").value = data.correo;
	formulario_usar.querySelector("#rol").value = data.rol_id;	

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden", true);
		boton_formulario.setAttribute("disabled", true);
	}
	
	boton_formulario.setAttribute("modificar", true);
	boton_formulario.setAttribute("id_modificar", data.id_usuario);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Usuario";
	
	formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Nueva Contraseña" ;
	formulario_usar.querySelector("#confir_contra").placeholder = "Escriba su Nueva Contraseña" ;
	formulario_usar.querySelector("#contra").placeholder = "Escriba su Contraseña";

	id_modificar = id;
	correo_an = data.correo;
}

async function modificar(id) {	
	let datos = new FormData(formulario_usar);
	
	// Si confir_contra tiene algo, usamos ese, si no usamos contra. 
	// Es mejor enviarlo explícitamente como lo tenías.
	let nueva_contra = formulario_usar.querySelector("#confir_contra").value || formulario_usar.querySelector("#contra").value;
	let rol_nombre = formulario_usar.querySelector("#rol").selectedOptions[0].textContent;

	datos.append("id_usuario", id);
	datos.append("contra", nueva_contra);
	datos.append("rol_nombre", rol_nombre);
	datos.append('operacion', 'editar_usuario');

	let respuesta = await Utilidades.query(datos);

	formulario_usar.reset();
 	modal.hide();

	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

	if (respuesta.actual){
		let nombre = formulario_usar.querySelector("#nombre").value;
		document.getElementById('boton_accion_usuario').textContent = `Hola, ${nombre} (${rol_nombre})`;
	}

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Usuario";

	tabla_usuarios.ajax.reload(null, false);
	Utilidades.mensaje('success', 'Éxito', 'El registro se ha modificado exitosamente');
}

function eventoEliminar(e){
	let id = e.target.value || e.target.parentElement.value;

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este Usuario?",
		showCancelButton: true,
		confirmButtonText: "Sí, Eliminar",
		confirmButtonColor: "#e01d22",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((resultado) => {
		if (resultado.isConfirmed) eliminar(id);				
	});
}

async function eliminar(id) {
	let datos = new FormData();
	datos.append("id_usuario", id);
	datos.append('operacion', 'eliminar');

	let respuesta = await Utilidades.query(datos);
	
	if (!respuesta.estatus) {
		Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
		return;
	}

	tabla_usuarios.ajax.reload(null, false);
	Utilidades.mensaje('success', 'Éxito', 'El registro ha sido eliminado correctamente');
}