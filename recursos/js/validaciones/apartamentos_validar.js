$(document).ready(function(){

	$("#nro_apartamento").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#nro_apartamento").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{1,2}$/,
		$(this),this.nextElementSibling,"Debe ingresar el número del apartamento");
	});

	$("#porcentaje_participacion").on("keypress",function(e){
		validarKeyPress(/[0-9.]$/, e);
	});

	$("#porcentaje_participacion").on("keyup",function(){
		validarKeyUp(/^\d{1,2}(\.\d{1,2})?$/,
		$(this),this.nextElementSibling,"Debe ingresar el codigo del banco");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Apartamento?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						nro_apartamento_an = null;
					}
			    });
		}	
	});

	$("#nro_apartamento").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{1,2}$/,
        $("#nro_apartamento"),document.querySelector("#nro_apartamento").nextElementSibling,'El formato debe ser en números'
        )) {
        	if (this.value == nro_apartamento_an) {return;}
			let datos = new FormData();
			datos.append('validar','nro_apartamento');
			datos.append('nro_apartamento',$(this).val());
			verificar_duplicados(datos);
        }		
	})

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

async function validarEnvio(accion = "Registrar"){	
	if(validarKeyUp(
        /^[0-9]{1,2}$/,
        $("#nro_apartamento"),document.querySelector("#nro_apartamento").nextElementSibling,'Debe ingresar el número del apartamento'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el número del apartamento',
		'El formato debe ser sólo en números');
		
		return false;
	}
	else if(validarKeyUp(
        /^\d{1,2}(\.\d{1,2})?$/,
        $("#porcentaje_participacion"),document.querySelector("#porcentaje_participacion").nextElementSibling,'Debe ingresar el porcentaje de participación'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el porcentaje de participación',
		'El formato debe ser sólo en números');
		
		return false;
	}
	else if(validar_select("gas")==0)
	{
		mensajes('error',4000,'Debe indicar si tiene o no gas',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("agua")==0)
	{
		mensajes('error',4000,'Debe indicar si tiene o no agua',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("alquilado")==0)
	{
		mensajes('error',4000,'Debe indicar si es alquilado o no',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("propietario_id")==0)
	{
		mensajes('error',4000,'Debe seleccionar un propietario',
		'Debe seleccionar una opción');
		
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
		datos.append("id_apartamento",id_modificar);
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
	if(nro_apartamento_an != $("#nro_apartamento").val()){
		datos = new FormData(); 
		datos.append('validar','nro_apartamento');
		datos.append('nro_apartamento',$("#nro_apartamento").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Número de apartamento ya esta registrado','Este número de apartamento esta registrado, debe ingresar otro.');
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