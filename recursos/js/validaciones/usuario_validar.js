/**
 * Script de validaciones para Usuarios
 * Dependencias: validaciones.js (Objeto Validaciones), utilidades.js (Objeto Utilidades)
 */

$(document).ready(function(){
	// ============================================
	// EVENTOS DE TIEMPO REAL (Filtros visuales)
	// ============================================
	$("#nombre, #apellido").on("keypress", function(e) {
		Validaciones.keyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, e);
	});

	$("#nombre, #apellido").on("keyup", function() {
		Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, this, this.nextElementSibling, "Solo texto, no más de 20 caracteres");
	});

	$("#correo").on("keypress", function(e) {	
		Validaciones.keyPress(/^[A-Za-z0-9_+.@\b]*$/, e);
	});

	$("#correo").on("keyup", function() {
		Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, this, this.nextElementSibling, "El formato debe ser: ejemplo@gmail.com");
	});

	$("#contra, #confir_contra").on("keyup", function() {
		Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, this, this.nextElementSibling.nextElementSibling, 'La contraseña debe tener mínimo 5 caracteres');
	});

	// Validación de Rol (Clave foránea) en tiempo real
	$("#rol").on("change", async function(e) {
		let valido = Validaciones.keyUp(/^[0-9]{1,11}$/, this, this.nextElementSibling, "El valor del rol no es válido");
		if (!valido) return;

		let datos = new FormData();
		datos.append('validar', 'validar_clave_foranea');
		datos.append('tabla', 'roles');
		datos.append('nombre_clave', 'id_rol');
		datos.append('valor', this.value);

		let res = await Utilidades.query(datos);
		
		if (res.estatus) {
			this.classList.replace('is-invalid', 'is-valid');
			this.nextElementSibling.textContent = "";
		} else {
			this.classList.replace('is-valid', 'is-invalid');
			this.nextElementSibling.textContent = "El rol seleccionado no existe";
		}
    });

	// Validar duplicidad de correo en tiempo real
	$("#correo").on("blur", async function() {
		if ($(this).val() === correo_an) return; // Si es el mismo de la BD, no validar
		
		if (Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, this, this.nextElementSibling, '')) {
			let datos = new FormData();
			datos.append('validar', 'correo');
			datos.append('correo', $(this).val());
			await Validaciones.verificarDuplicado(datos,'Este correo ya está en uso, ingrese uno diferente.');
		}		
	});
	
	// ============================================
	// ENVÍO DE FORMULARIO
	// ============================================
	$("#boton_formulario").on("click", async function(e) {
		e.preventDefault();
		let accion = (this.getAttribute("modificar")) ? "Editar" : "Registrar";		
		
		if(await validarEnvio(accion) === true){
			Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este usuario?`,
				showCancelButton: true,
				confirmButtonText: "Sí, " + accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			}).then((result) => {
				if (result.isConfirmed) {
					envio(accion);						
					correo_an = null;
				}
			});
		}	
	});
});

// ============================================
// FUNCIONES DE VALIDACIÓN GENERALES
// ============================================
async function validarEnvio(accion = "Registrar"){	
	
	const nombreValido = Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, document.querySelector("#nombre"), document.querySelector("#nombre").nextElementSibling, 'Solo texto, no más de 20 caracteres');
	if(!nombreValido) { Utilidades.mensaje('error', 'Error', 'El formato del nombre es incorrecto.'); return false; }

	const apellidoValido = Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, document.querySelector("#apellido"), document.querySelector("#apellido").nextElementSibling, 'Solo texto, no más de 20 caracteres');
	if(!apellidoValido) { Utilidades.mensaje('error', 'Error', 'El formato del apellido es incorrecto.'); return false; }
	
	const correoValido = Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, document.querySelector("#correo"), document.querySelector("#correo").nextElementSibling, 'Ejemplo: alguien@servidor.com');
	if(!correoValido) { Utilidades.mensaje('error', 'Error', 'Debe ingresar un correo electrónico válido.'); return false; }

	if(!Validaciones.select("rol")) { Utilidades.mensaje('error', 'Error', 'Debe seleccionar un rol.'); return false; }

	if (accion === "Registrar") {
		const contraValida = Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, document.querySelector("#contra"), document.querySelector("#contra").nextElementSibling.nextElementSibling, 'Mínimo 5 caracteres');
		if(!contraValida) { Utilidades.mensaje('error', 'Error', 'Debe ingresar una contraseña válida.'); return false; }

		if(document.querySelector("#contra").value !== document.querySelector("#confir_contra").value) {
			Utilidades.mensaje('error', 'Error', 'Las contraseñas no coinciden.');
			return false;
		}
	} else if (accion === "Editar") {
		// Validar contraseña actual si se está editando
		let datos = new FormData();
		datos.append("validar", 'contra');
		datos.append("id_usuario", id_modificar);
		datos.append("contra", $("#contra").val());
		
		let contraCorrecta = await Utilidades.query(datos);
		
		if(!contraCorrecta){
			let inputContra = document.querySelector("#contra");
			inputContra.classList.replace('is-valid', 'is-invalid');
			inputContra.nextElementSibling.nextElementSibling.textContent = `La contraseña actual ingresada es incorrecta`;
			inputContra.nextElementSibling.classList.replace('border-success', 'border-danger');
			inputContra.nextElementSibling.classList.replace('text-success', 'text-danger');
			
			Utilidades.mensaje('error', 'Contraseña Incorrecta', 'Para realizar cambios debe ingresar su contraseña actual correctamente.');
			return false;
		}

		// Validar nueva contraseña solo si escribió algo
		if ($("#confir_contra").val() !== '') {
			const nuevaValida = Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, document.querySelector("#confir_contra"), document.querySelector("#confir_contra").nextElementSibling.nextElementSibling, 'Mínimo 5 caracteres');
			if(!nuevaValida) {
				Utilidades.mensaje('error', 'Error en la nueva contraseña', 'Formato inválido en la nueva contraseña.');
				return false;
			}
		}
	}
	
	// Verificar correo duplicado si fue modificado
	if(correo_an !== $("#correo").val()){
		let datos = new FormData(); 
		datos.append('validar', 'correo');
		datos.append('correo', $("#correo").val());
		let duplicado = await Validaciones.verificarDuplicado(datos,'Este correo ya está en uso, ingrese uno diferente.');
		if(duplicado) return false;
	}

	// Validación final del rol contra la BD
	let rol = document.getElementById('rol');	
	let datosRol = new FormData();
	datosRol.append('validar', 'validar_clave_foranea');
	datosRol.append('tabla', 'roles');
	datosRol.append('nombre_clave', 'id_rol');
	datosRol.append('valor', rol.value);

	let rolExiste = await Utilidades.query(datosRol);
	if (!rolExiste.estatus) {
		rol.classList.replace('is-valid', 'is-invalid');
		rol.nextElementSibling.textContent = "El rol seleccionado no existe";
		Utilidades.mensaje('error', 'Atención', 'El rol seleccionado no existe en la BD.');
		return false;
	}

	return true;
}
