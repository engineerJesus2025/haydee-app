obtenerCopiasGuardadas();
// Elementos
let boton_exportar = document.getElementById('boton_exportar'),
 boton_descargar = document.getElementById('boton_descargar'),
 boton_importar = document.getElementById('boton_importar'), 
 select_db = document.getElementById('select_db'),
 select_copias = document.getElementById('select_copias'),
 input_file = document.getElementById('input_file_importar');

boton_exportar.parentElement.setAttribute("hidden",'');
boton_descargar.parentElement.setAttribute("hidden",'');

select_db.addEventListener("change",e=>{
	if (e.target.value != '') {
		let valido = /^negocio|seguridad/.test(e.target.value);

		if (!valido) {
			e.target.classList.remove('is-valid');
			e.target.classList.add('is-invalid');
			e.target.nextElementSibling.textContent = "La base de datos seleccionada no existe";

			boton_exportar.parentElement.setAttribute("hidden",'');
			boton_descargar.parentElement.setAttribute("hidden",'');
			document.getElementById('o').setAttribute('hidden','');
		}
		else{
			e.target.classList.add('is-valid');
			e.target.classList.remove('is-invalid');
			e.target.nextElementSibling.textContent = "";

			boton_exportar.parentElement.removeAttribute("hidden");
			boton_descargar.parentElement.removeAttribute("hidden");
			document.getElementById('o').removeAttribute('hidden');
		}
	}
});
select_copias.addEventListener("change",e=>{
	if (e.target.value != '') {		
		let valido = /^backup(_seguridad)?_haydee_db_\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.sql$/.test(select_copias.value);

		if (!valido) {
			select_copias.classList.remove('is-valid');
			select_copias.classList.add('is-invalid');
			select_copias.nextElementSibling.textContent = "La copia de seguridad seleccionada no existe";

			boton_importar.setAttribute("hidden",'');

			return;
		}
		else{
			select_copias.classList.add('is-valid');
			select_copias.classList.remove('is-invalid');
			select_copias.nextElementSibling.textContent = "";

			boton_importar.removeAttribute("hidden");
		}

		input_file.value = '';
		// boton_importar.removeAttribute("hidden");
	}
});
input_file.addEventListener("change",e=>{
	if (e.target.value != '') {
		select_copias.value = '';		
		boton_importar.removeAttribute("hidden");
	}
});

boton_exportar.addEventListener("click",async e=>{
	if (select_db.value == "") {		
		mensajes('error',4000,'Atención',
			'Debe seleccionar una Base de Datos para la copia de seguridad');
		return;
	}
	else{
		let valido = /^negocio|seguridad/.test(select_db.value);

		if (!valido) {
			select_db.classList.remove('is-valid');
			select_db.classList.add('is-invalid');
			select_db.nextElementSibling.textContent = "La base de datos seleccionada no existe";

			mensajes('error',4000,'Atención',
			'La base de datos seleccionada no existe');

			boton_exportar.parentElement.setAttribute("hidden",'');
			boton_descargar.parentElement.setAttribute("hidden",'');
			document.getElementById('o').setAttribute('hidden','');

			return;
		}
		else{
			select_db.classList.add('is-valid');
			select_db.classList.remove('is-invalid');
			select_db.nextElementSibling.textContent = "";

			boton_exportar.parentElement.removeAttribute("hidden");
			boton_descargar.parentElement.removeAttribute("hidden");
			document.getElementById('o').removeAttribute('hidden');
		}
	}
	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea exportar esta base de datos?`,
		showCancelButton: true,
		confirmButtonText: "Exportar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			generarCopiaSeguridad();
		}
	});	
});

boton_descargar.addEventListener("click",async e=>{
	e.preventDefault();
	if (select_db.value == "") {
		mensajes('error',4000,'Atención',
			'Debe seleccionar una Base de Datos para descargar la copia de seguridad');
		return;
	}
	else{
		let valido = /^negocio|seguridad/.test(select_db.value);

		if (!valido) {
			select_db.classList.remove('is-valid');
			select_db.classList.add('is-invalid');
			select_db.nextElementSibling.textContent = "La base de datos seleccionada no existe";

			mensajes('error',4000,'Atención',
			'La base de datos seleccionada no existe');

			boton_exportar.parentElement.setAttribute("hidden",'');
			boton_descargar.parentElement.setAttribute("hidden",'');
			document.getElementById('o').setAttribute('hidden','');

			return;
		}
		else{
			select_db.classList.add('is-valid');
			select_db.classList.remove('is-invalid');
			select_db.nextElementSibling.textContent = "";

			boton_exportar.parentElement.removeAttribute("hidden");
			boton_descargar.parentElement.removeAttribute("hidden");
			document.getElementById('o').removeAttribute('hidden');
		}
	}
	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea descargar esta base de datos?`,
		showCancelButton: true,
		confirmButtonText: "Exportar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			document.getElementById('db_input').value = select_db.value;
			e.target.closest("form").submit();
		}
	});	
});

