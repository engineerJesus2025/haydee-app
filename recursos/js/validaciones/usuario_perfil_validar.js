$(document).ready(function(){
	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/,
		this,this.nextElementSibling,"Solo texto, no mas de 20 caracteres");
	});

	$("#apellido").on("keypress",function(e){
		validarKeyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, e);
	});

	$("#apellido").on("keyup",function(){
		validarKeyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/,
		this,this.nextElementSibling,"Solo texto, no mas de 20 caracteres");
	});

	$("#correo").on("keypress",function(e){	
		validarKeyPress(/^[A-Za-z0-9_+.@\b]*$/, e);
	});

	$("#correo").on("keyup",function(e){
		validarKeyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/,this,
		this.nextElementSibling,"El formato debe ser asi: ejemplo@gmail.com");
	});

	$("#contra").on("keyup",function(e){
		validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        this,this.nextElementSibling.nextElementSibling,'La contraseña debe tener mínimo 5 caracteres'
        )
	});

	$("#confir_contra").on("keyup",function(e){
		validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        this,this.nextElementSibling.nextElementSibling,'La contraseña debe tener mínimo 5 caracteres'
        )
	});
	$("#contra_actual").on("keyup",function(e){
		validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        this,this.nextElementSibling.nextElementSibling,'La contraseña debe tener mínimo 5 caracteres'
        )
	});
	
	$("#boton_guardar").on("click",async function(e){		
		e.preventDefault();
		if(await validarEnvio()==true){
			Swal.fire({
			title: "¿Estás seguro?",
			text: `¿Está seguro que desea editar estos datos?`,
			showCancelButton: true,
			confirmButtonText: "Si, Editar",
			confirmButtonColor: "#1b8a40",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((result) => {
				if (result.isConfirmed) {
					modificar();						
					correo_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
				}
			});
		}	
	});

	$("#boton_guardar_contra").on("click",async function(e){		
		e.preventDefault();
		if(await validarEnvioContra()==true){
			Swal.fire({
			title: "¿Estás seguro?",
			text: `¿Está seguro que desea cambiar su contraseña?`,
			showCancelButton: true,
			confirmButtonText: "Si, Cambiar",
			confirmButtonColor: "#1b8a40",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((result) => {
				if (result.isConfirmed) {
					modificarContra();
				}
			});
		}	
	});

	$("#correo").on("keyup",function(e){
		if (validarKeyUp(
        /^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/,
        document.querySelector("#correo"),document.querySelector("#correo").nextElementSibling,'El formato debe ser ejemplo@gmail.com'
        )) {
        	if (this.value == correo_an) {return;}
			let datos = new FormData();
			datos.append('validar','correo');
			datos.append('correo',$(this).val());			
			verificar_duplicados(datos);
        }		
	})
});	//Fin de AJAX

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

async function validarEnvio(accion = "Registrar"){	
	if(validarKeyUp(
        /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/,
        document.querySelector("#nombre"),document.querySelector("#nombre").nextElementSibling,'Debe ingresar el nombre del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del usuario',
		'El formato debe ser sólo en letras, no mas de 20 caracteres');
		
		return false;
	}
	else if(validarKeyUp(
        /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/,
        document.querySelector("#apellido"),document.querySelector("#apellido").nextElementSibling,'Debe ingresar el apellido del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el apellido del usuario',
		'El formato debe ser sólo en letras, no mas de 20 caracteres');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/,
        document.querySelector("#correo"),document.querySelector("#correo").nextElementSibling,'Ejemplo: alguien@servidor.com'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un correo electrónico',
		'El correo electornico ingresado no tiene un formato adecuado. Intente por Ejemplo: alguien@servidor.com');
		
		return false;
	}
	
	// si el valor de correo no es el mismo de antes:
	if(correo_an != $("#correo").val()){
		let datos = new FormData(); 
		datos.append('validar','correo');
		datos.append('correo',$("#correo").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Correo ya registrado','Este correo esta registrado, debe ingresar otro.');
			return false;
		}
	}
	
	return true;
}
async function validarEnvioContra(){
	if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        document.querySelector("#contra_actual"),document.querySelector("#contra_actual").nextElementSibling.nextElementSibling,'Debe ingresar una contraseña'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar su contraseña actual',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        document.querySelector("#contra"),document.querySelector("#contra").nextElementSibling.nextElementSibling,'Debe ingresar una contraseña'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una nueva contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(document.querySelector("#contra").value !== document.querySelector("#confir_contra").value){
		document.querySelector("#contra").classList.add('is-invalid')
		document.querySelector("#contra").classList.remove('is-valid');			
		document.querySelector("#contra").nextElementSibling.nextElementSibling.textContent = `Las contraseñas ingresadas aquí deben coincidir`;

		document.querySelector("#contra").nextElementSibling.classList.remove('border-success');
		document.querySelector("#contra").nextElementSibling.classList.remove('text-success');

		document.querySelector("#contra").nextElementSibling.classList.add('border-danger');
		document.querySelector("#contra").nextElementSibling.classList.add('text-danger');

		document.querySelector("#confir_contra").classList.add('is-invalid')
		document.querySelector("#confir_contra").classList.remove('is-valid');			
		document.querySelector("#confir_contra").nextElementSibling.nextElementSibling.textContent = `Las contraseñas ingresadas aquí deben coincidir`;

		document.querySelector("#confir_contra").nextElementSibling.classList.remove('border-success');
		document.querySelector("#confir_contra").nextElementSibling.classList.remove('text-success');

		document.querySelector("#confir_contra").nextElementSibling.classList.add('border-danger');
		document.querySelector("#confir_contra").nextElementSibling.classList.add('text-danger');

		mensajes('error',4000,'Atención','La contraseña nueva y confirmar contraseña deben coincidir');
		return false;
	}
	
	datos = new FormData();
	datos.append("validar",'contra_perfil');
	datos.append("contra",document.querySelector("#contra_actual").value);
	res = await verificar_contra(datos);
	// revisamos si la contraseña que puso es la correcta
	if(!res){
		document.querySelector("#contra_actual").classList.add('is-invalid')
		document.querySelector("#contra_actual").classList.remove('is-valid');
		document.querySelector("#contra_actual").nextElementSibling.classList.remove('border-success');
		document.querySelector("#contra_actual").nextElementSibling.classList.remove('text-success');

		document.querySelector("#contra_actual").nextElementSibling.classList.add('border-danger');
		document.querySelector("#contra_actual").nextElementSibling.classList.add('text-danger');
		
		document.querySelector("#contra_actual").nextElementSibling.nextElementSibling.textContent = `La contraseña ingresada no es correcta`;

		mensajes('error',4000,'Contraseña Icorrecta','La contraseña ingresada no es correcta, para poder realizar cambios debe ingresar la contraseña correcta');
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
		etiqueta.classList.add('is-valid');
		etiqueta.classList.remove('is-invalid');

		if (etiqueta.id == "contra" || etiqueta.id == "confir_contra" || etiqueta.id == "contra_actual") {
			etiqueta.nextElementSibling.classList.remove('border-danger');
			etiqueta.nextElementSibling.classList.remove('text-danger');

			etiqueta.nextElementSibling.classList.add('border-success');
			etiqueta.nextElementSibling.classList.add('text-success');
		}
		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiqueta.classList.add('is-invalid');
		etiqueta.classList.remove('is-valid');

		if (etiqueta.id == "contra" || etiqueta.id == "confir_contra" || etiqueta.id == "contra_actual") {
			etiqueta.nextElementSibling.classList.remove('border-success');
			etiqueta.nextElementSibling.classList.remove('text-success');

			etiqueta.nextElementSibling.classList.add('border-danger');
			etiqueta.nextElementSibling.classList.add('text-danger');
		}
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}

async function verificar_duplicados(datos){
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;
	})
	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`;
		document.querySelector(`#${data.busqueda}`).classList.add('is-invalid');
		document.querySelector(`#${data.busqueda}`).classList.remove('is-valid');
		return true;
	}
	return false;
}


async function verificar_contra(datos){
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{
		let result = res.json()
		return result;
	});
	return data;
}