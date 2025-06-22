document.getElementById("boton_formulario_observacion").addEventListener("click",e=>{
	e.preventDefault();

	if(validarKeyUp(
       /^[0-9a-zA-Z ñÑ/b]{0,100}$/,
        document.getElementById("observacion_input"),document.getElementById("observacion_input").nextElementSibling,'Numeros y letras, Maximo 100 caracteres'
        )==0)
	{
		mensajes('error',4000,'Error en las Observaciones',
		'El formato debe ser sólo letras o números, Máximo 100 caracteres');
		
		return false;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea editar esta observacion?`,
		showCancelButton: true,
		confirmButtonText: "Cambiar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			id_caja = document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].id;
			editarObservacion(id_caja);// mandamos la confirmacion al envio ajax.js							
		}
	});
});

document.getElementById("observacion_input").addEventListener("keypress",e=>{
	validarKeyPress(/^[0-9a-zA-Z ñÑ]*$/, e);
});

document.getElementById("observacion_input").addEventListener("keyup",e=>{
	validarKeyUp(/^[0-9a-zA-Z ñÑ]{0,100}$/, document.getElementById("observacion_input"),document.getElementById("observacion_input").nextElementSibling,'Numeros y letras, Maximo 100 caracteres');
});

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

		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}
