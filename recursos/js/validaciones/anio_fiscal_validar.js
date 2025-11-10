$(document).ready(function(){
	$("#fecha_inicio").on("keyup",function(){
		validarKeyUp(/^\d{4}-\d{2}-\d{2}$/,
		this,this.nextElementSibling,"Debe ingresar una fecha adecuada")
	});

	$("#fecha_cierre").on("keyup",function(){
		validarKeyUp(/^\d{4}-\d{2}-\d{2}$/,
		this,this.nextElementSibling,"Debe ingresar una fecha adecuada");
	});

	$("#descripcion").on("keypress",function(e){	
		validarKeyPress(/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]*$/, e);
	});

	$("#descripcion").on("keyup",function(e){
		validarKeyUp(/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/,this,
		this.nextElementSibling,"Solo texto, no mas de 50 caracteres");
	});

	document.getElementById('estado').addEventListener("change",e=>{
		let valido = validarKeyUp(/^[a-zA-z]{3,15}$/,
		e.target,e.target.nextElementSibling,"El valor del estado no es válido");

		if (!valido) return;
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este usuario?`,
				showCancelButton: true,
				confirmButtonText: "Si, " + accion,
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
		this,this.nextElementSibling,"Debe ingresar una fecha adecuada")){			
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
	let fecha_inicio = document.querySelector("#fecha_inicio");
	let fecha_cierre = document.querySelector("#fecha_cierre");

	if(validarFecha(fecha_inicio)==0)
	{
		mensajes('error',4000,'Verifique la fecha de inicio Ingresada',
		'Debe ingresar una fecha adecuada');
		
		return false;
	}
	else if(validarFecha(fecha_cierre)==0)
	{
		mensajes('error',4000,'Verifique la fecha de cierre Ingresada',
		'Debe ingresar una fecha adecuada');
		
		return false;
	}
	else if(validarLogicaFechas(fecha_inicio,fecha_cierre)==0)
	{
		mensajes('error',4000,'Verifique la fecha de cierre Ingresada',
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
        /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/,
        document.querySelector("#descripcion"),document.querySelector("#descripcion").nextElementSibling,'Err'
        )==0)
	{
		mensajes('error',4000,'Verifique la descripción Ingresada',
		'Solo texto, no mas de 50 caracteres');
		
		return false;
	}

	let valido = validarKeyUp(/^[a-zA-z]{3,15}$/,
	document.getElementById('estado'),document.getElementById('estado').nextElementSibling,"El valor del estado no es válido");

	if (!valido) {
		mensajes('error',4000,'Atención','El valor del estado ingresado no es válido');
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

function validarFecha(fecha){
	if (fecha.value == '') {
		fecha.classList.add('is-invalid')
		fecha.classList.remove('is-valid');
		fecha.nextElementSibling.textContent = "Debe seleccionar una opcion";
		return false;		
	}

	let expresion = /^(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/;

	if (!expresion.test(fecha.value)) {
		fecha.classList.add('is-invalid')
		fecha.classList.remove('is-valid');
		fecha.nextElementSibling.textContent = "Formato de la fecha incorrecto";
		return false;
	}

	const [anio, mes, dia] = fecha.value.split('-').map(Number);
	const fecha_validar = new Date(anio,mes-1,dia);
	// console.log(fecha_validar,fecha.value);

	if (fecha_validar.getFullYear() !== anio || fecha_validar.getMonth() !== mes - 1 || fecha_validar.getDate() !== dia) {
        fecha.classList.add('is-invalid')
		fecha.classList.remove('is-valid');
		fecha.nextElementSibling.textContent = "La fecha es inválida (ejemplo: 31 de Febrero)";
		return false;
    }

	if (fecha_validar.getFullYear() < 2000) {
		fecha.classList.add('is-invalid')
		fecha.classList.remove('is-valid');
		fecha.nextElementSibling.textContent = "La fecha debe ser posterior al 2000";
		return false;
	}

	fecha.classList.add('is-valid');
	fecha.classList.remove('is-invalid');
	fecha.nextElementSibling.textContent = "";
	return true;
}

function validarLogicaFechas(inicio,cierre){
	let [anio_inicio, mes_inicio, dia_inicio] = inicio.value.split('-').map(Number);
	const fecha_inicio = new Date(anio_inicio,mes_inicio-1,dia_inicio);

	let [anio_cierre, mes_cierre, dia_cierre] = cierre.value.split('-').map(Number);
	const fecha_cierre = new Date(anio_cierre,mes_cierre-1,dia_cierre);

	if (fecha_inicio >= fecha_cierre) {
		inicio.classList.add('is-invalid')
		inicio.classList.remove('is-valid');
		inicio.nextElementSibling.textContent = "La fecha de inicio debe ser anterior que la de cierre";
		cierre.classList.add('is-invalid')
		cierre.classList.remove('is-valid');
		cierre.nextElementSibling.textContent = "La fecha de cierre debe ser posterior que la de inicio";
		return false;
	}

	const dia = 1000 * 60 * 60 * 24;
	const diferencia = Math.floor((fecha_cierre - fecha_inicio) / dia);

	if (diferencia < 364 || diferencia > 366) {
		inicio.classList.add('is-invalid')
		inicio.classList.remove('is-valid');
		inicio.nextElementSibling.textContent = "El periodo debe ser de 1 año (364-366 dias)";
		cierre.classList.add('is-invalid')
		cierre.classList.remove('is-valid');
		cierre.nextElementSibling.textContent = "El periodo debe ser de 1 año (364-366 dias)";
		return false;
	}

	inicio.classList.add('is-valid');
	inicio.classList.remove('is-invalid');
	inicio.nextElementSibling.textContent = "";
	cierre.classList.add('is-valid');
	cierre.classList.remove('is-invalid');
	cierre.nextElementSibling.textContent = "";
	return true;
}