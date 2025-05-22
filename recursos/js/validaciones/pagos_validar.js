$(document).ready(function(){

	$("#nombre_banco").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre_banco").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el nombre del banco");
	});

	$("#codigo").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#codigo").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{4}$/,
		$(this),this.nextElementSibling,"Debe ingresar el codigo del banco");
	});

	$("#numero_cuenta").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#numero_cuenta").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{18,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el número de cuenta");
	});

    $("#telefono_afiliado").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#telefono_afiliado").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{11}$/,
		$(this),this.nextElementSibling,"Debe ingresar un telefono afiliado");
	});

    $("#cedula_afiliada").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#cedula_afiliada").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{7,8}$/,
		$(this),this.nextElementSibling,"Debe ingresar una cedula afiliada");
	});
	
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
						numero_cuenta_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#referencia").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{18,30}$/,
        $("#referencia"),document.querySelector("#referencia").nextElementSibling,'El formato debe ser en números'
        )) {
        	if (this.value == numero_cuenta_an) {return;}
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
        /^[A-Za-z ]{3,30}$/,
        $("#nombre_banco"),document.querySelector("#nombre_banco").nextElementSibling,'Debe ingresar el nombre del banco'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del banco',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{4}$/,
        $("#codigo"),document.querySelector("#codigo").nextElementSibling,'Debe ingresar el codigo del banco'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el codigo del banco',
		'El formato debe ser sólo en números');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[0-9]{18,30}$/,
        $("#numero_cuenta"),document.querySelector("#numero_cuenta").nextElementSibling,'Ejemplo: XXXX-XXXXX-XXX...'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un número de cuenta',
		'Ejemplo: XXXX-XXXXX-XXX...');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{11}$/,
        $("#telefono_afiliado"),document.querySelector("#telefono_afiliado").nextElementSibling,'Debe ingresar un telefono afiliado'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un telefono afiliado',
		'El formato debe ser XXXX-XXXXXXX');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{7,8}$/,
        $("#cedula_afiliada"),document.querySelector("#cedula_afiliada").nextElementSibling,'Debe ingresar una cedula afiliada'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una cedula afiliada',
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
		datos.append("id_banco",id_modificar);
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
	if(numero_cuenta_an != $("#referencia").val()){
		datos = new FormData(); 
		datos.append('validar','referencia');
		datos.append('referencia',$("#referencia").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Número de cuenta ya registrado','Este número de cuenta esta registrado, debe ingresar otro.');
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