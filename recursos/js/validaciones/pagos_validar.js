function asignarEventos(){
	$(".fecha_admin").on("keyup",function(){
		validarKeyUp(/^\d{4}-\d{2}-\d{2}$/,
			this,"Debe ingresar una fecha adecuada")
	});

	$(".monto").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$(".monto").on("keyup",function(){
		validarKeyUp(/^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
		this,"Debe ingresar el monto del pago");
	});

	$(".monto_dolar").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$(".monto_dolar").on("keyup",function(){
		validarKeyUp(/^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
		this,"Debe ingresar el monto del dolar");
	});

	$(".tasa_dolar").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$(".tasa_dolar").on("keyup",function(){
		validarKeyUp(/^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
		this,"Debe ingresar la tasa del día de hoy");
	});

	$(".imagen").on("change",function(){
		validarImagen(this, 5 * 1024 * 1024); // Llama a la función de validación con límite de 5MB
	});

	$(".observacion").on("keyup",function(){
		validarKeyUp(
			/^[a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ.,-]{3,60}$/,
			this,
			"Debe ingresar una descripción de 3 a 60 caracteres (letras, números, espacios y puntos/comas)"
		);
	});

	$(".referencia").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$(".referencia").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9\b]{3,10}$/,
        this,'Solo números entre 3 y 10 dígitos'
        )) {
        	if (this.value == referencia_an) {return;}
			let datos = new FormData();
			datos.append('validar','referencia');
			datos.append('referencia',$(this).val());
			verificar_duplicados(datos,this);
        }		
	})

	formulario_usar.querySelectorAll("[type='date']").forEach(input=>{
		input.addEventListener("change",()=>{
			input.classList.add('is-valid');
			input.classList.remove('is-invalid');
			input.nextElementSibling.textContent = "";
		});
	});

	// Validaciones de selects
	document.getElementById('mensualidad_id').addEventListener("change",async e=>{
		let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
		e.target,e.target.nextElementSibling,"El valor de la mensualidad no es válido");

		if (!valido) return;

		let datos = new FormData();
		datos.append('validar','validar_clave_foranea');
		datos.append('tabla','mensualidad');
		datos.append('nombre_clave','id_mensualidad');
		datos.append('valor',e.target.value);

		valido = await verificar_clave_foranea(datos);
		
		if (valido) {
			e.target.classList.add('is-valid');
			e.target.classList.remove('is-invalid');
			e.target.nextElementSibling.textContent = "";
		}
		else{
			e.target.classList.remove('is-valid');
			e.target.classList.add('is-invalid');
			e.target.nextElementSibling.textContent = "La mensualidad seleccionada no existe";
		}
	});

	document.getElementById('estado').addEventListener("change",async e=>{
		let valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
		e.target,e.target.nextElementSibling,"El valor del estado no es válido");

		if (!valido) return;
	});

	document.querySelector('.tipo_pago_admin').addEventListener("change",async e=>{
		let valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
		e.target,e.target.nextElementSibling,"El valor del método de pago no es válido");

		if (!valido) return;
	});

	document.querySelector('.banco_admin').addEventListener("change",async e=>{
		let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
		e.target,e.target.nextElementSibling,"El valor del banco no es válido");

		if (!valido) return;

		let datos = new FormData();
		datos.append('validar','validar_clave_foranea');
		datos.append('tabla','bancos');
		datos.append('nombre_clave','id_banco');
		datos.append('valor',e.target.value);

		valido = await verificar_clave_foranea(datos);
		
		if (valido) {
			e.target.classList.add('is-valid');
			e.target.classList.remove('is-invalid');
			e.target.nextElementSibling.textContent = "";
		}
		else{
			e.target.classList.remove('is-valid');
			e.target.classList.add('is-invalid');
			e.target.nextElementSibling.textContent = "El banco seleccionado no existe";
		}
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
				confirmButtonText: "Sí, "+accion,
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
	if(validar_select("apartamento_id")==0)
	{
		mensajes('error',4000,'Debe ingresar el apartamento',
		'Debe seleccionar una opción de apartamento');
		
		return false;
	}
	else if(validar_select("mensualidad_id")==0)
	{
		mensajes('error',4000,'Debe ingresar la mensualidad',
		'Debe seleccionar una opción de mensualidad');
		
		return false;
	}
	else if(validarFecha($(".fecha_admin"))==false)
	{
		mensajes('error',4000,'Fecha no válida',
		'La fecha debe ser posterior a 1900 y no puede ser futura');
		
		return false;
	}
	else if(validar_select_multiple("tipo_pago_admin")==0)
	{
		mensajes('error',4000,'Debe ingresar un metodo de pago',
		'Debe seleccionar una opción de metodo de pago');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
        $(".monto"),'Debe ingresar el monto del pago'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del pago',
		'El formato del monto debe ser sólo en numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
        $(".monto_dolar"),'Debe ingresar el monto del dolar'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el monto del dolar',
		'El formato del monto del dolar debe ser sólo en numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/,
        $(".tasa_dolar"),'Debe ingresar la tasa del dolar de hoy'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la tasa del dolar de hoy',
		'El formato de la tasa del dolar debe ser sólo en números');
		
		return false;
	}else if(validarKeyUp(
        /^[a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ.,-]{3,60}$/,
        $("#observacion"),'Debe ingresar una observación válida'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una observación',
		'La observación debe ser de 3 a 60 caracteres (letras, números y espacios)');
		
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
	else if(validar_select_multiple("banco_admin")==0)
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

	//Validar integridad de selects
	const apartamento = document.getElementById("apartamento_id"),
	mensualidad = document.getElementById("mensualidad_id"),
	estado = document.getElementById("estado"),
	metodo_pago_total = document.querySelectorAll(".tipo_pago_admin"),
	banco_total = document.querySelectorAll(".banco_admin");

	// Apartamento
	let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
	apartamento,apartamento.nextElementSibling,"El valor del apartamento no es válido");

	if (!valido) {
		mensajes('error',4000,'Atención','El valor del apartamento no es válido');
		return false;
	}

	let datos = new FormData();
	datos.append('validar','validar_clave_foranea');
	datos.append('tabla','apartamentos');
	datos.append('nombre_clave','id_apartamento');
	datos.append('valor',apartamento.value);

	valido = await verificar_clave_foranea(datos);
	
	if (valido) {
		apartamento.classList.add('is-valid');
		apartamento.classList.remove('is-invalid');
		apartamento.nextElementSibling.textContent = "";
	}
	else{
		apartamento.classList.remove('is-valid');
		apartamento.classList.add('is-invalid');
		apartamento.nextElementSibling.textContent = "El apartamento seleccionado no existe";

		mensajes('error',4000,'Atención','El apartamento seleccionado no existe');
		return false;
	}

	// Mensualidad
	valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
	mensualidad,mensualidad.nextElementSibling,"El valor de la mensualidad no es válido");

	if (!valido) {
		mensajes('error',4000,'Atención','El valor de la mensualidad no es válido');
		return false;
	}

	datos = new FormData();
	datos.append('validar','validar_clave_foranea');
	datos.append('tabla','mensualidad');
	datos.append('nombre_clave','id_mensualidad');
	datos.append('valor',mensualidad.value);

	valido = await verificar_clave_foranea(datos);
	
	if (valido) {
		mensualidad.classList.add('is-valid');
		mensualidad.classList.remove('is-invalid');
		mensualidad.nextElementSibling.textContent = "";
	}
	else{
		mensualidad.classList.remove('is-valid');
		mensualidad.classList.add('is-invalid');
		mensualidad.nextElementSibling.textContent = "La mensualidad seleccionada no existe";

		mensajes('error',4000,'Atención','La mensualidad seleccionada no existe');
		return false;
	}

	// Estado
	valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
	estado,estado.nextElementSibling,"El valor del estado no es válido");

	if (!valido) {
		mensajes('error',4000,'Atención','El valor del estado no es válido');
		return false;
	}

	// Metodo de pago
	let error = false;
	for (let metodo_pago of metodo_pago_total){
		valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
		metodo_pago,metodo_pago.nextElementSibling,"El valor del método de pago no es válido");

		if (!valido) {
			mensajes('error',4000,'Atención','El valor del método de pago no es válido');
			error = true;
			break;
		}
	}

	if (error) {return false;}

	// Banco
	error = false;

	for (let banco of banco_total){
		if (!(banco.checkVisibility())) continue;
		valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
		banco,banco.nextElementSibling,"El valor del banco no es válido");

		if (!valido) {
			mensajes('error',4000,'Atención','El valor del banco no es válido');
			error = true;
			break;
		}

		datos = new FormData();
		datos.append('validar','validar_clave_foranea');
		datos.append('tabla','bancos');
		datos.append('nombre_clave','id_banco');
		datos.append('valor',banco.value);

		valido = await verificar_clave_foranea(datos);
		
		if (valido) {
			banco.classList.add('is-valid');
			banco.classList.remove('is-invalid');
			banco.nextElementSibling.textContent = "";
		}
		else{
			banco.classList.remove('is-valid');
			banco.classList.add('is-invalid');
			banco.nextElementSibling.textContent = "El banco seleccionado no existe";

			mensajes('error',4000,'Atención','El banco seleccionado no existe');
			error = true;
		}
	}
	
	if (error) {return false;}

	// --- INICIO DE CÓDIGO A INSERTAR (AQUÍ DEBE IR) ---
	let imagen_total = document.querySelectorAll(".imagen");
	
	for (let imagen_input of imagen_total) {
		if (imagen_input.checkVisibility()) { 
			if (!validarImagen(imagen_input, 5 * 1024 * 1024)) {
				mensajes('error', 4000, 'Error en Imagen', 'Una de las imágenes seleccionadas no es válida (tamaño o tipo).');
				return false; 
			}
		}
	}
	// --- FIN DE CÓDIGO A INSERTAR ---
	
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
	if (etiqueta.length != undefined){
		let error = false;
		for (let etiqueta_selec of etiqueta){
			if (!(etiqueta_selec.checkVisibility())) continue;

			let etiquetamensaje = etiqueta_selec.nextElementSibling;
			a = er.test(etiqueta_selec.value);

			if(a){
				etiqueta_selec.classList.add('is-valid');
				etiqueta_selec.classList.remove('is-invalid');
				etiquetamensaje.textContent = "";
			}
			else{
				etiqueta_selec.classList.add('is-invalid')
				etiqueta_selec.classList.remove('is-valid');
				etiquetamensaje.textContent = mensaje;
				error = true;
				break;
			}
		}
		return !error;
	}
	else{
		if (!(etiqueta.checkVisibility())) return;

		let etiquetamensaje = etiqueta.nextElementSibling;
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
}

