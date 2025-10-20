$(document).ready(function(){
	$("#fecha").on("keyup",function(){
		validarKeyUp(/^\d{4}-\d{2}-\d{2}$/,
		this,this.nextElementSibling,"Debe ingresar una fecha adecuada");
	});

	$("#monto").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$("#monto").on("keyup",function(){
		let resultado = validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/, this,this.nextElementSibling.nextElementSibling,"Solo numeros, no mas de 15 caracteres y no mas de 2 decimales");
        if (resultado) {        	
			let input_convertir = document.getElementById("monto_cambio");
        	if (this.getAttribute("monto") == "bs") {
        		let fondo = document.getElementById("fondos_caja").textContent.split("Bs")[0],
				etiqueta_fondo_restantes = document.getElementById("fondos_restante");

				if (this.value <= 0 || this.value == '') {
					input_convertir.value = 0;
					etiqueta_fondo_restantes.textContent = document.getElementById("fondos_caja").textContent;
					return;
				}
				input_convertir.value = (parseFloat(this.value) / tasa_dolar).toFixed(2) || 0;
				
				if ((fondo - this.value) < 0){
					etiqueta_fondo_restantes.textContent = "Excedido";
				}
				else{
					etiqueta_fondo_restantes.textContent = (fondo - this.value + diferencia).toFixed(2) + "Bs. / " + ((fondo - this.value + diferencia) / tasa_dolar).toFixed(2) + "$";
				}
			}
			else{
				let fondo = document.getElementById("fondos_caja").textContent.split("Bs")[0],
				etiqueta_fondo_restantes = document.getElementById("fondos_restante");

				if (this.value <= 0 || this.value == '') {
					input_convertir.value = 0;
					etiqueta_fondo_restantes.textContent = document.getElementById("fondos_caja").textContent;
					return;
				}
				input_convertir.value = (parseFloat(this.value) * tasa_dolar).toFixed(2);

				if ((fondo - input_convertir.value) < 0){
					etiqueta_fondo_restantes.textContent = "Excedido";
				}
				else{
					etiqueta_fondo_restantes.textContent = (fondo - input_convertir.value + diferencia) + "Bs. / " + ((fondo - input_convertir.value + diferencia) / tasa_dolar).toFixed(2) + "$";
				}
			}
        }
	});

	$("#concepto").on("keypress",function(e){	
		validarKeyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, e);
	});

	$("#concepto").on("keyup",function(e){
		validarKeyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/,
		this,this.nextElementSibling,"Solo texto, no mas de 100 caracteres");
	});

	$("#monto_reponer").on("keypress",function(e){
		validarKeyPress(/^[0-9,.]*$/, e);
	});

	$("#monto_reponer").on("keyup",function(){
		let resultado = validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/, this,this.nextElementSibling.nextElementSibling,"Solo numeros, no mas de 15 caracteres y no mas de 2 decimales");
        if (resultado) {        	
			let input_convertir = document.getElementById("monto_cambio_reponer");
        	if (this.getAttribute("monto") == "bs") {
				if (this.value <= 0 || this.value == '') {
					input_convertir.value = 0;					
					return;
				}
				input_convertir.value = (parseFloat(this.value) / tasa_dolar).toFixed(2) || 0;
			}
			else{				

				if (this.value <= 0 || this.value == '') {
					input_convertir.value = 0;					
					return;
				}
				input_convertir.value = (parseFloat(this.value) * tasa_dolar).toFixed(2);			
			}
        }
	});
	
	$("#boton_gasto_caja").on("click",function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este gasto?`,
				showCancelButton: true,
				confirmButtonText: "Si, " + accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
					}
			    });
		}	
	});

	$("#boton_guardar_reposicion").on("click",function(e){
		e.preventDefault();		
		if(validarEnvioReponerCaja()==true){
			if (verificarReposicionExcedente()) {
				Swal.fire({
					title: "Advertencia",
					text: `El monto ingresado hará que se incremente el fondo fijo ¿Está seguro que desea continuar?`,
					showCancelButton: true,
					confirmButtonText: "Si, Continuar",
					confirmButtonColor: "#1b8a40",
					cancelButtonText: "Cancelar",
					icon: "warning"
				}).then((result) => {
					if (result.isConfirmed) {
						envio("reponer_caja");						
					}
				});
			}
			else{
				Swal.fire({
					title: "¿Estás seguro?",
					text: `¿Está seguro que desea reponer la caja chica?`,
					showCancelButton: true,
					confirmButtonText: "Si, Reponer",
					confirmButtonColor: "#1b8a40",
					cancelButtonText: "Cancelar",
					icon: "warning"
				}).then((result) => {
					if (result.isConfirmed) {
						envio("reponer_caja");						
					}
				});
			}			
		}	
	});
});

document.getElementById("boton_formulario_observacion").addEventListener("click",e=>{
	e.preventDefault();

	if(validarKeyUp(
       /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{0,100}$/,
        document.getElementById("descripcion_input"),document.getElementById("descripcion_input").nextElementSibling,'Numeros y letras, Maximo 100 caracteres'
        )==0)
	{
		mensajes('error',4000,'Error en la descripcion',
		'El formato debe ser sólo letras o números, Máximo 100 caracteres');
		
		return false;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea editar esta descripcion?`,
		showCancelButton: true,
		confirmButtonText: "Cambiar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			id_caja = document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].value;
			editarObservacion(id_caja);// mandamos la confirmacion al envio ajax.js							
		}
	});
});

