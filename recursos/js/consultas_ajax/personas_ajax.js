consultar();

let data_table, id_eliminado, id_registrado, id_modificar, cedula_an;
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
let tabla = document.querySelector("#tabla_personas"); //La tabla
let boton_formulario = document.querySelector("#boton_formulario"); // el boton
let formulario_usar = document.querySelector(`#form_personas`); // el form
let modal = new bootstrap.Modal("#modal_personas"); // el modal
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa")); // Boton vista previa

//En caso de que se envie un formulario
function envio(operacion) {	
	if (operacion == "Editar") {
		id_modificar = boton_formulario.getAttribute("id_modificar");//obtenemos el id del registro
		modificar(id_modificar);
	}
	else if(operacion == "Registrar"){
		//sino a registrar
		registrar();
	}else{
		// esto es imposible que pase pero aja
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

// Esto es en caso de que uno quite el formulario, le devuelve los valores que tenia
document.querySelector(`#modal_personas`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";
	document.getElementById('titulo_modal').textContent = "Registrar Persona";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");
});

function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`; // DD-MM-AAAA
    }
    return fechaStr; // En caso de error, retorna original
}

//Si queremos registrar:
async function registrar() {
	// el async vuelve la funcion asincrona	
	//Creamos el formData
	datos_consulta = new FormData();
	//Creamos las variables con los datos de los inputs
	let nombre = formulario_usar.querySelector("#nombre").value,	
	apellido = formulario_usar.querySelector("#apellido").value,
	cedula = formulario_usar.querySelector("#cedula").value,
	telefono = formulario_usar.querySelector("#telefono").value,
	correo = formulario_usar.querySelector("#correo").value,
	fecha_nacimiento = formulario_usar.querySelector("#fecha_nacimiento").value,
	sexo = formulario_usar.querySelector("#sexo").value,
	sexos = formulario_usar.querySelector("#sexo").selectedOptions[0].text,
	apartamento_id = formulario_usar.querySelector("#apartamento_id").value,
	apartamento = formulario_usar.querySelector("#apartamento_id").selectedOptions[0].text,
	tipo_vinculo = formulario_usar.querySelector("#tipo_vinculo").value,
	vinculo = formulario_usar.querySelector("#tipo_vinculo").selectedOptions[0].text;

	// le pasamos los datos por el formData
	datos_consulta.append("nombre",nombre);	
	datos_consulta.append("apellido",apellido);
	datos_consulta.append("cedula",cedula);
	datos_consulta.append("telefono",telefono);
	datos_consulta.append("correo",correo);
	datos_consulta.append("fecha_nacimiento",fecha_nacimiento);
	datos_consulta.append("sexo",sexo);
	datos_consulta.append("apartamento_id",apartamento_id);
	datos_consulta.append("tipo_vinculo",tipo_vinculo);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','registrar');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta); // El await es para que espere el resultado, al ser asincrono, normalmente no lo esperaria
	// wait = esperar (english)
	modal.hide(); //Esconde el modal
	formulario_usar.reset();//Limpia el formulario

	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	id_registrado = await last_id(); //Guarda el nuevo id registrado, para darselo al evento de modificar
	
	let acciones = crearBotones(id_registrado.mensaje); //Crea botones
	
	// esta variable no hace nada, pero me dio error cuando la quite XD
	let res_data_table = await data_table.row.add([`${nombre}`,`${apellido}`,`${cedula}`,`${"Nro: " + apartamento}`,`${acciones.outerHTML}`]).draw();
	// Tiene el await para que lo espere, sino no la pone en la tabla

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');//Mensaje de que se completo la operacion
}

//Si queremos consultar
async function consultar() {
	//Creamos el formData
	datos_consulta = new FormData();

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta');

	//Llamamos a la funcion para hacer la consulta
	data = await query(datos_consulta)
	vaciar_tabla(); //Vaciamos la tabla de lo que tenia antes
	
	// Resvisamos el resultado
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	//recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
	await data.map(fila=>{
		llenarTabla(fila);
	})
	
	data_table = init_data_table(); //iniciamos el dataTable de jquery
}

// Esta funcion hace lo que dice
function vaciar_tabla() {
	let cuerpo_tabla = document.querySelector(`#tabla_personas tbody`);
	cuerpo_tabla.textContent = null;
}