function validar_select(id) {
	let selec = document.querySelector("#"+id);
	if (selec.value == '') {
		selec.classList.add('is-invalid')
		selec.classList.remove('is-valid');
		selec.nextElementSibling.textContent = "Debe seleccionar una opcion";
		return false;
	}
	else{
		selec.classList.add('is-valid');
		selec.classList.remove('is-invalid');
		selec.nextElementSibling.textContent = "";
		return true;
	}
}

function validar_select_multiple(id) {
	let selects = document.querySelectorAll("."+id);
	let resultado = true;
	selects.forEach(etiqueta_selec=>{
		if (etiqueta_selec.checkVisibility()) {			
			if (etiqueta_selec.value == '') {
				etiqueta_selec.classList.add('is-invalid')
				etiqueta_selec.classList.remove('is-valid');
				etiqueta_selec.nextElementSibling.textContent = "Debe seleccionar una opcion";
				resultado = false;
			}
		}
	});
	return resultado;
}

// function validar_input_multiple(er,etiqueta,mensaje) {
// 	let selects = document.querySelectorAll("."+id);
// 	let resultado = true;
// 	selects.forEach(etiqueta_selec=>{
// 		if (etiqueta_selec.checkVisibility()) {
// 			console.log(etiqueta_selec,etiqueta_selec.checkVisibility())
// 			if (etiqueta_selec.value == '') {
// 				etiqueta_selec.classList.add('is-invalid')
// 				etiqueta_selec.classList.remove('is-valid');
// 				etiqueta_selec.nextElementSibling.textContent = "Debe seleccionar una opcion";
// 				resultado = false;
// 			}
// 		}
// 	});
// 	return resultado;

