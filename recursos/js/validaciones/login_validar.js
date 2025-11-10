let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let modal_carga = new bootstrap.Modal("#modal_carga");

let recuperacion_contrasenia = {
	enviada: false,
	tiempo: null
};

if (!(navigator.onLine)) {
	document.querySelector(".g-recaptcha").style.display = 'none'
}

document.getElementById('correo_login').addEventListener("keyup",e=>{
	validarKeyPress(/^[A-Za-z0-9_ .@\b]*$/, e);
	validarKeyUp(/^[A-Za-z0-9_ .]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,document.getElementById('correo_login'),'Ejemplo: alguien@servidor.com');
});

document.getElementById('correo_recuperar').addEventListener("keyup",e=>{
	validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
	validarKeyUp(/^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,document.getElementById('correo_recuperar'),'Ejemplo: alguien@servidor.com');
});

document.getElementById('contra').addEventListener("keyup",e=>{
	validarKeyPress(/^[A-Za-z0-9_.+*$#%&/]*$/, e);
	validarKeyUp(/^[A-Za-z0-9_.+*$#%&/]{5,50}$/,document.getElementById('contra'),'Minimo 5 caracteres, se permiten caracteres especiales');
});

document.getElementById('enviar').addEventListener("click",async e=>{	
	e.preventDefault();
	if(await validarEnvio()==true){
		let datos_consulta = new FormData();

		let usuario = document.getElementById('correo_login').value,
		contra = document.getElementById('contra').value,
		mantener_sesion = document.getElementById('checkbox_mantener_sesion').checked;

		let reCAPTCHA;
		if (!(navigator.onLine)) {
			reCAPTCHA = "no_internet";
		}
		else{
			reCAPTCHA = document.getElementById('g-recaptcha-response').value;
		}

		datos_consulta.append("usuario",usuario);
		datos_consulta.append("contra",contra);
		datos_consulta.append("mantener_sesion",mantener_sesion);
		datos_consulta.append("g-recaptcha-response",reCAPTCHA);
		datos_consulta.append("operacion","entrar");

		let resultado = await query(datos_consulta);
		
		if (resultado.estatus) {
			await obtenerTasaDolar();

			window.location = "?pagina=inicio_controlador.php&accion=inicio";
		}
		else if (resultado.estatus == false){
			mensajes('error',4000,resultado.mensaje,
		'Intenta nuevamente');
		}
	}
});

document.getElementById('boton_recuperar').addEventListener("click",async e=>{	
	e.preventDefault();
	//validacion de tiempo de envio
	if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        document.getElementById('correo_recuperar'),'Ejemplo: alguien@servidor.com'
        )){

		let datos_consulta = new FormData();

		let correo_recuperar = document.getElementById('correo_recuperar').value;
		let token = generarToken(200);

		datos_consulta.append("correo_recuperar",correo_recuperar);
		datos_consulta.append("token",token);
		datos_consulta.append("operacion","enviar_notificacion");

		await query(datos_consulta);

		mensajes('warning',8000,'Atencion',"Revise su bandeja de entrada del correo. Si el correo que ingreso está en el sistema, encontrará un enlace para recuperar su contraseña.");
		recuperacion_contrasenia.enviada = true;
		recuperacion_contrasenia.tiempo = new Date();
	}
	else{
		mensajes('error',4000,'Verifique el correo de recuperacion',
		'Ejemplo: alguien@servidor.com');
		
		return false;
	}
});

if (document.getElementById('resultado_cambio') != null) {
	mensajes('success',4000,'Atencion','La contraseña se ha cambiado exitosamente');
};

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
        document.getElementById('correo_login'),'Ejemplo: alguien@servidor.com'
        )==0)
	{
		mensajes('error',4000,'Verifique el correo',
		'El correo esta mal ingresado. Verifique nuevamente. Ejemplo: alguien@servidor.com');
		
		return false;
	}
	else if(validarKeyUp(
        /^[A-Za-z0-9_.+*$#%&/]{5,50}$/,
        document.getElementById('contra'),'Minimo 5 caracteres, se permiten caracteres especiales'
        )==0)
	{
		mensajes('error',4000,'Verifique la contraseña',
		'El formato debe tener mínimo 5 caracteres, utilizar letras, numeros y caracteres especiales como: _.+*$#%&/ ');
		
		return false;
	}
	if (navigator.onLine) {
		const recaptchaResponse = grecaptcha.getResponse();
	    if (recaptchaResponse.length === 0) {
	        mensajes('error',4000,'Verifique el reCAPTCHA','Debe completar la validación.');
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

function validarKeyUp(er, etiqueta, mensaje = '') {
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

async function query(datos) {
	peticionesActivas++;

	const tiempoInicio = performance.now();

	ultimaPeticion = tiempoInicio;

	if (peticionesActivas === 1) {
		tiempoCarga = setTimeout(()=>{
			modal_carga.show();
		}, 200);
	}

	try{
		let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;//Convertimos el resultado de json a js y lo mandamos
		});
		return data;
	}
	catch(error){
		console.log(error);
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
	finally{
		peticionesActivas--;

		if (peticionesActivas === 0) {
			const espera = 50;
			setTimeout(()=>{
				if (peticionesActivas === 0) {
					clearTimeout(tiempoCarga);

					const tiempoTranscurido = performance.now() - tiempoInicio;
					const tiempoEsperaMin = 400; //lo mini que debe durar la peticion

					if (tiempoTranscurido < tiempoEsperaMin) {
						const restante = tiempoEsperaMin - tiempoTranscurido;
						setTimeout(()=>{
							if (performance.now() - ultimaPeticion >= restante) {
								modal_carga.hide();
							}
						},restante);
					}
					else{
						modal_carga.hide();
					}
				}
			}, espera);
		}
	}
}

function generarToken(longitud) {
  let token = '';
  const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  for (let i = 0; i < longitud; i++) {
    token += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
  }
  return token;
}

async function obtenerTasaDolar(){
	const fechaTasaGuardada = localStorage.getItem('fecha_tasa_dolar');
    const tasaGuardada = localStorage.getItem('tasa_dolar');

	if (fechaTasaGuardada && tasaGuardada) {
        // Convertir la fecha ISO guardada a objeto Date
        const fechaTasa = new Date(fechaTasaGuardada);
        const fechaActual = new Date();
        
        // Comparar solo año, mes y día (ignorar hora)
        if (fechaTasa.toDateString() === fechaActual.toDateString()) {
            return { 
                estatus: true, 
                tasa: parseFloat(tasaGuardada),
                mensaje: "Tasa en cache",
                fecha: fechaTasaGuardada
            };
        }
    }

	peticionesActivas++;

	const tiempoInicio = performance.now();

	ultimaPeticion = tiempoInicio;

	if (peticionesActivas === 1) {
		tiempoCarga = setTimeout(()=>{
			modal_carga.show();
		}, 200);
	}

	try{
		const respuesta = await fetch("https://ve.dolarapi.com/v1/dolares/oficial"); 
        const data = await respuesta.json();
		
		localStorage.setItem('fecha_tasa_dolar', data.fechaActualizacion); // "2025-11-07T20:02:28.091Z"
		localStorage.setItem('tasa_dolar', data.promedio.toString()); 
	}
	catch (error){
		localStorage.setItem('error_tasa_dolar', error);
	}
	finally{
		peticionesActivas--;

		if (peticionesActivas === 0) {
			const espera = 50;
			setTimeout(()=>{
				if (peticionesActivas === 0) {
					clearTimeout(tiempoCarga);

					const tiempoTranscurido = performance.now() - tiempoInicio;
					const tiempoEsperaMin = 400; //lo mini que debe durar la peticion

					if (tiempoTranscurido < tiempoEsperaMin) {
						const restante = tiempoEsperaMin - tiempoTranscurido;
						setTimeout(()=>{
							if (performance.now() - ultimaPeticion >= restante) {
								modal_carga.hide();
							}
						},restante);
					}
					else{
						modal_carga.hide();
					}
				}
			}, espera);
		}
	}
}

// VALIDACIÓN CORREGIDA
// async function obtenerTasaDolar() {
//     const fechaTasaGuardada = localStorage.getItem('fecha_tasa_dolar');
//     const tasaGuardada = localStorage.getItem('tasa_dolar');
    
//     if (fechaTasaGuardada && tasaGuardada) {
//         // Convertir la fecha ISO guardada a objeto Date
//         const fechaTasa = new Date(fechaTasaGuardada);
//         const fechaActual = new Date();
        
//         // Comparar solo año, mes y día (ignorar hora)
//         if (fechaTasa.toDateString() === fechaActual.toDateString()) {
//             return { 
//                 estatus: true, 
//                 tasa: parseFloat(tasaGuardada),
//                 mensaje: "Tasa en cache",
//                 fecha: fechaTasaGuardada
//             };
//         }
//     }

//     // Si llegamos aquí, necesitamos actualizar
//     try {
//         const respuesta = await fetch("https://dolarapi.com/v1/dolares/blue"); // o el endpoint correcto
//         const data = await respuesta.json();
        
//         // Guardar la fecha COMPLETA que viene de la API
//         localStorage.setItem('fecha_tasa_dolar', data.fechaActualizacion); // "2025-11-07T20:02:28.091Z"
//         localStorage.setItem('tasa_dolar', data.venta.toString()); // Asumiendo que usas 'venta'
        
//         return {
//             estatus: true,
//             tasa: data.venta,
//             mensaje: "Tasa actualizada correctamente",
//             fecha: data.fechaActualizacion
//         };
//     } catch (error) {
//         // Manejo de errores...
//     }
// }

// Callbacks para reCAPTCHA
function onRecaptchaSuccess(token) {
    document.getElementById('enviar').disabled = false;    
}

function onRecaptchaExpired() {
    document.getElementById('enviar').disabled = true;    
}

function onRecaptchaError() {
    document.getElementById('enviar').disabled = true;    
}