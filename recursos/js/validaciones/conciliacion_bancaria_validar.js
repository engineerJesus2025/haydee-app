$(document).ready(function(){

	$("#monto_movimiento").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$("#monto_movimiento").on("keyup",function(){
		validarKeyUp(/^[0-9,.]{1,15}$/,
		$(this),this.nextElementSibling,"Solo numeros permitidos");
	});

	$("#fecha_movimiento").on("keyup",function(){
		validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
		$(this),this.nextElementSibling,"Debe ingresar una fecha valida");
	});

	$("#referencia_movimiento").on("keypress",function(e){	
		validarKeyPress(/^[A-Za-z0-9\b]*$/, e);
	});

	$("#referencia_movimiento").on("keyup",function(e){
		validarKeyUp(/^[A-Za-z0-9\b]{1,20}$/,$(this),
		this.nextElementSibling,"El formato de la referencia es incorrecto");
	});

	$("#observaciones").on("keypress",function(e){	
		validarKeyPress(/^[a-zA-z \b]*$/, e);		
	});

	$("#observaciones").on("keyup",function(e){
		validarKeyUp(/^[a-zA-z \b]{0,100}$/,$(this),
		this.nextElementSibling,"Numeros y letras, Maximo 100 caracteres");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = e.target.getAttribute("op");		
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
						envio(accion);// mandamos la confirmacion al envio ajax.js	
						referencia_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});
	
	$("#boton_registrar_conciliacion").on("click",async function(e){		
		e.preventDefault();
		if(await validarConciliacion()==true){
			Swal.fire({
			title: "¿Estás seguro?",
			text: `¿Está seguro que desea Guardar Conciliacion Bancaria?`,
			showCancelButton: true,
			confirmButtonText: "Guardar",
			confirmButtonColor: "#1b8a40",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((result) => {
				if (result.isConfirmed) {
					guardarConciliacionBancaria()
				}
			});
		}	
	});

	// Referencia
	$("#referencia_movimiento").on("keyup",function(e){
		if (validarKeyUp(
        /^[A-Za-z\b]{3,20}$/,
        $("#referencia_movimiento"),document.querySelector("#referencia_movimiento").nextElementSibling,'El formato de la referencia es incorrecto'
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
       /^[0-9,.]{1,15}$/,
        $("#monto_movimiento"),document.querySelector("#monto_movimiento").nextElementSibling,'Solo numeros permitidos'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el Monto del movimiento',
		'El formato debe ser sólo en numeros, se permiten decimales');
		
		return false;
	}
	else if(validarKeyUp(
        /^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
        $("#fecha_movimiento"),document.querySelector("#fecha_movimiento").nextElementSibling,'Debe ingresar una fecha valida'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la fecha del movimiento',
		'Solo se permiten numeros en la fecha');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[A-Za-z0-9\b]{1,20}$/,
        $("#referencia_movimiento"),document.querySelector("#referencia_movimiento").nextElementSibling,'El formato de la referencia es incorrecto'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la referencia bancaria',
		'Se permiten numeros y letras');
		
		return false;
	}
	else if(validar_select("banco_movimiento")==0)
	{
		mensajes('error',4000,'Debe ingresar el Banco',
		'Debe seleccionar una opción');
		
		return false;
	}
	
	// si el valor de referencia no es el mismo de antes:
	if(referencia_an != $("#referencia_movimiento").val()){
		datos = new FormData(); 
		datos.append('validar','referencia');
		datos.append('referencia',$("#referencia_movimiento").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Referencia Bancaria ya registrado','Esta Referencia Bancaria esta registrada, debe ingresar otra.');
			return false;
		}
	}
	
	return true;
}

function validarConciliacion(){	
	if(validarKeyUp(
       /^[0-9a-zA-z /b]{0,100}$/,
        $("#observaciones"),document.querySelector("#observaciones").nextElementSibling,'Numeros y letras, Maximo 100 caracteres'
        )==0)
	{
		mensajes('error',4000,'Error en las Observaciones',
		'El formato debe ser sólo letras o números, Máximo 100 caracteres');
		
		return false;
	}
	// Que todos los campos estan revisados
	let marca = document.querySelector("[title='Marcar como sin Movimiento Bancario Asociado']");
	if (marca) {
		mensajes('error',8000,'Error en la Tabla de Registros',
		'Debe Revisar todos los registros del sistema y asignarle movimientos. En caso de no tener marcar como "No Correspondido"');
		
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

async function verificar_duplicados(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `Referencia ya registrada`
		return true;
	}
	return false;
}
