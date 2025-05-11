$(document).ready(function(){

	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el nombre del usuario");
	});

	$("#apellido").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#apellido").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el apellido del usuario");
	});

	$("#correo").on("keypress",function(e){	
		validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
	});

	$("#correo").on("keyup",function(e){
		validarKeyUp(/^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,$(this),
		this.nextElementSibling,"El formato debe ser ejemplo@gmail.com");
	});

	$("#contra").on("keyup",function(e){
		validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        $(this),this.nextElementSibling,'La contraseña debe tener mínimo 5 caracteres'
        )
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

	$("#correo").on("keyup",function(e){
		if (validarKeyUp(
        /^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        $("#correo"),document.querySelector("#correo").nextElementSibling,''
        )) {
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

async function validarEnvio(){	
	if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#nombre"),document.querySelector("#nombre").nextElementSibling,'Debe ingresar el nombre del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del usuario',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#apellido"),document.querySelector("#apellido").nextElementSibling,'Debe ingresar el apellido del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el apellido del usuario',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        $("#correo"),document.querySelector("#correo").nextElementSibling,'Ejemplo: alguien@servidor.com'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un correo electrónico',
		'Ejemplo: alguien@servidor.com');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        $("#contra"),document.querySelector("#contra").nextElementSibling,'Debe ingresar una contraseña'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(validar_contra()==0)
	{
		mensajes('error',4000,'Verifique nuevamente la contraseña',
		'El campo "contraseña" y el campo "confirmar contraseña" no coinciden');
		
		return false;
	}
	else if(validar_select("rol")==0)
	{
		mensajes('error',4000,'Debe ingresar el rol',
		'Debe seleccionar una opción');
		
		return false;
	}	
	// si el valor de correo no es el mismo de antes:
	if(correo_an != $("#correo").val()){
		datos = new FormData(); 
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
// AJAX
async function verificar_duplicados(datos,accion = "registrar"){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// console.log(data)
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrada`
		return true;
	}
	return false;
}