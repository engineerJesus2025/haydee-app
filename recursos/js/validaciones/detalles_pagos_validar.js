$(document).ready(function(){
	// Validaciones
	$("#monto_detalles").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$("#monto_detalles").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),this.nextElementSibling,"Debe ingresar el monto del pago");
	});

	$("#monto_dolar").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$("#monto_dolar").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),this.nextElementSibling,"Debe ingresar la tasa del día de hoy");
	});

	$("#referencia_detalles").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#referencia_detalles").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{3,10}$/,
		$(this),this.nextElementSibling,"Debe ingresar la referencia del pago");
	});
	
	// Boton del formulario
	$("#boton_formulario_detalles").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio_detalles(accion)==true){
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
						envio_detalles(accion);						
						referencia_an_detalles = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#referencia_detalles").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{3,10}$/,
        $("#referencia_detalles"),document.querySelector("#referencia_detalles").nextElementSibling,'El formato debe ser en números'
        )) {
        	if (this.value == referencia_an_detalles) {return;}
			let datos = new FormData();
			datos.append('validar','referencia');
			datos.append('referencia',$(this).val());
			verificar_duplicados_detalles(datos);
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

async function validarEnvio_detalles(accion = "Registrar"){	
	if(validarKeyUp(
        /^\d{1,6}(\.\d{1,2})?$/,
        $("#monto_detalles"),document.querySelector("#monto_detalles").nextElementSibling,'Debe ingresar el monto del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del pago',
		'El formato debe ser sólo en numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^\d{1,6}(\.\d{1,2})?$/,
        $("#monto_dolar_detalles"),document.querySelector("#monto_dolar_detalles").nextElementSibling,'Debe ingresar la tasa del dolar de hoy'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la tasa del dolar de hoy',
		'El formato debe ser sólo en números');
		
		return false;
	}
	
	else if($("#referencia_detalles").is(":visible") && 
		validarKeyUp(
        /^[0-9]{3,10}$/,
        $("#referencia_detalles"),document.querySelector("#referencia_detalles").nextElementSibling,'Debe ingresar la referencia del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la referencia del pago',
		'El formato debe ser sólo en números');
		
		return false;
	}	
	// si el valor de correo no es el mismo de antes:
	if(referencia_an_detalles != $("#referencia_detalles").val()){
		datos = new FormData(); 
		datos.append('validar','referencia');
		datos.append('referencia',$("#referencia_detalles").val());
		res = await verificar_duplicados_detalles(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Referencia ya registrada','Esta referencia esta registrada, debe ingresar otra.');
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

async function verificar_duplicados_detalles(datos){
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