// esta tambien, se ve larga, pero no es tan complicada  **********
// esta funcion crea filas para la tabla al momento de consultar
function llenarTabla(fila) {
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_personas tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	let id_campo = fila["id_persona"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let nombre_td = document.createElement("td"),
	apellido_td = document.createElement("td"),	
	cedula_td = document.createElement("td"),
	apartamento_td = document.createElement("td");
	/*telefono_td = document.createElement("td");
    correo_td = document.createElement("td");
    fecha_nacimiento_td = document.createElement("td");
    sexo_td = document.createElement("td");*/

	// le damos el contenido de la consulta
	nombre_td.textContent = fila["nombre"];
	apellido_td.textContent = fila["apellido"];
	cedula_td.textContent = fila["cedula"];
	apartamento_td.textContent = "Nro: " + fila["nro_apartamento"];
	/*telefono_td.textContent = fila["telefono"];
    correo_td.textContent = fila["correo"];
    fecha_nacimiento_td.textContent = fila["fecha_nacimiento"];
    sexo_td.textContent = fila["sexo"];*/
 
	let acciones = crearBotones(id_campo); 
	// creamos los botones de eliminar y modificar

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(nombre_td);
	fila_tabla.appendChild(apellido_td);
	fila_tabla.appendChild(cedula_td);
	fila_tabla.appendChild(apartamento_td);
	/*fila_tabla.appendChild(telefono_td);
	fila_tabla.appendChild(correo_td);
    fila_tabla.appendChild(fecha_nacimiento_td);
    fila_tabla.appendChild(sexo_td);*/
    fila_tabla.appendChild(acciones);

	fila_tabla.setAttribute("id",`fila-${id_campo}`);
	// le ponemos un id a las fila para cuando las eliminemos
	
	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);	
}

function crearBotones(id) {
	// Creamos los botones de las acciones
	let td = document.createElement("td");
	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");
	// le damos la clases de boostrap para que se vea tu sabe'

	// BOTON DE VISTA PREVIA CON EL OJITO
    let boton_vista_previa = document.createElement("button");
    let icono_ver = document.createElement("i");
    icono_ver.setAttribute("class", "bi bi-eye-fill");
    boton_vista_previa.appendChild(icono_ver);
    boton_vista_previa.setAttribute("type", "button");
    boton_vista_previa.setAttribute("class", "btn btn-primary btn-sm col-3");
    boton_vista_previa.setAttribute("title", "Detalles Persona");
    boton_vista_previa.setAttribute("value", id);
    boton_vista_previa.addEventListener("click", mostrarVistaPrevia);
    acciones.appendChild(boton_vista_previa);
	// ...

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
	boton_editar.setAttribute("data-bs-target", "#modal_personas");
	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);
	boton_editar.addEventListener("click",modificar_formulario); //Esa funcion esta mas abajo

	//Le ponemos los botones al <td><td> de las acciones
	acciones.appendChild(boton_editar);

	if (permiso_eliminar) {
		//creamos el boton de eliminar, le damos valor, y le asignamos la funcion para eliminar
		let boton_eliminar = document.createElement("button");

		let icono_eliminar = document.createElement("i");// le ponemos un icono
		icono_eliminar.setAttribute("class", "bi bi-trash");// y estilos
		boton_eliminar.appendChild(icono_eliminar);
		
		// le ponemos todos los atributos que lleva este boton
		boton_eliminar.setAttribute("type", "button");
		boton_eliminar.setAttribute("class", "btn btn-danger btn-sm eliminar col-3");
		boton_eliminar.setAttribute("tabindex", "-1"); 
		boton_eliminar.setAttribute("role", "button");
		boton_eliminar.setAttribute("aria-disabled", "true");
		// no se para que sirven la mayoria, pero bueno... boostrap

		boton_eliminar.setAttribute("title","Eliminar");
		boton_eliminar.setAttribute("value",id);// el valor del id para eliminar	

		acciones.appendChild(boton_eliminar);
	}

	td.appendChild(acciones);

	return td;
}
 
//FUNCIONALIDAD DE LA VISTA PREVIA
async function mostrarVistaPrevia(e) {
    let boton = e.target.closest("button");
    let id = boton.getAttribute("value");

    let datos_consulta = new FormData();
    datos_consulta.append("id_persona", id);
    datos_consulta.append("operacion", "consulta_especifica");

    let respuesta = await query(datos_consulta);

    let data = respuesta;

	document.getElementById("vista_nombre").textContent = data.nombre;
	document.getElementById("vista_apellido").textContent = data.apellido;
	document.getElementById("vista_cedula").textContent = data.cedula;
	document.getElementById("vista_telefono").textContent = data.telefono;
    document.getElementById("vista_correo").textContent = data.correo;
    document.getElementById("vista_fecha_nacimiento").textContent = formatearFecha(data.fecha_nacimiento);
    document.getElementById("vista_sexo").textContent = data.sexo;

	if(data.apartamento == undefined){
		document.getElementById("vista_apartamento").textContent = "Apartamento NO Registrado";
	}else{
		document.getElementById("vista_apartamento").textContent = "Nro " + data.apartamento; // Comprobar
	}

	document.getElementById("vista_vinculo").textContent = data.tipo_vinculo;	

    // Mostrar el modal como los otros
    modalVistaPrevia.show();
}

