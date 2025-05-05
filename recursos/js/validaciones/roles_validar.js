$(document).ready(function(){

	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[A-Za-z\b]{3,30}$/,
		$(this),$("#span_nombre"),"Solo letras entre 3 y 30 caracteres");
	});
	
	$("#envio").on("click",function(e){
		e.preventDefault();
		if(validarenvio()==true){
			Swal.fire({
				title: "¿Estás seguro?",
				text: "¿Está seguro que desea registrar este rol?",
				showCancelButton: true,
				confirmButtonText: "Registrar",
				confirmButtonColor: "#e01d22",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						$("#form_rol").submit();	
					}
			    });
		}	
	});
	$(".editar").on("click",function(e){
		e.preventDefault();
		
		Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea editar este rol?",
			showCancelButton: true,
			confirmButtonText: "Editar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
			if (resultado.isConfirmed) {
				$("#form_rol").submit();	
			}
			});
	});

	$(".eliminar").on("click",function(e){
		e.preventDefault();
		var link = $(this).attr('href');
		
		Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea eliminar este rol?",
			showCancelButton: true,
			confirmButtonText: "Eliminar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
			if (resultado.isConfirmed) {
				window.location.href=link;
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

function validarenvio(){
	if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#nombre"),$("#span_nombre"),'El formato debe ser en letras'
        )==0)
	{
		mensajes('error',4000,'Verifique el nombre del rol',
		'El formato debe ser en letras');
		
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
		etiquetamensaje.text("");
		return 1;
	}
	else{
		etiquetamensaje.text(mensaje);
		return 0;
	}
}