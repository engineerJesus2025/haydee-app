$(document).ready(function(){

	$("#nombre_banco").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre_banco").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,20}$/,
		this,this.nextElementSibling,"Solo letras, no mas de 20 caracteres");
	});

	$("#codigo").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#codigo").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{4}$/,
		this,this.nextElementSibling,"Debe ingresar el codigo del banco, cuatro digitos");
	});

	$("#numero_cuenta").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#numero_cuenta").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{18,30}$/,
		this,this.nextElementSibling,"Debe ingresar el número de cuenta, no mas de 30 caracteres");
	});

    $("#telefono_afiliado").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#telefono_afiliado").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{11}$/,
		this,this.nextElementSibling,"Solo numeros, ejemplo xxxx-xxxxxxx");
	});

    $("#cedula_afiliada").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#cedula_afiliada").on("keyup",function(){
		validarKeyUp(/^[0-9\b]{7,8}$/,
		this,this.nextElementSibling,"Solo numeros, no mas de 8 caracteres");
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarEnvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este Banco?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);						
						numero_cuenta_an = null;//resetea el valor del correo original (esto es de usuario_ajax.js)
					}
			    });
		}	
	});

	$("#numero_cuenta").on("keyup",function(e){
		if (validarKeyUp(
        /^[0-9]{18,30}$/,
        document.querySelector("#numero_cuenta"),document.querySelector("#numero_cuenta").nextElementSibling,'Debe ingresar el número de cuenta, no mas de 30 caracteres'
        )) {
        	if (this.value == numero_cuenta_an) {return;}
			let datos = new FormData();
			datos.append('validar','numero_cuenta');
			datos.append('numero_cuenta',$(this).val());
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
        /^[A-Za-z ]{3,30}$/,
        document.querySelector("#nombre_banco"),document.querySelector("#nombre_banco").nextElementSibling,'Solo letras, no mas de 20 caracteres'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del banco',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{4}$/,
        document.querySelector("#codigo"),document.querySelector("#codigo").nextElementSibling,'Debe ingresar el codigo del banco, cuatro digitos'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el codigo del banco',
		'El formato debe ser sólo en números');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[0-9]{18,30}$/,
        document.querySelector("#numero_cuenta"),document.querySelector("#numero_cuenta").nextElementSibling,'Debe ingresar el número de cuenta, no mas de 30 caracteres'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un número de cuenta',
		'Ejemplo: XXXX-XXXXX-XXX...');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{11}$/,
        document.querySelector("#telefono_afiliado"),document.querySelector("#telefono_afiliado").nextElementSibling,'Solo numeros, ejemplo xxxx-xxxxxxx'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un telefono afiliado',
		'El formato debe ser XXXX-XXXXXXX');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{7,8}$/,
        document.querySelector("#cedula_afiliada"),document.querySelector("#cedula_afiliada").nextElementSibling,'Solo numeros, no mas de 8 caracteres'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una cedula afiliada',
		'El formato debe ser sólo en números');
		
		return false;
	}

	if(numero_cuenta_an != $("#numero_cuenta").val()){
		datos = new FormData(); 
		datos.append('validar','numero_cuenta');
		datos.append('numero_cuenta',$("#numero_cuenta").val());
		res = await verificar_duplicados(datos);

		if(res){
			mensajes('error',4000,'Número de cuenta ya registrado','Este número de cuenta esta registrado, debe ingresar otro.');
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

async function verificar_duplicados(datos){

	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).classList.add('is-invalid');
		document.querySelector(`#${data.busqueda}`).classList.remove('is-valid');
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`;
		return true;
	}
	return false;
}