// 	if (etiqueta.length != undefined){
// 		let error = false;
// 		for (let etiqueta_selec of etiqueta){
// 			if (!(etiqueta_selec.checkVisibility())) continue;

// 			let etiquetamensaje = etiqueta_selec.nextElementSibling;
// 			a = er.test(etiqueta_selec.value);

// 			if(a){
// 				etiqueta_selec.classList.add('is-valid');
// 				etiqueta_selec.classList.remove('is-invalid');
// 				etiquetamensaje.textContent = "";
// 			}
// 			else{
// 				etiqueta_selec.classList.add('is-invalid')
// 				etiqueta_selec.classList.remove('is-valid');
// 				etiquetamensaje.textContent = mensaje;
// 				error = true;
// 				break;
// 			}
// 		}
// 		return !error;
// 	}
// 	else{
// 		let etiquetamensaje = etiqueta.nextElementSibling;
// 		a = er.test(etiqueta.value);

// 		if(a){
// 			etiqueta.classList.add('is-valid');
// 			etiqueta.classList.remove('is-invalid');
// 			etiquetamensaje.textContent = "";
// 			return 1;
// 		}
// 		else{
// 			etiqueta.classList.add('is-invalid')
// 			etiqueta.classList.remove('is-valid');
// 			etiquetamensaje.textContent = mensaje;
// 			return 0;
// 		}
// 	}
// }

