document.getElementById('contra').addEventListener("keypress",e=>{
	validarKeyPress(/^[A-Za-z0-9_.+*$#%&/]*$/, e);
});

document.getElementById('confir_contra').addEventListener("keypress",e=>{
	validarKeyPress(/^[A-Za-z0-9_.+*$#%&/]*$/, e);
});

document.getElementById('cambiar_contrasenia').addEventListener('click',e=>{	
	e.preventDefault();
	if(validarEnvio()==true){
		Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea cambiar esta contraseña?`,
		showCancelButton: true,
		confirmButtonText: "Cambiar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
		}).then((result) => {
			if (result.isConfirmed) {
				e.target.closest("form").submit();
			}
		});
	}
});



function validarEnvio(){
	if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&/]{5,50}$/,
        document.getElementById('contra')
        )==0)
	{
		mensajes('error',4000,'Verifique la contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&/]{5,50}$/,
        document.getElementById('confir_contra')
        )==0)
	{
		mensajes('error',4000,'Verifique la casilla "confirmar contraseña"',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	else if(document.getElementById('confir_contra').value != document.getElementById('contra').value)
	{
		mensajes('error',4000,'Atención',
		'al campo "Contraseña" y "Confirmar contraseña no coinciden". Deben coincidir.');
		
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

async function query(datos) {
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	return data;
}