// si queremos eliminar
async function eliminar(id) {
	//Creamos el formData
	datos_consulta = new FormData()

	// Le ponemos el id al FormData
	datos_consulta.append("id_persona",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','eliminar');

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);
	
	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	id_eliminado = id; 
	// con esto indicamos que se elimino un registro
	// en caso de que lo de abajo no lo elimine

	data_table.row(`#fila-${id}`).remove().draw(); // esto es para eliminar la fila del data table

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

// Esta funcion prepara el formulario para editar el registro
async function modificar_formulario(e) {
	// primero buscamos el registro a modificar
	//Creamos el formData
	datos_consulta = new FormData();
		
	let id = e.target.value; // tomamos el id
	if (id === undefined) {
		id = e.target.parentElement.value; 
		//esto es por si seleciona el icono en vez del boton al dar click
	}
	
	// le damos el id
	datos_consulta.append("id_persona",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta_especifica');

	//Llamamos a la funcion para hacer la consulta y guardamos los datos
	data = await query(datos_consulta);

	// ahora seleccionamos los inputs
	let nombre = formulario_usar.querySelector("#nombre"),
	apellido = formulario_usar.querySelector("#apellido"),	
	cedula = formulario_usar.querySelector("#cedula"),	
	telefono = formulario_usar.querySelector("#telefono");
    correo = formulario_usar.querySelector("#correo");
    fecha_nacimiento = formulario_usar.querySelector("#fecha_nacimiento");
    sexo = formulario_usar.querySelector("#sexo");
	apartamento_id = formulario_usar.querySelector("#apartamento_id");
	tipo_vinculo = formulario_usar.querySelector("#tipo_vinculo");
 
	// le damos valor
	nombre.value = data.nombre;
	apellido.value = data.apellido;	
	cedula.value = data.cedula;
	telefono.value = data.telefono;
    correo.value = data.correo;
    fecha_nacimiento.value = data.fecha_nacimiento;
    sexo.value = data.sexo;
	apartamento_id.value = data.apartamento_id;
	tipo_vinculo.value = data.tipo_vinculo;

	// este if revisa si tiene permiso para editar, en caso de que no, quitamos el boton
	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
		//si no los tiene apaga el boton.
	}

	// aqui cambiamos los datos del boton para registrar, para saber que ahora se va es a modificar un registro
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_persona);
	boton_formulario.textContent = "Modificar";
	document.getElementById('titulo_modal').textContent = "Modificar Persona";

	id_modificar = id;
	cedula_an = cedula.value;
	//guardamos el orginal del correo, para que no choquen con las validaciones
}

//si queremos modificar
async function modificar(id) {	
	//Creamos el formData
	let datos_consulta = new FormData();

	//Guardamos los datos del formulario
	let nombre = formulario_usar.querySelector("#nombre").value;
	let apellido = formulario_usar.querySelector("#apellido").value;
	let cedula = formulario_usar.querySelector("#cedula").value;
	let telefono = formulario_usar.querySelector("#telefono").value;
	let correo = formulario_usar.querySelector("#correo").value;
	let fecha_nacimiento = formulario_usar.querySelector("#fecha_nacimiento").value;
	let sexo = formulario_usar.querySelector("#sexo").value;
	let sexos = formulario_usar.querySelector("#sexo").selectedOptions[0].text;
	let apartamento_id = formulario_usar.querySelector("#apartamento_id").value;
	let apartamento = formulario_usar.querySelector("#apartamento_id").selectedOptions[0].text;
	let tipo_vinculo = formulario_usar.querySelector("#tipo_vinculo").value;
	let vinculo = formulario_usar.querySelector("#tipo_vinculo").selectedOptions[0].text;

	// Le ponemos los datos del formulario
	datos_consulta.append("id_persona",id);

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);	
	datos_consulta.append("cedula",cedula);
	datos_consulta.append("telefono",telefono);
	datos_consulta.append("correo",correo);
    datos_consulta.append("fecha_nacimiento",fecha_nacimiento);
    datos_consulta.append("sexo",sexo);
	datos_consulta.append("apartamento_id",apartamento_id);
	datos_consulta.append("tipo_vinculo",tipo_vinculo);
	// ...

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','modificar');

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);

	formulario_usar.reset(); //Limpiamos el formulario
 	modal.hide(); // escondemos el modal

 	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	// al terminar le damos al boton su valores originales

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";

	document.getElementById('titulo_modal').textContent = "Registrar Persona";

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');//Mensaje de que se completo la operacion

	// esto de abajo es para editar la fila que se modifico en el data table
	let acciones = crearBotones(id); // creamos otro botones (no se que tan necesario sea esto)

	data_table.row(`#fila-${id}`).data([`${nombre}`,`${apellido}`,`${cedula}`,`${"Nro: " + apartamento}`,`${acciones.outerHTML}`])
	data_table.draw(); // esta funcion refresca la tabla, por si le da sed

	// se le vuelve a poner el evento al boton
	let fila = document.querySelector(`#fila-${id}`);
	if (fila) {
		fila.querySelector(`[value='${id}']`).addEventListener("click",modificar_formulario);
	}	
}