boton_importar.addEventListener("click",async e=>{
	if (select_copias.value == '' && input_file.value == '') {
		mensajes('error',4000,'Atención',
			'Debe seleccionar una Base de Datos para la copia de seguridad, o importar un archivo compatible');
		return;
	}
	else if (select_copias.value != ''){
		let valido = /^backup(_seguridad)?_haydee_db_\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.sql$/.test(select_copias.value);

		if (!valido) {
			select_copias.classList.remove('is-valid');
			select_copias.classList.add('is-invalid');
			select_copias.nextElementSibling.textContent = "La copia de seguridad seleccionada no existe";

			boton_importar.parentElement.setAttribute("hidden",'');
			boton_descargar.parentElement.setAttribute("hidden",'');
			document.getElementById('o').setAttribute('hidden','');

			mensajes('error',4000,'Atención','La copia de seguridad seleccionada no existe');

			return;
		}
		else{
			select_copias.classList.add('is-valid');
			select_copias.classList.remove('is-invalid');
			select_copias.nextElementSibling.textContent = "";

			boton_importar.parentElement.removeAttribute("hidden");
			boton_descargar.parentElement.removeAttribute("hidden");
			document.getElementById('o').removeAttribute('hidden');
		}
	}
	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea importar esta base de datos? Esta acción no se puede revertir.`,
		showCancelButton: true,
		confirmButtonText: "Importar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			if (input_file.value == '') {
				importarCopiaSeguridad();
			}
			else{
				importarSQL();
			}
		}
	});	
});

async function obtenerCopiasGuardadas() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion','obtener_copias');
	
	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	if (respuesta.mensaje.length == 0) {
		select_copias[0].textContent = "No hay copias guardadas";
		select_copias[0].value = '';
		select_copias.setAttribute("disabled","");
		return;
	}

	let fragment = document.createDocumentFragment();

	respuesta.mensaje.map(fichero=>{
		let option = document.createElement("option");

		option.textContent = fichero;
		option.value = fichero;

		fragment.appendChild(option);
	});

	select_copias.appendChild(fragment);
}

async function query(datos,oscuro = false) {
    if (oscuro) {document.getElementById('icono_carga').setAttribute("class",`loader_dark`);}
    else{document.getElementById('icono_carga').setAttribute("class",`loader`);}
    
	let modal_carga = new bootstrap.Modal("#modal_carga");
	let mostrarModal = false;
    let tiempoCarga;

	tiempoCarga = setTimeout(()=>{
		mostrarModal = true;
		modal_carga.show();
	}, 600);
	
	try{
		const tiempoInicio = performance.now();

		const res = await fetch("", { method: "POST", body: datos });
    	const data = await res.json();

		const tiempoTranscurido = performance.now() - tiempoInicio;
		const tiempoEsperaMin = 700;
		
		if (mostrarModal && tiempoTranscurido < tiempoEsperaMin) {
			
			const restante = tiempoEsperaMin - tiempoTranscurido;
			await new Promise(resolve => setTimeout(resolve,restante));
		}

		return data;
	}
	catch(error){
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
	finally{
		clearTimeout(tiempoCarga);
		if (mostrarModal) {
			modal_carga.hide();
		}
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

async function importarCopiaSeguridad() {
	let datos_consulta = new FormData();

	let fichero = select_copias.value;
	let db = (select_copias.value.includes("seguridad"))?'seguridad':'negocio';

	datos_consulta.append("fichero",fichero);
	datos_consulta.append("db",db);
	
	datos_consulta.append('operacion','importar_copia_seguridad');
	
	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	mensajes('success',4000,'Atencion',respuesta.mensaje);
}

async function importarSQL() {
	let datos_consulta = new FormData();

	// let fichero = select_copias.value;
	// let db = (select_copias.value.includes("seguridad"))?'seguridad':'negocio';

	datos_consulta.append("fichero",input_file.files[0]);
	// datos_consulta.append("db",db);
	
	datos_consulta.append('operacion','importar_archivo_sql');
	
	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	mensajes('success',4000,'Atencion',respuesta.mensaje);
}

async function generarCopiaSeguridad() {
	let datos_consulta = new FormData();

	let db = select_db.value;

	datos_consulta.append("db",db);

	datos_consulta.append('operacion','generar_copia_seguridad');
	
	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	mensajes('success',4000,'Atencion',respuesta.mensaje);
}

//En caso de error
const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('e');
if (error != null) {
	const currentURL = new URL(window.location.href);
	const searchParams = new URLSearchParams(currentURL.search);
	searchParams.delete('e');
	currentURL.search = searchParams.toString();
	window.history.replaceState({}, '', currentURL.toString());
	mensajes('error',4000,'Atencion',"A ocurrido un error");
}