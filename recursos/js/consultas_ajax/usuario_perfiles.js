let correo_an;

let formulario_usar = document.querySelector(`#modal_contra`); 
//Eventos:
document.querySelector(`#modal_contra`).addEventListener("hide.bs.modal",()=>{
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));

	document.querySelectorAll('input').forEach(input=>{
		if (input.id.includes('contra')) {
			input.nextElementSibling.classList.remove('border-danger');
			input.nextElementSibling.classList.remove('text-danger');
			input.nextElementSibling.classList.remove('border-success');
			input.nextElementSibling.classList.remove('text-success');

			input.nextElementSibling.firstElementChild.classList.replace('bi-eye-slash','bi-eye');
		}
		input.value = '';
	});
});

// Efecto de mostrar/ocultar la constraseña
document.querySelectorAll('.contra').forEach(boton=>{
	boton.addEventListener('click',e=>{
		e.preventDefault();

		let i;
		if (e.target.firstElementChild == null) {
			i = e.target;
		}
		else{
			i = e.target.firstElementChild;
		}

		if (i.classList.contains('bi-eye')){
			i.parentElement.previousElementSibling.setAttribute('type','text')
			i.classList.replace('bi-eye','bi-eye-slash');
		}
		else{
			i.parentElement.previousElementSibling.setAttribute('type','password')
			i.classList.replace('bi-eye-slash','bi-eye');
		}
	});
});

document.getElementById('boton_editar').addEventListener('click',e=>{
	document.getElementById("nombre").value = document.getElementById("p_nombre").textContent;
	document.getElementById("apellido").value = document.getElementById("p_apellido").textContent;
	document.getElementById("correo").value = document.getElementById("p_correo").textContent;

	document.getElementById('body_perfil').setAttribute('hidden','');
	document.getElementById('form_perfil').removeAttribute('hidden');

	document.getElementById('boton_editar').setAttribute('disabled','');
});

document.getElementById('boton_cancelar').addEventListener('click',e=>{
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));

	document.getElementById('form_perfil').setAttribute('hidden','');
	document.getElementById('body_perfil').removeAttribute('hidden');

	document.getElementById('boton_editar').removeAttribute('disabled','');
});

async function llenarCardUsuario(){
	let datos_consulta = new FormData();

	datos_consulta.append('operacion','consultar_perfil_usuario');

	let usuario = await query(datos_consulta);
	
	let [clases_badge_rol,clases_icono_rol] = definirColorBadge(usuario.nombre_rol);

	document.getElementById("titulo_nombre").textContent = `${usuario.nombre_usuario} ${usuario.apellido}`
	document.getElementById("titulo_rol").textContent = usuario.nombre_rol;
	document.getElementById("p_nombre").textContent = usuario.nombre_usuario;
	document.getElementById("p_apellido").textContent = usuario.apellido;
	document.getElementById("p_correo").textContent = usuario.correo;
	document.getElementById("spam_rol").textContent = usuario.nombre_rol;
	
	document.getElementById("ultimo_acceso").textContent = formatearUltimoAcceso(usuario.ultima_vez);

	correo_an = usuario.correo;

	let icono_rol = document.createElement("i");
	icono_rol.setAttribute('class',clases_icono_rol);
	icono_rol.setAttribute('style',"font-size: 4rem !important;");

	document.getElementById("titulo_icono").textContent = null;
	document.getElementById("titulo_icono").appendChild(icono_rol);	

	document.getElementById("spam_rol").setAttribute('class',clases_badge_rol);
}

function definirColorBadge(nombre_rol){
	switch (nombre_rol){
		case 'Administrador Global':
			return ["badge bg-warning text-dark","bi bi-globe me-3"];
			break;
		case 'Administrador':
			return ["badge bg-primary","bi bi-person-fill-gear me-3"];
			break;
		case 'Propietario':
			return ["badge bg-success","bi bi-key-fill me-3"];
			break;

		case 'Contador':
			return ["badge bg-danger","bi bi-calculator-fill me-3"];
			break;
		case 'Presidente':
			return ["badge bg-info text-dark","bi bi-award-fill me-3"];
			break;
		default:
		return ["badge bg-secondary","bi bi-person-circle me-3"];
		break;
	}
}

function formatearUltimoAcceso(fecha) {
	const ahora = new Date();
	if(!fecha){
		return ahora.toLocaleDateString('es-ES');
	}
    const fechaISO = fecha.replace(" ", "T");
    const fechaAcceso = new Date(fechaISO);
    

    const diffMs = ahora - fechaAcceso;
    const diffDias = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    let hora = fechaAcceso.getHours();
    const minutos = fechaAcceso.getMinutes().toString().padStart(2, '0');
    const amPm = hora >= 12 ? 'p.m.' : 'a.m.';
    
    hora = hora % 12 || 12; // Convierte 0 a 12
    const horaFormateada = `${hora}:${minutos}${amPm}`;
    
    if (diffDias === 0) {
        return `Hoy a las ${horaFormateada}`;
    } else if (diffDias === 1) {
        return `Ayer a las ${horaFormateada}`;
    } else if (diffDias <= 7) {
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return `El ${diasSemana[fechaAcceso.getDay()]} a las ${horaFormateada}`;
    } else {
        return fechaAcceso.toLocaleDateString('es-ES') + ` a las ${horaFormateada}`;
    }
}

async function modificar() {
	let datos_consulta = new FormData();

	let nombre = document.querySelector("#nombre").value,
	apellido = document.querySelector("#apellido").value,
	correo = document.querySelector("#correo").value;	

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);	
	datos_consulta.append("correo",correo);
	// datos_consulta.append("contra",nueva_contra);

	datos_consulta.append('operacion','editar_perfil');

	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	document.querySelectorAll('input').forEach(input=>input.value = '');

 	llenarCardUsuario();

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');

	document.getElementById('form_perfil').setAttribute('hidden','');
	document.getElementById('body_perfil').removeAttribute('hidden');
	document.getElementById('boton_editar').removeAttribute('disabled','');
}

async function modificarContra() {
	let datos_consulta = new FormData();

	let nueva_contra = document.getElementById("contra").value;	
	let correo = document.getElementById("p_correo").textContent;

	datos_consulta.append("contra",nueva_contra);
	datos_consulta.append("correo",correo);

	datos_consulta.append('operacion','cambiar_contrasenia');

	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	document.querySelectorAll('input').forEach(input=>input.value = '');

	mensajes('success',4000,'Atencion','La contraseña se ha modificado exitosamente');
	
	modal.hide();
}



async function query(datos) {	
	try{
		const res = await fetch("", { method: "POST", body: datos });
    	const data = await res.json();

		return data;
	}
	catch(error){
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
}

function mensajes(icono,tiempo,titulo,mensaje){
	Swal.fire({
	icon:icono,
    timer:tiempo,	
    title:titulo,
	text:mensaje,
	confirmButtonText:'Aceptar',
	confirmButtonColor: "#e01d22",
	});
}

llenarCardUsuario();