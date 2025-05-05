consultar(true); // para llenar la tabla al cargar

//Este es el boton del formulario, lo usaremos para registrar o modificar
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#registrar_usuario");
// EL fomulario principal de la pagia
let formulario_usar = document.querySelector(`#form_usuario`);
let correo_an, cedula_an;
//En caso de que se envie un formulario

function envio(operacion) {	
	if (operacion == "Editar") {
		console.log(operacion)
		id_modificar = boton_formulario.getAttribute("id_modificar")
		modificar(id_modificar);
	}else if(operacion == "Registrar"){
		//sino a registrar
		registrar()
	}
}

document.querySelector(`#registrar_usuario`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";
	document.getElementById('titulo_modal').textContent = "Registrar Usuario";	
});

//Si queremos registrar:
async function registrar() {
	// el async vuelve la funcion asincrona	
	//Creamos el formData
	datos_consulta = new FormData()
	let nombre = formulario_usar.querySelector("#nombre").value,
	apellido = formulario_usar.querySelector("#apellido").value,
	tipo_cedula = formulario_usar.querySelector("#tipo_cedula").value, 
	cedula = formulario_usar.querySelector("#cedula").value, 
	correo = formulario_usar.querySelector("#correo").value, 
	contra = formulario_usar.querySelector("#contra").value, 
	rol = formulario_usar.querySelector("#rol").value;

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);
	datos_consulta.append("tipo_cedula",tipo_cedula);
	datos_consulta.append("cedula",cedula);
	datos_consulta.append("correo",correo);
	datos_consulta.append("contra",contra);
	datos_consulta.append("rol",rol);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','registrar');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta); // El await es para que espere el resultado, al ser asincrono, normalmente no lo esperaria
	
	modal.hide()

	formulario_usar.reset();

	consultar(); // esto es para actualizar la tabla al registrar
	consulta_completada();
}

//Si queremos consultar
async function consultar(inicial = false) {
	
	//Creamos el formData
	datos_consulta = new FormData()

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta');

	//Llamamos a la funcion para hacer la consulta
	data = await query(datos_consulta)
	vaciar_tabla(); //Vaciamos la tabla de lo que tenia antes
	//recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
	await data.map(fila=>{
		llenarTabla(fila)
	})
	if (inicial) {data_table()}
}

// Esta funcion hace lo que dice
function vaciar_tabla() {
	let cuerpo_tabla = document.querySelector(`#tabla_usuario tbody`);
	cuerpo_tabla.textContent = null;
}

// esta tambien, se ve larga, pero no es tan complicada  **********
function llenarTabla(fila) {
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_usuario tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");	
	id_informacion = document.createElement("td");

	let id_campo = fila["id_usuario"];
	
	id_informacion.textContent = id_campo;
	fila_tabla.appendChild(id_informacion);

	let nombre_td = document.createElement("td"),
	apellido_td = document.createElement("td"),
	cedula_td = document.createElement("td"), 
	correo_td = document.createElement("td"), 
	rol_td = document.createElement("td");

	cedula_td.textContent = fila["cedula"];
	nombre_td.textContent = fila["nombre_usuario"];
	apellido_td.textContent = fila["apellido"];
	correo_td.textContent = fila["correo"];
	rol_td.textContent = fila["nombre_rol"];

	// Llenamos la etiquetas
	fila_tabla.appendChild(cedula_td);
	fila_tabla.appendChild(nombre_td);
	fila_tabla.appendChild(apellido_td);
	fila_tabla.appendChild(correo_td);
	fila_tabla.appendChild(rol_td);
	
	// Creamos los botones de las acciones
	let acciones = document.createElement("td");
	acciones.setAttribute("class","row justify-content-evenly")

	//creamos el boton de eliminar, le damos valor, y le asignamos la funcion para eliminar
	let boton_eliminar = document.createElement("button");

	let icono_eliminar = document.createElement("i");
	icono_eliminar.setAttribute("class", "bi bi-trash");
	boton_eliminar.appendChild(icono_eliminar);
	
	boton_eliminar.setAttribute("type", "button");
	boton_eliminar.setAttribute("class", "btn btn-danger btn-sm eliminar col-3");
	boton_eliminar.setAttribute("tabindex", "-1");
	boton_eliminar.setAttribute("role", "button");
	boton_eliminar.setAttribute("aria-disabled", "true");

	boton_eliminar.setAttribute("title","Eliminar");
	boton_eliminar.setAttribute("value",id_campo);
	// boton_eliminar.addEventListener("click",eliminar);//Esa funcion esta mas abajo

	// Lo mismo que arriba, pero con modificar
	let boton_editar = document.createElement("button");

	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square")
	boton_editar.appendChild(icono_editar);

	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-3");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#registrar_usuario");

	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id_campo);
	boton_editar.addEventListener("click",modificar_formulario)//Esa funcion esta mas abajo

	acciones.appendChild(boton_editar);//Le ponemos los botones al <td><td> de las acciones
	acciones.appendChild(boton_eliminar);
	fila_tabla.setAttribute("id",`fila-${id_campo}`);
	// le ponemos un id a las fila para cuando las eliminemos
	//Llenamos la fila con la info
	
	// ...
	fila_tabla.appendChild(acciones);

	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);	
}

