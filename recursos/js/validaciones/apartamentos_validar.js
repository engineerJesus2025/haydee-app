$(document).ready(function(){
	$("#nro_apartamento").on("keypress",function(e){
		validarKeyPress(/^[0-9\-\b]*$/, e);
	});

	$("#nro_apartamento").on("keyup",function(){
		validarKeyUp(/^[0-9\-\b]{1,3}$/,
		this,this.nextElementSibling,"Debe ingresar el número del apartamento");
	});

	$("#porcentaje_participacion").on("keypress",function(e){
		validarKeyPress(/[0-9.]$/, e);
	});

	$("#porcentaje_participacion").on("keyup",function(){
		validarKeyUp(/^\d{1,2}(\.\d{1,2})?$/,
		this,this.nextElementSibling,"Debe ingresar porcentaje de participación de este apartamento");
	});

	document.getElementById('gas').addEventListener("change",e=>{
		let valido = validarKeyUp(/^[0-9]{1}$/,
        e.target,e.target.nextElementSibling,"El valor del gas ingresado no es válido");

        if (!valido) return;
	});

	document.getElementById('agua').addEventListener("change",e=>{
		let valido = validarKeyUp(/^[0-9]{1}$/,
        e.target,e.target.nextElementSibling,"El valor del agua ingresado no es válido");

        if (!valido) return;
	});

	document.getElementById('alquilado').addEventListener("change",e=>{
		let valido = validarKeyUp(/^[0-9]{1}$/,
        e.target,e.target.nextElementSibling,"El valor de 'alquilado' no es válido");

        if (!valido) return;
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Apartamento?`,
				showCancelButton: true,
				confirmButtonText: "Sí, " + accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						nro_apartamento_an = null;
					}
			    });
		}	
	});

	$("#nro_apartamento").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9\-]{1,3}$/,
        document.querySelector("#nro_apartamento"),document.querySelector("#nro_apartamento").nextElementSibling,'Debe ingresar el número del apartamento'
        )) {
        	if (this.value == nro_apartamento_an) {return;}
			let datos = new FormData();
			datos.append('validar','nro_apartamento');
			datos.append('nro_apartamento',$(this).val());
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

async function validarEnvio(accion = "Registrar"){	
	if(validarKeyUp(
        /^[0-9\-\b]{1,3}$/,
        document.querySelector("#nro_apartamento"),document.querySelector("#nro_apartamento").nextElementSibling,'Debe ingresar el número del apartamento'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el número del apartamento',
		'El formato debe ser sólo en números');
		
		return false;
	}
	else if(validarKeyUp(
        /^\d{1,2}(\.\d{1,2})?$/,
        document.querySelector("#porcentaje_participacion"),document.querySelector("#porcentaje_participacion").nextElementSibling,'Debe ingresar porcentaje de participación de este apartamento'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el porcentaje de participación',
		'El formato debe ser sólo en números');
		
		return false;
	}
	else if(validar_select("gas")==0)
	{
		mensajes('error',4000,'Debe indicar si tiene o no gas',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("agua")==0)
	{
		mensajes('error',4000,'Debe indicar si tiene o no agua',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("alquilado")==0)
	{
		mensajes('error',4000,'Debe indicar si es alquilado o no',
		'Debe seleccionar una opción');
		
		return false;
	}

	let valido = validarKeyUp(/^[0-9]{1}$/,
    document.getElementById('gas'),document.getElementById('gas').nextElementSibling,"El valor del gas no es válido");
    if (!valido) {
        mensajes("error", 2000, "Atención", "El valor del gas ingresado no es válido");
        return false;
    }
    valido = validarKeyUp(/^[0-9]{1}$/,
    document.getElementById('agua'),document.getElementById('agua').nextElementSibling,"El valor del agua no es válido");
    if (!valido) {
        mensajes("error", 2000, "Atención", "El valor del agua ingresado no es válido");
        return false;
    }
    valido = validarKeyUp(/^[0-9]{1}$/,
    document.getElementById('alquilado'),document.getElementById('alquilado').nextElementSibling,"El valor de 'alquilado' no es válido");
    if (!valido) {
        mensajes("error", 2000, "Atención", "El valor de ¿Es Alquilado? no es válido");
        return false;
    }

	// si el valor de correo no es el mismo de antes:
	if(nro_apartamento_an != $("#nro_apartamento").val()){
		datos = new FormData(); 
		datos.append('validar','nro_apartamento');
		datos.append('nro_apartamento',$("#nro_apartamento").val());
		res = await verificar_duplicados(datos);
		// revisamos si esta duplicado con otro usuario
		if(res){
			mensajes('error',4000,'Número de apartamento ya esta registrado','Este número de apartamento esta registrado, debe ingresar otro.');
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

async function verificar_duplicados(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).classList.add('is-invalid');
		document.querySelector(`#${data.busqueda}`).classList.remove('is-valid');
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`;
		return true;
	}
	return false;
}
