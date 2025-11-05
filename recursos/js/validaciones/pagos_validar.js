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

	formulario_usar.querySelectorAll("select").forEach(select=>{
		select.addEventListener("change",()=>{
			select.classList.add('is-valid');
			select.classList.remove('is-invalid');
			select.nextElementSibling.textContent = "";
		});
	});

	formulario_usar.querySelectorAll("[type='date']").forEach(input=>{
		input.addEventListener("change",()=>{
			input.classList.add('is-valid');
			input.classList.remove('is-invalid');
			input.nextElementSibling.textContent = "";
		});
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
			console.log(etiqueta_selec,etiqueta_selec.checkVisibility())
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