$(document).ready(function(){

	$("#nombre_tipo_gasto").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre_tipo_gasto").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el nombre del tipo de gasto");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Tipo de Gasto?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
					}
			    });
		}	
	});

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
        $("#nombre_tipo_gasto"),document.querySelector("#nombre_tipo_gasto").nextElementSibling,'Debe ingresar el nombre del tipo de gasto'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del tipo de gasto',
		'El formato debe ser sólo en letras');
		
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
