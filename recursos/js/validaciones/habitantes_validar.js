$(document).ready(function(){
	$("#cedula").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#cedula").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{7,8}$/,
		this,this.nextElementSibling,"Debe ingresar la cedula del Habitante");
	});

	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		this,this.nextElementSibling,"Debe ingresar el nombre del Habitante");
	});

	$("#apellido").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#apellido").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		this,this.nextElementSibling,"Debe ingresar el apellido del Habitante");
	});

	$("#fecha_nacimiento").on("keyup",function(){
        validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
        this,this.nextElementSibling,"Ingrese una fecha valida");
    });

	$("#telefono").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#telefono").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{11}$/,
		this,this.nextElementSibling,"Debe ingresar un telefono del Habitante");
	});

	$("#correo").on("keypress",function(e){	
		validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
	});

	$("#correo").on("keyup",function(e){
		validarKeyUp(/^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,this,
		this.nextElementSibling,"El formato debe ser asi: ejemplo@gmail.com");
	});
	
	$("#boton_formulario_habitantes").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio_habitantes(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} a este Habitante?`,
				showCancelButton: true,
				confirmButtonText: "Sí, "+accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio_habitantes(accion);						
						cedula_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	document.getElementById('sexo').addEventListener("change",e=>{
		e.target.classList.add('is-valid');
		e.target.classList.remove('is-invalid');
		e.target.nextElementSibling.textContent = "";
	});

	document.getElementById('apartamento_id').addEventListener("change",e=>{
		e.target.classList.add('is-valid');
		e.target.classList.remove('is-invalid');
		e.target.nextElementSibling.textContent = "";
	});

	document.getElementById('tipo_vinculo').addEventListener("change",e=>{
		e.target.classList.add('is-valid');
		e.target.classList.remove('is-invalid');
		e.target.nextElementSibling.textContent = "";
	});

	$("#cedula").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9\b]{7,8}$/,
        document.querySelector("#cedula"),document.querySelector("#cedula").nextElementSibling,'El formato debe ser en números'
        )) {
        	if (this.value == cedula_an) {return;}
			let datos = new FormData();
			datos.append('validar','cedula');
			datos.append('cedula',$(this).val());
			verificar_duplicados_habitantes(datos);
        }		
	})

	$("#tipo_vinculo").on("change",function(e){
		if (this.value == tipo_vinculo_an) {return;}
		let datos = new FormData();
		datos.append('validar','tipo_vinculo');
		datos.append('tipo_vinculo',$(this).val());
		datos.append('apartamento_id',$("#apartamento_id").val());
		verificar_duplicados_habitantes(datos);
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

async function validarEnvio_habitantes(accion = "Registrar"){	
	if(validarKeyUp(
        /^[0-9\b]{7,8}$/,
        document.querySelector("#cedula"),document.querySelector("#cedula").nextElementSibling,'Debe ingresar la cedula'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la cedula',
		'El formato debe ser sólo en números');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z \b]{3,30}$/,
        document.querySelector("#nombre"),document.querySelector("#nombre").nextElementSibling,'Solo letras, no mas de 30 caracteres'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre',
		'Solo letras, no mas de 30 caracteres');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[A-Za-z \b]{3,30}$/,
        document.querySelector("#apellido"),document.querySelector("#apellido").nextElementSibling,'Solo letras, no mas de 30 caracteres'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el apellido',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarFechaNacimiento(document.querySelector("#fecha_nacimiento")) == false)
	{

		return false;
	}
	else if(validarKeyUp(
        /^[0-9\b]{11}$/,
        document.querySelector("#telefono"),document.querySelector("#telefono").nextElementSibling,'Solo numeros'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un telefono',
		'El formato debe ser XXXX-XXXXXXX. Solo numeros');
		
		return false;
	}
	else if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        document.querySelector("#correo"),document.querySelector("#correo").nextElementSibling,'Ejemplo: alguien@servidor.com'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un correo electrónico',
		'Ejemplo: alguien@servidor.com');
		
		return false;
	}
	else if(validar_select("sexo")==0)
	{
		mensajes('error',4000,'Debe seleccionar un sexo',
		'Debe seleccionar una opción');
		
		return false;
	}
	else if(validar_select("apartamento_id")==0)
	{
		mensajes('error',4000,'Debe seleccionar un apartamento',
		'Debe seleccionar una opción');
		
		return false;
	}
	
	else if(validar_select("tipo_vinculo")==0)
	{
		mensajes('error',4000,'Debe seleccionar un vinculo',
		'Debe seleccionar una opción');
		
		return false;
	}
	
	if (accion == "Registrar") {
		
	}else if (accion == "Editar"){
		datos = new FormData();
		
		datos.append("id_habitane",id_modificar_habitantes);
	}
	
	// si el valor de correo no es el mismo de antes:
	if(cedula_an != $("#cedula").val()){
		datos = new FormData(); 
		datos.append('validar','cedula');
		datos.append('cedula',$("#cedula").val());
		res = await verificar_duplicados_habitantes(datos);
		// revisamos si esta duplicado con otra cedula
		if(res){
			mensajes('error',4000,'Esta cedula ya esta registrada','Esta cedula esta registrada, debe ingresar otra.');
			return false;
		}
	}
	
	if(tipo_vinculo_an != $("#tipo_vinculo").val()){
		datos = new FormData(); 
		datos.append('validar','tipo_vinculo');
		datos.append('tipo_vinculo',$("#tipo_vinculo").val());
		datos.append('apartamento_id',$("#apartamento_id").val());
		res = await verificar_duplicados_habitantes(datos);
		// revisamos si hay un propietario ya registrado
		if(res){
			mensajes('error',4000,'Ya existe un propietario registrado para este apartamento','Solo puede haber un propietario por apartamento.');
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

function validarFechaNacimiento(fecha_input) {
	let resultado = true;

	if (!fecha_input.value) {
		mensajes('error', 4000, 'Fecha obligatoria', 'Debes ingresar la fecha de nacimiento');
		return false;
	}

	const fechaNacimiento = new Date(fecha_input.value);
	const hoy = new Date();

	// Validación básica de rango aceptable
	if (fechaNacimiento.getFullYear() < 1900 || fechaNacimiento > hoy) {
		mensajes('error', 4000, 'Fecha no válida', 'La fecha debe ser posterior a 1900 y no puede ser futura');
		return false;
	}

	// Calcular la edad
	let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
	const mes = hoy.getMonth() - fechaNacimiento.getMonth();

	if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
		edad--;
	}

	if (edad < 18) {
		mensajes('error', 4000, 'Edad insuficiente', 'Debes ser mayor de 18 años');
		resultado = false;
	}

	return resultado;
}

async function verificar_duplicados_habitantes(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	});
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
		return true;
	}
	return false;
}