// esta funcion obtiene el ultimo id registrado en la base de datos
async function last_id() {
	datos_consulta = new FormData()
	datos_consulta.append('operacion','ultimo_id');
	let res = await query(datos_consulta);
	return res;
}

// Aqui se hace la peticion AJAX
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

// esto solo es para decir que se completo o fallo una operacion
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

// esta funcion es para incializar el data table
function init_data_table() {
	return new DataTable("#tabla_personas",{
            destroy: true,
            responsive: true,
            "scrollX": true,
            "pageLength": 10,
            "aaSorting": [],
            language: {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "infoPostFix": "",
                "search": "Buscar:",
                "url": "",
                "infoThousands": ",",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "<i class='bi bi-caret-right'></i>",
                    "previous": "<i class='bi bi-caret-left'></i>"
                },
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
            }
    })
    // si lees esto tienes que saber que ahora odio estos data table, muerte a jquery...
}
 
// un observador que detecte cuando cambie la tabla, si detecta cambio ejecuta la esa funcion
// yo la puse porque jquery cuando hace la paginacion en la tabla, borra los elementos, 
// entonces se pierden los eventos asignados, y cuando vuelven a aparecer, no los tienen.
// Esto es para reasinarle estos eventos (para eliminar, modificar, etc)
const observer = new MutationObserver(() => {
	reasignarEventos();
});

observer.observe(tabla, {childList:true});

// esta funcion pone los eventos de eliminar y modificar
function reasignarEventos() {
	console.log("me ejecuto");
	if (id_eliminado){ //Si hay un eliminado que no se ha quitado de la tabla
		let existe_fila = tabla.querySelector(`#fila-${id_eliminado}`)
		if (existe_fila) {
			data_table.row(`#fila-${id_eliminado}`).remove().draw();
			id_eliminado = null;	
		}
	//Esto es porque si la tabla esta paginada, como que no encuentra cual borrar hasta que esta en la pagina que la contiene
	}

	// Se asigna el evento eliminar para los botones, esta aqui porque pasa algo parecido a lo de arriba
	$(".eliminar").on("click",function(e){
		id = e.target.value;
		if (id == undefined) {	
			id = e.target.parentElement.value;
		}
		Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea eliminar esta persona?",
			showCancelButton: true,
			confirmButtonText: "Eliminar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
				if (resultado.isConfirmed) {
					eliminar(id);				
				}
			});
	});

	document.querySelectorAll("button[title='Detalles Persona']").forEach(btn => {
        btn.removeEventListener("click", mostrarVistaPrevia);
        btn.addEventListener("click", mostrarVistaPrevia);
    });

	// El boton no queria funcionar asi que toco hacer lo mismo que el de vista previa (detalles)
	document.querySelectorAll("button[title='Editar']").forEach(btn => {
        btn.removeEventListener("click", modificar_formulario);
        btn.addEventListener("click", modificar_formulario);
    });

	if (id_registrado) { // en caso de que se haya registrado y no se haya añadido a la tabla
		let boton_modificar = tabla.querySelector(`[value='${id_registrado.mensaje}']`); 
		// captura el boton de editar, sino lo encuentra es que no esta en su pagina, y no tiene caso ponerle evento
		if (boton_modificar) {
			// si lo encuentra le pone el evento de modificar
			boton_modificar.addEventListener("click",modificar_formulario);
			boton_modificar.parentElement.parentElement.parentElement.setAttribute("id",`fila-${id_registrado.mensaje}`);
			id_registrado = null;
		}
	}
}