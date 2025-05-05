$(document).ready(function(){

	$("#nombre").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#nombre").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el nombre del usuario");
	});

	$("#apellido").on("keypress",function(e){
		validarKeyPress(/^[A-Za-z \b]*$/, e);
	});

	$("#apellido").on("keyup",function(){
		validarKeyUp(/^[A-Za-z \b]{3,30}$/,
		$(this),this.nextElementSibling,"Debe ingresar el apellido del usuario");
	});

	$("#cedula").on("keypress",function(e){
		validarKeyPress(/^[0-9\b]*$/, e);
	});

	$("#cedula").on("keyup",function(e){
		validarKeyUp(/^[0-9]{7,8}$/,$(this),
		this.nextElementSibling,"Solo numeros, no menos de 7 y mas de 8");
	});

	$("#correo").on("keypress",function(e){	
		validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
	});

	$("#correo").on("keyup",function(e){
		validarKeyUp(/^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,$(this),
		this.nextElementSibling,"El formato debe ser ejemplo@gmail.com");
	});

	$("#contra").on("keyup",function(e){
		validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{4,50}$/,
        $(this),this.nextElementSibling,'La contraseña debe tener mínimo 5 caracteres'
        )
	});
	
	$("#boton_formulario").on("click",async function(e){
		let accion = (e.target.getAttribute("modificar"))?"Editar":"Registrar";		
		e.preventDefault();
		if(await validarenvio(accion)==true){
				Swal.fire({
				title: "¿Estás seguro?",
				text: `¿Está seguro que desea ${accion} este usuario?`,
				showCancelButton: true,
				confirmButtonText: accion,
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			    }).then((result) => {
					if (result.isConfirmed) {
						envio(accion);
						cedula_an = null;
						correo_an = null;
					}
			    });
		}	
	});

	// Para que verifique cada tanto si ya hay botones para eliminar, 
	// Sin esto daria null
	let intervalo = setInterval(function(){
		if ($(".eliminar").length == 0) {return;}

		$(".eliminar").on("click",function(e){
			id = e.target.value;
			if (id == undefined) {	
				id = e.target.parentElement.value;
			}
			Swal.fire({
				title: "¿Estás seguro?",
				text: "¿Está seguro que desea eliminar este usuario?",
				showCancelButton: true,
				confirmButtonText: "Eliminar",
				confirmButtonColor: "#e01d22",
				cancelButtonText: "Cancelar",
				icon: "warning"
				}).then((resultado) => {
					if (resultado.isConfirmed) {
						eliminar(id)
					}
				});
		});

		clearInterval(intervalo);
	},1000)

	// AJAX

	$("#cedula").on("keyup",function(){
		if ($(this).val().length > 6 && $(this).val().length < 9) {
			let datos = new FormData();
			datos.append('validar','cedula');
			datos.append('tipo_cedula',this.closest("form").querySelector("#tipo_cedula").value);
			datos.append('cedula',$(this).val());			
			verificar_duplicados(datos);
		}
	})
	
	$("#correo").on("keyup",function(e){
		if ($(this).val().length > 6 || $(this).val().length < 9) {			
			let datos = new FormData();
			datos.append('validar','correo');
			datos.append('correo',$(this).val());
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

async function validarenvio(){	
	if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#nombre"),document.querySelector("#nombre").nextElementSibling,'Debe ingresar el nombre del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el nombre del usuario',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#apellido"),document.querySelector("#apellido").nextElementSibling,'Debe ingresar el apellido del usuario'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar el apellido del usuario',
		'El formato debe ser sólo en letras');
		
		return false;
	}
	else if(validarKeyUp(
        /^[0-9]{7,9}$/,
        $("#cedula"),document.querySelector("#cedula").nextElementSibling,'Debe ingresar la cédula de identidad'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar la cédula de identidad',
		'El formato debe ser 12345678');
		
		return false;
	}
	
	else if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        $("#correo"),document.querySelector("#correo").nextElementSibling,'Ejemplo: alguien@servidor.com'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar un correo electrónico',
		'Ejemplo: alguien@servidor.com');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&@]{5,50}$/,
        $("#contra"),document.querySelector("#contra").nextElementSibling,'Debe ingresar una contraseña'
        )==0)
	{
		mensajes('error',4000,'Debe ingresar una contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(validar_contra()==0)
	{
		mensajes('error',4000,'Verifique nuevamente la contraseña',
		'El campo "contraseña" y el campo "confirmar contraseña" no coinciden');
		
		return false;
	}
	else if(validar_select("rol")==0)
	{
		mensajes('error',4000,'Debe ingresar el rol',
		'Debe seleccionar una opción');
		
		return false;
	}	
	
		if(cedula_an != $("#cedula").val()){
			let datos = new FormData();
			datos.append('validar','cedula');
			datos.append('tipo_cedula',document.querySelector("#tipo_cedula").value);
			datos.append('cedula',$("#cedula").val());
			let res = await verificar_duplicados(datos);

			if(res){
				mensajes('error',4000,'Cedula ya registrada','Esta cedula esta registrada, debe ingresar otra.');
				return false;
			}
		}
		if(correo_an != $("#correo").val()){
			datos = new FormData();
			datos.append('validar','correo');
			datos.append('correo',$("#correo").val());
			res = await verificar_duplicados(datos);

			if(res){
				mensajes('error',4000,'Correo ya registrado','Este correo esta registrado, debe ingresar otro.');
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

function validar_contra() {
	let contra = document.querySelector("#contra");
	let confir_contra = document.querySelector("#confir_contra");
	if (contra.value == confir_contra.value) {
		return 1;
	}else{
		return 0;
	}
}
// AJAX
async function verificar_duplicados(datos,accion = "registrar"){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// console.log(data)
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrada`
		return true;
	}
	return false;
}

function enviaAjax(datos) {
  $.ajax({
    async: true,
    url: "",
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
    beforeSend: function () {},
    timeout: 10000,
    success: function (respuesta) {
    // console.log(respuesta);
      try {
        let lee = JSON.parse(respuesta);        
        if (lee.resultado == true) {
        	if (lee.busqueda == "cedula") {
        		//cosole.log(lee)
        		document.querySelector("#contra").nextElementSibling.textContent = "Cédula de identidad ya registrada";
        	}
        	else if (lee.busqueda == "correo") {
        		document.querySelector("#correo").nextElementSibling.textContent = "Correo electrónico ya registrado";        			
        	}
        }

      } catch (e) {
      	console.log(e)
        alert("Error en JSON " + e.name);
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("Servidor ocupado, intente de nuevo");
      } else {
        muestraMensaje("ERROR: <br/>" + request + status + err);
      }
    },
    complete: function () {},
  });
}
//Fin de AJAX