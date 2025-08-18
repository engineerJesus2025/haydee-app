function asignarEventos(){
	$(".monto").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$(".monto").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),"Debe ingresar el monto del pago");
	});

	$(".monto_dolar").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$(".monto_dolar").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),"Debe ingresar el monto del dolar");
	});

	$(".tasa_dolar").on("keypress",function(e){
		validarKeyPress(/^\d*\.?\d*$/, e);
	});

	$(".tasa_dolar").on("keyup",function(){
		validarKeyUp(/^\d{1,6}(\.\d{1,2})?$/,
		$(this),"Debe ingresar la tasa del día de hoy");
	});

	$(".referencia").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$(".referencia").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{3,10}$/,
		$(this),"Debe ingresar la referencia del pago");
	});
}

$(document).ready(function(){
	// Validaciones
	asignarEventos();
	
	// Boton del formulario
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
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
						envio(accion);						
						referencia_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#agregar_detalle").on("click",function(e){
		asignarEventos();
	});

	$(".referencia").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{3,10}$/,
        $(".referencia"),document.querySelector(".referencia").nextElementSibling,'Solo deben ser números y tener entre 3 y 10 dígitos'
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
        /^[0-9,]{1,8}$/,
        $(".monto"),'Debe ingresar el monto del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del pago',
		'El formato del monto debe ser sólo en numeros');
		
		return false;
	}
	if(validarKeyUp(
        /^[0-9,]{1,8}$/,
        $(".monto_dolar"),'Debe ingresar el monto del dolar'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del dolar',
		'El formato del monto del dolar debe ser sólo en numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^\d{1,6}(\.\d{1,2})?$/,
        $(".tasa_dolar"),'Debe ingresar la tasa del dolar de hoy'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la tasa del dolar de hoy',
		'El formato de la tasa del dolar debe ser sólo en números');
		
		return false;
	}
	
	else if($(".referencia").is(":visible") && 
		validarKeyUp(
        /^[0-9]{3,10}$/,
        $(".referencia"),'Debe ingresar la referencia del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la referencia del pago',
		'El formato de la referencia debe ser sólo en números');
		
		return false;
	}
	else if(validar_select_multiple("tipo_pago_propietario")==0)
	{
		mensajes('error',4000,'Debe ingresar un tipo de pago',
		'Debe seleccionar una opción de tipo de pago');
		
		return false;
	}
	else if(validar_select_multiple("banco_propietario")==0)
	{
		mensajes('error',4000,'Debe ingresar un banco',
		'Debe seleccionar una opción de banco');
		
		return false;
	}
	else if(validar_select("estado")==0)
	{
		mensajes('error',4000,'Debe ingresar un estado de pago',
		'Debe seleccionar una opción de estado de pago');
		
		return false;
	}
	else if(validar_select("mensualidad_id")==0)
	{
		mensajes('error',4000,'Debe ingresar la mensualidad',
		'Debe seleccionar una opción de mensualidad');
		
		return false;
	}
	else if(validar_select("apartamento_id")==0)
	{
		mensajes('error',4000,'Debe ingresar el apartamento',
		'Debe seleccionar una opción de apartamento');
		
		return false;
	}
	else if(validarFecha($(".fecha_propietario"))==false)
	{
		mensajes('error',4000,'Fecha no válida',
		'La fecha debe ser posterior a 1900 y no puede ser futura');
		
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
		datos.append("id_pago",id_modificar);
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
	if(referencia_an != $(".referencia").val()){
		datos = new FormData(); 
		datos.append('validar','referencia');
		datos.append('referencia',$(".referencia").val());
		res = await verificar_duplicados(datos);
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

function validarKeyUp(er,etiqueta,mensaje){
	let etiquetamensaje;
	etiqueta.map(etiqueta_selec=>{			
		etiquetamensaje = etiqueta[etiqueta_selec].nextElementSibling;
		a = er.test(etiqueta[etiqueta_selec].value);

		if(a){
			etiquetamensaje.textContent = "";
				return 1;
		}
		else{
			etiquetamensaje.textContent = mensaje;
				return 0;
		}
	});
}

function validar_select(id) {
	let selec = document.querySelector("#"+id);
	if (selec.value == '') {
		return false;
	}else{
		return true;
	}
}

function validar_select_multiple(id) {
	let selec = document.querySelectorAll("."+id);
	let resultado = true;
	selec.forEach(etiqueta_selec=>{
		if (etiqueta_selec.checkVisibility()) {
			if (etiqueta_selec.value == '') {
				resultado = false;
			}
		}
	});
	return resultado;
}

function validarFecha(fecha_arreglo){
	let resultado = true;

	fecha_arreglo.map(fecha=>{
		if (!(fecha_arreglo[fecha].value)) {
			resultado = false;
		}

		let	fecha_validar = new Date(fecha_arreglo[fecha].value);
		console.log(fecha_validar);
		if (fecha_validar.getFullYear() < 1900 || fecha_validar.getFullYear() > new Date().getFullYear()) {
			mensajes('error',4000,'Fecha no válida','La fecha debe ser posterior a 1900 y no puede ser futura');
			resultado = false;
		}
		else if (fecha_validar.getFullYear() == new Date().getFullYear()){
			if (fecha_validar.getMonth() > new Date().getMonth()) {
				mensajes('error',4000,'Fecha no válida','El Mes seleccionado no puede ser futuro');
				resultado = false;
			}
			else if (fecha_validar.getMonth() == new Date().getMonth()) {
				if (fecha_validar.getDate()+1 > new Date().getDate()) {
					mensajes('error',4000,'Fecha no válida','El Dia seleccionado no puede ser futuro');
					resultado = false;
				}
			}
		}
		
		if (fecha_validar.getMonth() < 0 || fecha_validar.getMonth() > 11) {
			mensajes('error',4000,'Fecha no válida','El mes debe estar entre 0 y 11');
			resultado = false;
		}
		if (fecha_validar.getDate() < 1 || fecha_validar.getDate() > 31) {
			mensajes('error',4000,'Fecha no válida','El día debe estar entre 1 y 31');
			resultado = false;
		}
		if (fecha_validar.getDate() > 28 && fecha_validar.getMonth() == 1) {
			mensajes('error',4000,'Fecha no válida','Febrero solo tiene 28 días');
			resultado = false;
		}
		if ((fecha_validar.getDate() == 31) && (fecha_validar.getMonth() == 3 || fecha_validar.getMonth() == 5 || fecha_validar.getMonth() == 8 || fecha_validar.getMonth() == 10)) {
			mensajes('error',4000,'Fecha no válida','Los meses de abril, junio, septiembre y noviembre solo tienen 30 días');
			resultado = false;
		}
	});

	return resultado;
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
		document.querySelector(`.${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
		return true;
	}
	return false;
}