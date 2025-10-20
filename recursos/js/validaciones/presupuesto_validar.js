window.addEventListener('DOMContentLoaded',()=>{
	$("#cuota_reserva").on("keypress", function (e) {
        validarKeyPress(/^[0-9,.]*$/, e);
    });	
    $("#cuota_reserva").on("keyup", function (e) {
        let resultado = validarKeyUp(/^[0-9]{1,12}[,.]{0,1}[0-9]{0,2}$/, this,this.nextElementSibling,"Solo numeros, no mas de 15 caracteres y no mas de 2 decimales");
        if (resultado) {        	
			let input_convertir = this.closest(".row").querySelector("[convertido]");
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

	$("#observacion").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z0-9ñ., \b]*$/, e);
    });	
    $("#observacion").on("keyup", function (e) {
        validarKeyUp(/^[A-Za-z0-9ñ., \b]{0,50}$/, this,this.nextElementSibling,"Letras y numeros, no mas de 50 caracteres");
    });

	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
			Swal.fire({
			title: "¿Estás seguro?",
			text: `¿Está seguro que desea ${accion} este presupuesto?`,
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

async function validarEnvio(accion = "Registrar"){	
	if(validar_select("fecha")==0)
	{
		mensajes('error',4000,'Debe ingresar el mes',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/, document.getElementById('cuota_reserva'),document.getElementById('cuota_reserva').nextElementSibling,"Letras y numeros, no mas de 50 caracteres")==0)
	{
		mensajes('error',4000,'Debe ingresar la cuota de reserva',
		'Debe seleccionar un monoto válido');
		
		return false;
	}
	else if(!validarInputsDetalles()){
		return false;
	}
	else if(validarKeyUp(/^[A-Za-z0-9ñ., \b]{0,50}$/, document.querySelector("#observacion"),document.querySelector("#observacion").nextElementSibling,"Letras y numeros, no mas de 50 caracteres")==0)
	{
		mensajes('error',4000,'Atención',
		'Debe ingresar una descripcion valida');
		
		return false;
	}

	return true;
}

function validarInputsDetalles() {
	let inputs_texto = form_presupuesto.querySelectorAll("[type='text']");
	let inputs_montos = form_presupuesto.querySelectorAll("[type='number']");
	let total_montos = 0;
	let error = {
		estatus: false,
		lugar: '',
		input: ''
	};
	inputs_texto.forEach(input=>{
		if (input.id == "observacion") {return;}		
		if (input.value == '') {	
			error.lugar = input.closest(".accordion-item").querySelector("button").textContent			
			error.estatus = true;
			error.input = 'nombre';
		}
	});
	inputs_montos.forEach(input=>{
		if (input.id == "cuota_reserva" && input.value == '') {
			error.lugar = "Cuota de reserva";
			error.estatus = true;
			error.input = 'monto';
		}
		else if (input.value == '') {
			error.lugar = input.closest(".accordion-item").querySelector("button").textContent			
			error.estatus = true;
			error.input = 'monto';
		}else{
			total_montos += parseFloat(input.value);
		}
	});

	if (total_montos == 0) {
		mensajes('error',4000,'Atención',
			`Debe asignar algun monto al presupuesto. No puede ser 0`);
		return false;
	}

	if (error.estatus) {
		mensajes('error',4000,'Atención',
			`Falta asignar un ${error.input} en el area de ${error.lugar}`);
		return false;
	}

	return true;
}

function validarExpresionesDetalles() {
	let error = {
		estatus: false,
		lugar: '',
		mensaje: ''
	};
	form_presupuesto.querySelectorAll("[type='text']").forEach(input=>{
		if (input.id == "observacion") {return;}
		if (!validarKeyUp(/^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]{4,50}$/,input,input.nextElementSibling,'Solo letras, no mas de 50 caracteres')){
			error.lugar = input.closest(".accordion-item").querySelector("button").textContent			
			error.estatus = true;
			error.mensaje = 'Hay un error de escritura en uno de los nombres del area';
		}
	});
	form_presupuesto.querySelectorAll("[type='number']").forEach(input=>{
		if (!validarKeyUp(/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/,input,input.nextElementSibling,'Solo letras, no mas de 15 caracteres')){
			error.lugar = input.closest(".accordion-item").querySelector("button").textContent			
			error.estatus = true;
			error.mensaje = 'Hay un error de escritura en uno de los montos del area';
		}
	});

	if (error.estatus) {
		mensajes('error',4000,'Atención',
			`${error.mensaje}: ${error.lugar}`);
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
		selec.classList.add('is-invalid');
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