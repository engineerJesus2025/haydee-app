obtenerCopiasGuardadas();
// Elementos
let boton_exportar = document.getElementById('boton_exportar'),
 boton_importar = document.getElementById('boton_importar'), 
 select_db = document.getElementById('select_db'),
 select_copias = document.getElementById('select_copias'),
 file = document.getElementById('archivo_importar');

select_db.addEventListener("change",e=>{
	if (e.target.value != '') {
		boton_exportar.removeAttribute("hidden");
	}
});
select_copias.addEventListener("change",e=>{
	if (e.target.value != '') {
		boton_importar.removeAttribute("hidden");
	}
});

boton_exportar.addEventListener("click",async e=>{
	if (select_db.value == "") {
		mensajes('error',4000,'Atención',
			'Debe seleccionar una Base de Datos para la copia de seguridad');
		return;
	}
	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea exportar esta base de datos.?`,
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

boton_importar.addEventListener("click",async e=>{
	if (select_copias.value == "") {
		mensajes('error',4000,'Atención',
			'Debe seleccionar una Base de Datos para la copia de seguridad');
		return;
	}
	Swal.fire({
		title: "¿Estás seguro?",
		text: `¿Está seguro que desea importar esta base de datos. Esta acción no se puede revertir?`,
		showCancelButton: true,
		confirmButtonText: "Importar",
		confirmButtonColor: "#1b8a40",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((result) => {
		if (result.isConfirmed) {
			importarCopiaSeguridad();
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

async function query(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json();		
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// console.log(data);
	return data;
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