$(document).ready(function(){

	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Solo letras entre 3 y 30 caracteres");
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
						nombre_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});
	$("#nombre").on("keyup",function(e){
		if (validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Solo letras entre 3 y 30 caracteres")) {
			if(this.value == nombre_an){return}
			let datos = new FormData();
			datos.append('validar','nombre');
			datos.append('nombre',$(this).val());
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

async function validarEnvio(){
	if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#nombre"),$("#span_nombre"),'El formato debe ser en letras'
        )==0)
	{
		mensajes('error',4000,'Verifique el nombre del rol',
		'El formato debe ser en letras');
		
		return false;
	}

	if(nombre_an != $("#nombre").val()){
		datos = new FormData(); 
		datos.append('validar','nombre');
		datos.append('nombre',$("#nombre").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Nombre ya registrado','Este nombre esta registrado, debe ingresar otro.');
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

async function verificar_duplicados(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	
	if(data.estatus){		
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado`
		return true;
	}
	return false;
}