function validarFecha(fecha_arreglo){
	let resultado = true;

	fecha_arreglo.map(fecha=>{
		if (!(fecha_arreglo[fecha].value)) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = "Debe seleccionar una opcion";
			resultado = false;
		}

		let	fecha_validar = new Date(fecha_arreglo[fecha].value);
		
		if (fecha_validar.getFullYear() < 1900 || fecha_validar.getFullYear() > new Date().getFullYear()) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = "Posterior a 1900 hasta la actualidad";

			mensajes('error',4000,'Fecha no válida','La fecha debe ser posterior a 1900 y no puede ser futura');
			resultado = false;
		}
		else if (fecha_validar.getFullYear() == new Date().getFullYear()){
			if (fecha_validar.getMonth() > new Date().getMonth()) {
				fecha_arreglo[fecha].classList.add('is-invalid')
				fecha_arreglo[fecha].classList.remove('is-valid');
				fecha_arreglo[fecha].nextElementSibling.textContent = "El mes no puede ser futuro";

				mensajes('error',4000,'Fecha no válida','El Mes seleccionado no puede ser futuro');
				resultado = false;
			}
			else if (fecha_validar.getMonth() == new Date().getMonth()) {
				if (fecha_validar.getDate()+1 > new Date().getDate()) {
					fecha_arreglo[fecha].classList.add('is-invalid')
					fecha_arreglo[fecha].classList.remove('is-valid');
					fecha_arreglo[fecha].nextElementSibling.textContent = "El dia no puede ser fututo";

					mensajes('error',4000,'Fecha no válida','El Dia seleccionado no puede ser futuro');
					resultado = false;
				}
			}
		}
		
		if (fecha_validar.getMonth() < 0 || fecha_validar.getMonth() > 11) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = 'El mes debe estar entre 0 y 11';

			mensajes('error',4000,'Fecha no válida','El mes debe estar entre 0 y 11');
			resultado = false;
		}
		if (fecha_validar.getDate() < 1 || fecha_validar.getDate() > 31) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = 'El día debe estar entre 1 y 31';

			mensajes('error',4000,'Fecha no válida','El día debe estar entre 1 y 31');
			resultado = false;
		}
		if (fecha_validar.getDate() > 28 && fecha_validar.getMonth() == 1) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = 'Febrero solo tiene 28 días';

			mensajes('error',4000,'Fecha no válida','Febrero solo tiene 28 días');
			resultado = false;
		}
		if ((fecha_validar.getDate() == 31) && (fecha_validar.getMonth() == 3 || fecha_validar.getMonth() == 5 || fecha_validar.getMonth() == 8 || fecha_validar.getMonth() == 10)) {
			fecha_arreglo[fecha].classList.add('is-invalid')
			fecha_arreglo[fecha].classList.remove('is-valid');
			fecha_arreglo[fecha].nextElementSibling.textContent = 'Los meses de abril, junio, septiembre y noviembre solo tienen 30 días';

			mensajes('error',4000,'Fecha no válida','Los meses de abril, junio, septiembre y noviembre solo tienen 30 días');
			resultado = false;
		}
	});

	return resultado;
}

