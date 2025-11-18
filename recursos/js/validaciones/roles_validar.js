$(document).ready(function(){
	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/,
		this,this.nextElementSibling,"Solo letras entre 3 y 30 caracteres");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este usuario?`,
				showCancelButton: true,
				confirmButtonText: "Si, " + accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						nombre_anterior = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});
	$("#nombre").on("keyup",function(e){
		if (validarKeyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/,
		this,this.nextElementSibling,"Solo letras entre 3 y 30 caracteres")) {
			if(this.value == nombre_anterior){return}
			let datos = new FormData();
			datos.append('validar','nombre');
			datos.append('nombre',$(this).val());
			verificar_duplicados(datos);			
        }
	})

	document.querySelectorAll("[name='permisos[]']").forEach(checkbox=>{
		checkbox.addEventListener("click",async e=>{
			if (!checkbox.checked){
				if (checkbox.getAttribute("error") == 1){
					checkbox.setAttribute('error',0);
					checkbox.classList.remove('is-invalid');
					if (checkbox.closest(".row").querySelector("[error='1']") == null){
						checkbox.closest(".row").lastElementChild.textContent = "";
					}
				}
				return;
			}

			let valido = validarKeyUp(/^[0-9]{1,11}$/,
			checkbox,checkbox.closest(".row").lastElementChild,"El valor de uno o mas permisos no es válido");

			if (!valido) {
				checkbox.setAttribute('error',1);
				return;
			}

			let datos = new FormData();
			datos.append('validar','validar_clave_foranea');
			datos.append('tabla','permisos_usuarios');
			datos.append('nombre_clave','id_permiso_usuario');
			datos.append('valor',checkbox.value);

			valido = await verificar_clave_foranea(datos);
			
			if (valido) {
				checkbox.classList.remove('is-invalid');
				if (checkbox.closest(".row").querySelector("[error='1']") == null){
					checkbox.closest(".row").lastElementChild.textContent = "";
				}
			}
			else{
				checkbox.setAttribute('error',1);
				// console.log(checkbox)
				checkbox.classList.add('is-invalid');
				checkbox.closest(".row").lastElementChild.textContent = "Se detectaron permisos inexistentes";
			}
		});
	});
});

function mensajes(icono,tiempo,titulo,mensaje){
	Swal.fire({
	icon:icono,
    timer:tiempo,	
    title:titulo,
	text:mensaje,
	showConfirmButton:true,
	confirmButtonText:'Aceptar',
	confirmButtonColor: "#e01d22",
	});
}

async function validarEnvio(){
	if(validarKeyUp(
        /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/,
        document.getElementById('nombre'),document.getElementById('nombre').nextElementSibling,'El formato debe ser en letras'
        )==0)
	{
		mensajes('error',4000,'Verifique el nombre del rol',
		'El formato debe ser en letras');
		
		return false;
	}

	if(nombre_anterior != $("#nombre").val()){
		let datos = new FormData(); 
		datos.append('validar','nombre');
		datos.append('nombre',$("#nombre").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Nombre ya registrado','Este nombre esta registrado, debe ingresar otro.');
			return false;
		}
	}
	error = false;

	for (let checkbox of document.querySelectorAll("[name='permisos[]']")){
		if (!(checkbox.checked)) {continue;}

		let valido = validarKeyUp(/^[0-9]{1,11}$/,
		checkbox,checkbox.closest(".row").lastElementChild,"El valor de uno o mas permisos no es válido");

		if (!valido) {
			error = true;
			mensajes('error',4000,'Atención','El valor de uno o mas permisos no es válido');
			break;
		}
	}

	if (error) {return false;}

	const checkbox = document.querySelectorAll("[name='permisos[]']");
	const checkboxActivados = Array.from(checkbox).filter(checkbox=>checkbox.checked).map(checkbox=>checkbox.value);

	let datos = new FormData();
	datos.append('validar','validar_permisos_usuarios');

	checkboxActivados.forEach(id => {
    	datos.append('valor[]', id); 
	});

	let valido = await verificar_clave_foranea(datos);
	
	if (!valido["estatus"]) {

		if (valido.ids_no_encontrados) {
			valido.ids_no_encontrados.map(id_errorneo=>{
				let checkbox = document.querySelector(`[value='${id_errorneo}']`)
				checkbox.classList.remove('is-valid');
				checkbox.classList.add('is-invalid');
				checkbox.closest(".row").lastElementChild.textContent = "Se detectaron permisos inexistentes";
			});
		}

		mensajes('error',4000,'Atención',valido.mensaje);

		return false;
	}

	return true;
}

function validarKeyPress(er, e) {
    key = e.keyCode;
    tecla = String.fromCharCode(key);
    a = er.test(tecla);
    if (!a) {
        e.preventDefault();
    }
}

function validarKeyUp(er,etiqueta,etiquetamensaje,
mensaje){
	a = er.test(etiqueta.value);
	
	if(a){
		if (etiqueta.type != "checkbox") {etiqueta.classList.add('is-valid');}
		etiqueta.classList.remove('is-invalid');
		if (etiqueta.closest(".row").querySelector("[error='1']") == null){
			etiquetamensaje.textContent = "";
		}
		return 1;
	}
	else{
		if (etiqueta.type != "checkbox") {etiqueta.classList.remove('is-valid');}
		etiqueta.classList.add('is-invalid');
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}

async function verificar_duplicados(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	
	if(data.estatus){	
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado`;
		document.querySelector(`#${data.busqueda}`).classList.add('is-invalid');
		document.querySelector(`#${data.busqueda}`).classList.remove('is-valid');
		return true;
	}
	return false;
}

async function verificar_clave_foranea(datos){	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;
	});

	return data		
}