$(document).ready(function(){

	$("#nombre_tipo_gasto").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z áéíúóñÑ\b]*$/, e);
	});

	$("#nombre_tipo_gasto").on("keyup",function(){
		validarKeyUp(/^[A-Za-z áéíúóñÑ\b]{3,50}$/,
		this,this.nextElementSibling,"Debe ingresar el nombre del tipo de gasto");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Tipo de Gasto?`,
				showCancelButton: true,
				confirmButtonText: "Si, " + accion,
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
        /^[A-Za-z áéíúóñÑ]{3,50}$/,
        document.querySelector("#nombre_tipo_gasto"),document.querySelector("#nombre_tipo_gasto").nextElementSibling,'Debe ingresar el nombre del tipo de gasto'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del tipo de gasto',
		'El formato debe ser sólo en letras');
		
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
		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiqueta.classList.add('is-invalid')
		etiqueta.classList.remove('is-valid');
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}