document.getElementById("descripcion_input").addEventListener("keypress",e=>{
	validarKeyPress(/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/, e);
});

document.getElementById("descripcion_input").addEventListener("keyup",e=>{
	validarKeyUp(/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{0,100}$/, document.getElementById("descripcion_input"),document.getElementById("descripcion_input").nextElementSibling,'Numeros y letras, Maximo 100 caracteres');
});

function validarEnvio(){
	if(validarFecha(document.getElementById("fecha"))==0)
	{
		mensajes('error',4000,'Verifique la fecha Ingresada',
		'Debe ingresar una fecha adecuada');
		
		return false;
	}
	else if(document.querySelector("#monto").value == 0)
	{
		document.querySelector("#monto").classList.add('is-invalid');
		document.querySelector("#monto").classList.remove('is-valid');
		document.querySelector("#monto").nextElementSibling.nextElementSibling.textContent = "El monto esta vacío";
		mensajes('error',4000,'Atención',
		'El monto esta vacío');
		
		return false;
	}
	else if(validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/,document.querySelector("#monto"),document.querySelector("#monto").nextElementSibling.nextElementSibling,'Solo numeros, no mas de 15 caracteres y no mas de 2 decimales'
        )==0)
	{
		mensajes('error',4000,'Atención',
		'Solo se admiten numeros, no mas de 15 caracteres y no mas de 2 decimales');
		
		return false;
	}
	else if (verificarMontoExcedido()) {
		mensajes('error',4000,'Atención',
		'El monto colocado es superior a lo que está en la caja');

		return false;
	}
	else if(validarKeyUp(
        /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/,
        document.querySelector("#concepto"),document.querySelector("#concepto").nextElementSibling,'Solo texto, no mas de 100 caracteres'
        )==0)
	{
		mensajes('error',4000,'Atención',
		'El formato debe ser sólo en letras, no mas de 100 caracteres');
		
		return false;
	}
	return true;
}

function validarEnvioReponerCaja(){
	if(document.querySelector("#monto_reponer").value == 0)
	{
		document.querySelector("#monto_reponer").classList.add('is-invalid');
		document.querySelector("#monto_reponer").classList.remove('is-valid');
		document.querySelector("#monto_reponer").nextElementSibling.nextElementSibling.textContent = "El monto esta vacío";
		mensajes('error',4000,'Atención',
		'El monto esta vacío');
		
		return false;
	}
	else if(validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/,document.querySelector("#monto_reponer"),document.querySelector("#monto_reponer").nextElementSibling.nextElementSibling,'Solo numeros, no mas de 15 caracteres y no mas de 2 decimales'
        )==0)
	{
		mensajes('error',4000,'Atención',
		'Solo se admiten numeros, no mas de 15 caracteres y no mas de 2 decimales');
		
		return false;
	}
	else if (verificarReposicionInsuficiente()) {
		mensajes('error',4000,'Atención',
		'El monto colocado no cubre todos los gasto de caja');

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

function verificarMontoExcedido(){
	let input_monto = document.getElementById("monto");
	let fondo = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]);

	if (input_monto.getAttribute("monto") == "bs"){
		return (parseFloat(input_monto.value) > fondo);
	}
	else{
		let input_cambio = document.getElementById("monto_cambio");
		return (parseFloat(input_cambio.value) > fondo);
	}
}

function verificarReposicionInsuficiente(){
	let input_monto = document.getElementById("monto_reponer");
	let fondo_gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);

	if (input_monto.getAttribute("monto") == "bs"){
		return (parseFloat(input_monto.value) < fondo_gastado);
	}
	else{
		let input_cambio = document.getElementById("monto_cambio_reponer");
		return (parseFloat(input_cambio.value) < fondo_gastado);
	}
}

function verificarReposicionExcedente(){
	let input_monto = document.getElementById("monto_reponer");
	let fondo_gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);

	if (input_monto.getAttribute("monto") == "bs"){
		return (parseFloat(input_monto.value) > fondo_gastado);
	}
	else{
		let input_cambio = document.getElementById("monto_cambio_reponer");
		return (parseFloat(input_cambio.value) > fondo_gastado);
	}
}