async function verificar_duplicados(datos,etiqueta){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		etiqueta.nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
		return true;
	}
	return false;
}

function validarImagen(input, limiteBytes) {
    const file = input.files[0];
    const maxFileSize = limiteBytes; // 5 MB en bytes (ejemplo)
    const allowedTypes = ['image/jpeg', 'image/png'];

    if (!file) {
        // No hay archivo seleccionado, se considera válido si no es requerido
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
        // El span con el mensaje de error es el siguiente elemento, pero el input de file está en un input-group
        // Buscamos el siguiente span.invalid-feedback dentro del contenedor
        $(input).closest('.input-group').nextAll('.invalid-feedback').text("");
        return true;
    }

    // 1. Validar Tipo de Archivo (PNG o JPG)
    if (!allowedTypes.includes(file.type)) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        $(input).closest('.input-group').nextAll('.invalid-feedback').text("Solo se permiten archivos PNG y JPG.");
        return false;
    }

    // 2. Validar Tamaño del Archivo (Máx 5MB)
    if (file.size > maxFileSize) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        $(input).closest('.input-group').nextAll('.invalid-feedback').text("La imagen no debe superar los 5 MB.");
        return false;
    }

    // Si todo es correcto
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    $(input).closest('.input-group').nextAll('.invalid-feedback').text("");
    return true;
}

function validarKeyUpSelect(er,etiqueta,etiquetamensaje,
mensaje){
    a = er.test(etiqueta.value);
    
    if(a){
        etiqueta.classList.add('is-valid');
        etiqueta.classList.remove('is-invalid');

        if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
            etiqueta.nextElementSibling.classList.remove('border-danger');
            etiqueta.nextElementSibling.classList.remove('text-danger');

            etiqueta.nextElementSibling.classList.add('border-success');
            etiqueta.nextElementSibling.classList.add('text-success');
        }
        etiquetamensaje.textContent = "";
        return 1;
    }
    else{
        etiqueta.classList.add('is-invalid');
        etiqueta.classList.remove('is-valid');

        if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
            etiqueta.nextElementSibling.classList.remove('border-success');
            etiqueta.nextElementSibling.classList.remove('text-success');

            etiqueta.nextElementSibling.classList.add('border-danger');
            etiqueta.nextElementSibling.classList.add('text-danger');
        }
        etiquetamensaje.textContent = mensaje;
        return 0;
    }
}

async function verificar_clave_foranea(datos){	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;
	});

	return data		
}