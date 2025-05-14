document.getElementById('correo').addEventListener("keypress",e=>{
	validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
})

document.getElementById('contra').addEventListener("keypress",e=>{
	validarKeyPress(/^[A-Za-z0-9_.+*$#%&/]*$/, e);
})

document.getElementById('enviar').addEventListener("click",async e=>{	
	e.preventDefault();
	if(await validarEnvio()==true){
		let datos_consulta = new FormData();

		let usuario = document.getElementById('correo').value;
		let contra = document.getElementById('contra').value;

		datos_consulta.append("usuario",usuario);
		datos_consulta.append("contra",contra);
		datos_consulta.append("operacion","entrar");

		let resultado = await loguear(datos_consulta);

		if (resultado.estatus) {
			window.location = "?pagina=inicio_controlador.php&accion=inicio"
		}else if (resultado.estatus == false){
			mensajes('error',4000,resultado.mensaje,
		'Intenta nuevamente');
		}
	}
})	

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

function validarEnvio(){
	if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        document.getElementById('correo')
        )==0)
	{
		mensajes('error',4000,'Verifique el correo',
		'Ejemplo: alguien@servidor.com');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&/]{5,50}$/,
        document.getElementById('contra')
        )==0)
	{
		mensajes('error',4000,'Verifique la contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
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

function validarKeyUp(er, etiqueta) {
    a = er.test(etiqueta.value);
    if (a) {
        return 1;
    } else {
        return 0;
    }
}

async function loguear(datos) {
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	return data;
}