// si queremos eliminar
async function eliminar(id) {
	//Creamos el formData
	datos_consulta = new FormData()

	// Le ponemos el id al FormData
	datos_consulta.append("id_usuario",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','eliminar');

	//Llamamos a la funcion para hacer la consulta
	let result = await query(datos_consulta)
	
	eliminar_fila(id); // y por ultimo, eliminamos esa fila con esta funcion
	consulta_completada();
}

// elimina una fila de la tabla
function eliminar_fila(id) {
	let cuerpo_tabla = document.querySelector(`#tabla_usuario tbody`);
	let fila_eliminar = cuerpo_tabla.querySelector(`#fila-${id}`);
	cuerpo_tabla.removeChild(fila_eliminar);
}

// Esta funcion prepara el formulario para editar el registro
async function modificar_formulario(e) {
	// primero buscamos el registro a modificar
	//Creamos el formData
	datos_consulta = new FormData()
	
	// Le ponemos los datos del formulario
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value;
	}
	datos_consulta.append("id_usuario",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta_especifica');

	//Llamamos a la funcion para hacer la consulta y guardamos los datos
	data = await query(datos_consulta);	
	
	// ahora seleccionamos los inputs
	let nombre = formulario_usar.querySelector("#nombre"),
	apellido = formulario_usar.querySelector("#apellido"),
	tipo_cedula = formulario_usar.querySelector("#tipo_cedula"),
	cedula = formulario_usar.querySelector("#cedula"),
	correo = formulario_usar.querySelector("#correo"),
	contra = formulario_usar.querySelector("#contra"),
	rol = formulario_usar.querySelector("#rol");

	nombre.value = data.nombre_usuario;
	apellido.value = data.apellido;
	tipo_cedula.value = data.cedula.substring(0,1);
	cedula.value = data.cedula.substring(1);
	correo.value = data.correo;
	contra.value = data.contra;
	rol.value = data.rol_id;

	let boton_formulario = document.querySelector("#boton_formulario");	

	// aqui cambiamos los datos del boton para registrar, para saber que ahora se va es a modificar un registro
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_usuario);
	boton_formulario.textContent = "Modificar";

	cedula_an = cedula.value;
	correo_an = correo.value;

	document.getElementById('titulo_modal').textContent = "Modificar Usuario";
}

//si queremos modificar
async function modificar(id) {	
	//Creamos el formData
	let datos_consulta = new FormData();

	//Guardamos los datos del formulario
	let nombre = formulario_usar.querySelector("#nombre").value,
	apellido = formulario_usar.querySelector("#apellido").value,
	tipo_cedula = formulario_usar.querySelector("#tipo_cedula").value, 
	cedula = formulario_usar.querySelector("#cedula").value, 
	correo = formulario_usar.querySelector("#correo").value, 
	contra = formulario_usar.querySelector("#contra").value, 
	rol = formulario_usar.querySelector("#rol").value;
	
	// Le ponemos los datos del formulario
	datos_consulta.append("id_usuario",id);

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);
	datos_consulta.append("tipo_cedula",tipo_cedula);
	datos_consulta.append("cedula",cedula);
	datos_consulta.append("correo",correo);
	datos_consulta.append("contra",contra);
	datos_consulta.append("rol",rol);
	// ...

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','modificar');

	//Llamamos a la funcion para hacer la consulta
	await query(datos_consulta)

	consultar(); // Volvemos a llenar la tabla
	formulario_usar.reset();

 	modal.hide();

	// al terminar le damos al boton su valores originales

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";

	document.getElementById('titulo_modal').textContent = "Registrar Usuario";

	consulta_completada()
}

// Aqui se hace la peticion AJAX
async function query(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	
	return data;
}

function consulta_completada(){
	Swal.fire({
	    title: "Atencion",
	    text: "La operacion se ha realizado correctamente",
	    confirmButtonText: "Aceptar",
	    confirmButtonColor: "#e01d22",
	    icon: "success"
	});
}