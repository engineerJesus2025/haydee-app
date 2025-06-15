$(document).ready(function(){

	$("#fecha_inicio").on("keyup",function(){
		validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
		$(this),this.nextElementSibling,"Debe ingresar una fecha adecuada")
	});

	$("#fecha_cierre").on("keyup",function(){
		validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
		$(this),this.nextElementSibling,"Debe ingresar una fecha adecuada");
	});

	$("#descripcion").on("keypress",function(e){	
		validarKeyPress(/^[A-Za-z0-9,. ñÑ\b]*$/, e);
	});

	$("#descripcion").on("keyup",function(e){
		validarKeyUp(/^[A-Za-z0-9,. ñÑ\b]{0,50}$/,$(this),
		this.nextElementSibling,"Solo texto, no mas de 50 caracteres");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este usuario?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						correo_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#fecha_inicio").on("change",function(){
		if(validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
		$(this),this.nextElementSibling,"Debe ingresar una fecha adecuada")){			
			nuevaFecha = new Date(this.value);
			nuevaFecha.setFullYear(nuevaFecha.getFullYear() + 1);
			$("#fecha_cierre")[0].valueAsDate = nuevaFecha;
		}
	});

});	//Fin de AJAX
let nuevaFecha;
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
	if(validarFecha("fecha_inicio")==0)
	{
		mensajes('error',4000,'Verifique la fecha de inicio Ingresada',
		'Debe ingresar una fecha adecuada');
		
		return false;
	}
	else if(validar_select("estado")==0)
	{
		mensajes('error',4000,'Verifique el estado Ingresado',
		'Debe seleccionar una opción');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[A-Za-z0-9,. ñÑ\b]{0,50}$/,
        $("#descripcion"),document.querySelector("#descripcion").nextElementSibling,'Err'
        )==0)
	{
		mensajes('error',4000,'Verifique la descripción Ingresada',
		'Solo texto, no mas de 50 caracteres');
		
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

function validarFecha(id) {
	let fecha = document.querySelector('#'+id);
	if (fecha.value != '') {
		return true;
	}
	return false;
}