$(document).ready(function(){
	// Validaciones
	$("#monto").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$("#monto").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),this.nextElementSibling,"Debe ingresar el monto del pago");
	});

	$("#tasa_dolar").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$("#tasa_dolar").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),this.nextElementSibling,"Debe ingresar la tasa del día de hoy");
	});

	$("#referencia").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#referencia").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{3,10}$/,
		$(this),this.nextElementSibling,"Debe ingresar la referencia del pago");
	});
	
	// Boton del formulario
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Pago?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						referencia_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#referencia").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{3,10}$/,
        $("#referencia"),document.querySelector("#referencia").nextElementSibling,'El formato debe ser en números'
        )) {
        	if (this.value == referencia_an) {return;}
			let datos = new FormData();
			datos.append('validar','referencia');
			datos.append('referencia',$(this).val());
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
        /^[0-9,]{1,8}$/,
        $("#monto"),document.querySelector("#monto").nextElementSibling,'Debe ingresar el monto del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del pago',
		'El formato debe ser sólo en numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^\d{1,6}(\.\d{1,2})?$/,
        $("#tasa_dolar"),document.querySelector("#tasa_dolar").nextElementSibling,'Debe ingresar la tasa del dolar de hoy'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la tasa del dolar de hoy',
		'El formato debe ser sólo en números');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^\d{1,6}(\.\d{1,2})?$/,
        $("#referencia"),document.querySelector("#referencia").nextElementSibling,'Debe ingresar la referencia del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la referencia del pago',
		'El formato debe ser sólo en números');
		
		return false;
	}
	if (accion == "Registrar") {
		/*if(validar_contra()==0)
		{
			mensajes('error',4000,'Verifique nuevamente la contraseña',
			'El campo "contraseña" y el campo "confirmar contraseña" no coinciden');
			
			return false;
		}*/
	}else if (accion == "Editar"){
		datos = new FormData();
		//datos.append("validar",'contra');
		datos.append("id_pago",id_modificar);
		/*datos.append("contra",$("#contra").val());
		res = await verificar_contra(datos);
		// revisamos si la contraseña que puso es la correcta
		if(!res){
			mensajes('error',4000,'Contraseña Icorrecta','La contraseña ingresada no es correcta, para poder realizar cambios debe ingresar la contraseña correcta');
			return false;
		}

		if ($("#confir_contra").val() != '') {
			if(validarKeyUp(/^[A-Za-z0-9_.+*$#%&@]{5,50}$/,$("#confir_contra"),document.querySelector("#confir_contra").nextElementSibling,'Debe ingresar una contraseña')==0)
			{
				mensajes('error',4000,'Error en la nueva contraseña',
				'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
				
				return false;
			}
		}*/
	}
	// si el valor de correo no es el mismo de antes:
	if(referencia_an != $("#referencia").val()){
		datos = new FormData(); 
		datos.append('validar','referencia');
		datos.append('referencia',$("#referencia").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Referencia ya registrada','Esta referencia esta registrada, debe ingresar otra.');
			return false;
		}
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
	a = er.test(etiqueta.val());

	if(a){

		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}

function validar_select(id) {
	let selec = document.querySelector("#"+id);
	if (selec.value == '') {
		return false;
	}else{
		return true;
	}
}

function validar_contra() {
	let contra = document.querySelector("#contra");
	let confir_contra = document.querySelector("#confir_contra");
	if (contra.value == confir_contra.value) {
		return 1;
	}else{
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
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
		return true;
	}
	return false;
}

async function verificar_contra(datos){
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// aqui revisamos el estatus, si es true es porque es correcta la contraseña		
	return data;
}