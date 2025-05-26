consultar(); // Para llenar la tabla al cargar o entrar a la pagina
api();
// Todo lo que esta aqui son variables que se pueden emplear a lo largo del codigo
let data_table, id_eliminado, id_registrado,id_modificar, referencia_an;
// Para guardar los permisos del usuario
/*
	- document: es un objeto que representa la página web cargada en el navegador 
	y sirve como punto de entrada para interactuar con el contenido de la página.

	- querySelector(): método que te permite seleccionar elementos del DOM 
	(Document Object Model) utilizando selectores CSS, ejemplos: .clase-especifica, 
	#id-especifico, p.

	- value: es el dato que una variable contiene o el valor que se asigna a una 
	propiedad o atributo.

	- punto (.): sirve para acceder a las propiedades y métodos de un objeto.
*/
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
// ...

let tabla = document.querySelector("#tabla_pagos"); //La tabla
let boton_formulario = document.querySelector("#boton_formulario"); // el boton

/* 
	El modal se define aqui

	- bootstrap.Modal(): es basicamente una clase de Bootstrap 5 para controlar
	modales.
	
	- new: al usar new estamos creando un objeto modal.

	NOTA: esta forma de definir es incorrecta.
*/ 
let modal = new bootstrap.Modal("#modal_pagos");
let formulario_usar = document.querySelector(`#form_pagos`); // el form

// Forma correcta de definir el modal
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa"));

//En caso de que se envie un formulario
function envio(operacion) {	
	if (operacion == "Editar") {
		/*
			- getAttribute: es un método que permite obtener el valor de un atributo 
			específico de un elemento HTML.

			y "envio", "modificar", "registrar", "mensajes" son funciones definidas 
			por nosotroso mismos para que no te confundas.
		*/
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
document.querySelector(`#modal_pagos`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";
	document.getElementById('titulo_modal').textContent = "Registrar Pago";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");

	api();

	document.querySelector("#nombre_imagen_cargada").textContent = "";
    document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
    document.querySelector("#boton_eliminar_imagen").removeAttribute("data-nombre");

    const inputOculto = formulario_usar.querySelector("input[name='eliminar_imagen']");
    if (inputOculto) inputOculto.remove();
});

async function api() {
    try {
        let response = await fetch('https://pydolarve.org/api/v2/dollar?page=alcambio');
        let obj_dolar = await response.json();
        console.log(obj_dolar);

        let tasaParalelo = obj_dolar?.monitors?.enparalelovzla?.price;
        if (tasaParalelo) {
            document.getElementById('tasa_dolar').value = tasaParalelo;
        } else {
            document.getElementById('tasa_dolar').value = "No disponible";
        }
    } catch (error) {
        console.error('Error al obtener la tasa del dólar paralelo:', error);
        document.getElementById('tasa_dolar').value = "Error";
    }
}

async function bcv() {
    try {
        let response = await fetch('https://pydolarve.org/api/v2/dollar?page=alcambio');
        let obj_dolar = await response.json();
        console.log(obj_dolar);

        let tasaBCV = obj_dolar?.monitors?.bcv?.price;
        if (tasaBCV) {
            return tasaBCV;
        } else {
            return "No disponible";
        }
    } catch (error) {
        console.error('Error al obtener la tasa del dólar paralelo:', error);
        return "Error";
    }
}

//Si queremos registrar:
async function registrar() {
	// el async vuelve la funcion asincrona	
	//Creamos el formData
	datos_consulta = new FormData();
	//Creamos las variables con los datos de los inputs
	let fecha = formulario_usar.querySelector("#fecha").value,
	monto = formulario_usar.querySelector("#monto").value,
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar").value; 
	estado = formulario_usar.querySelector("#estado").value,
    metodo_pago = formulario_usar.querySelector("#metodo_pago").value;
	banco_id = formulario_usar.querySelector("#banco_id").value;
	nombre_banco = formulario_usar.querySelector("#banco_id").selectedOptions[0].textContent;
	referencia = formulario_usar.querySelector("#referencia").value;
	imagen = formulario_usar.querySelector("#imagen").files[0];
	observacion = formulario_usar.querySelector("#observacion").value;
	mensualidad_id = formulario_usar.querySelector("#mensualidad_id").value;

	// le pasamos los datos por el formData
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("estado",estado);
	datos_consulta.append("metodo_pago",metodo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen",imagen);
	datos_consulta.append("observacion",observacion);
	datos_consulta.append("mensualidad_id",mensualidad_id);

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
	let res_data_table = await data_table.row.add([`${fecha}`,`${monto}`,`${tasa_dolar}`,`${estado}`,`${metodo_pago}`,`${nombre_banco}`,`${referencia}`,`${imagen}`,`${observacion}`,`${mensualidad_id}`,`${acciones.outerHTML}`]).draw();
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
	let cuerpo_tabla = document.querySelector(`#tabla_pagos tbody`);
	cuerpo_tabla.textContent = null;
}

// esta tambien, se ve larga, pero no es tan complicada  **********
// esta funcion crea filas para la tabla al momento de consultar
function llenarTabla(fila) {
	console.log(fila);
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_pagos tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	let id_campo = fila["id_pago"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let fecha_td = document.createElement("td"),	
	monto_td = document.createElement("td"),
	tasa_dolar_td = document.createElement("td");
    estado_td = document.createElement("td");
	metodo_pago_td = document.createElement("td");
	banco_id_td = document.createElement("td");
	referencia_td = document.createElement("td");
	imagen_td = document.createElement("td");
	observacion_td = document.createElement("td");
	mes_anio_td = document.createElement("td");

	// le damos el contenido de la consulta
	// Para arreglar la fecha
	let fecha = new Date(fila["fecha"]);
	let dia = String(fecha.getDate()).padStart(2, '0');
	let mes = String(fecha.getMonth() + 1).padStart(2, '0');
	let anio = String(fecha.getFullYear()); // Para solo 2 dígitos = String(fecha.getFullYear()).slice(-2);
	fecha_td.textContent = `${dia}/${mes}/${anio}`;
	// ...
	// Para mostrar el calculo entre el monto y la tasa
	monto_td.textContent = fila["monto"] + " $ " + "(" + (parseFloat(fila["monto"]) * parseFloat(fila["tasa_dolar"])).toFixed(2) + " Bs)";
	// ...
	tasa_dolar_td.textContent = fila["tasa_dolar"] + " Bs";
	// Para que los estados tengan colores
	estado_td.textContent = fila["estado"];

	if (fila["estado"] === "COMPROBADO") {
		estado_td.style.color = "green";
		estado_td.style.fontWeight = "bold";
	} else if (fila["estado"] === "PENDIENTE") {
		estado_td.style.color = "orange";
		estado_td.style.fontWeight = "bold";
	}
	// ...
    metodo_pago_td.textContent = fila["metodo_pago"];
	banco_id_td.textContent = fila["nombre_banco"];
	referencia_td.textContent = fila["referencia"];
	imagen_td.textContent = fila["imagen"];
	observacion_td.textContent = fila["observacion"];
	mes_anio_td.textContent = fila["mes_anio"];

	let acciones = crearBotones(id_campo); 
	// creamos los botones de eliminar y modificar

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(fecha_td);
	fila_tabla.appendChild(monto_td);
	fila_tabla.appendChild(tasa_dolar_td);
	fila_tabla.appendChild(estado_td);
	fila_tabla.appendChild(metodo_pago_td);
	fila_tabla.appendChild(banco_id_td);
	fila_tabla.appendChild(referencia_td);
	fila_tabla.appendChild(imagen_td);
	fila_tabla.appendChild(observacion_td);
	fila_tabla.appendChild(mes_anio_td);
    fila_tabla.appendChild(acciones);

	fila_tabla.setAttribute("id",`fila-${id_campo}`);
	// le ponemos un id a las fila para cuando las eliminemos
	
	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);	

	// Ajusta mas o menos los contenidos
	fecha_td.style.whiteSpace = "nowrap";
	fecha_td.style.overflow = "hidden";
	fecha_td.style.textOverflow = "ellipsis";
	// ...
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
    boton_vista_previa.setAttribute("title", "Vista previa");
    boton_vista_previa.setAttribute("value", id);
    boton_vista_previa.addEventListener("click", mostrarVistaPrevia);
    acciones.appendChild(boton_vista_previa);

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
	boton_editar.setAttribute("data-bs-target", "#modal_pagos");
	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);
	boton_editar.addEventListener("click",modificar_formulario)//Esa funcion esta mas abajo

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

// si queremos eliminar
async function eliminar(id) {
	//Creamos el formData
	datos_consulta = new FormData()

	// Le ponemos el id al FormData
	datos_consulta.append("id_pago",id);

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
	datos_consulta.append("id_pago",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta_especifica');

	//Llamamos a la funcion para hacer la consulta y guardamos los datos
	data = await query(datos_consulta);	
	
	// ahora seleccionamos los inputs
	let fecha = formulario_usar.querySelector("#fecha"),
	monto = formulario_usar.querySelector("#monto"),
	nro_apartamento = formulario_usar.querySelector("#nro_apartamento");
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar"),	
	estado = formulario_usar.querySelector("#estado");
    metodo_pago = formulario_usar.querySelector("#metodo_pago");
	banco_id = formulario_usar.querySelector("#banco_id");
	referencia = formulario_usar.querySelector("#referencia");	
	observacion = formulario_usar.querySelector("#observacion");
 
	// le damos valor
	fecha.value = data.fecha;
	monto.value = data.monto;
	nro_apartamento.value = data.nro_apartamento;
	tasa_dolar.value = data.tasa_dolar;
	estado.value = data.estado;
    metodo_pago.value = data.metodo_pago;
	banco_id.value = data.banco_id;
	referencia.value = data.referencia;
	observacion.value = data.observacion;
	// ...
 
	// Mostrar nombre de imagen, esos id estan en el formulario
    const nombreImagen = document.querySelector("#nombre_imagen_cargada");
    const botonEliminarImagen = document.querySelector("#boton_eliminar_imagen");

    if (data.imagen && data.imagen !== "") {
        let nombre_archivo = data.imagen.split("/").pop();
        nombreImagen.textContent = `Imagen cargada: ${nombre_archivo}`;
        botonEliminarImagen.classList.remove("d-none");
        botonEliminarImagen.setAttribute("data-nombre", nombre_archivo);
    } else {
        nombreImagen.textContent = "No hay imagen cargada.";
        botonEliminarImagen.classList.add("d-none");
        botonEliminarImagen.removeAttribute("data-nombre");
    }

	// este if revisa si tiene permiso para editar, en caso de que no, quitamos el boton
	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
		//si no los tiene apaga el boton.
	}

	// aqui cambiamos los datos del boton para registrar, para saber que ahora se va es a modificar un registro
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_pago);
	boton_formulario.textContent = "Modificar";
	document.getElementById('titulo_modal').textContent = "Modificar Pago";

	id_modificar = id;
	referencia_an = referencia.value;
	//guardamos el orginal del correo, para que no choquen con las validaciones
}

//FUNCIONALIDAD DE LA VISTA PREVIA
async function mostrarVistaPrevia(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_pago", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta;

	/*
    document.getElementById("vista_titulo").textContent = data.titulo;
    document.getElementById("vista_descripcion").textContent = data.descripcion;
    document.getElementById("vista_fecha").textContent = formatearFecha(data.fecha);
    document.getElementById("vista_prioridad").innerHTML = obtenerPrioridadTexto(data.prioridad);
    document.getElementById("vista_autor").textContent = data.nombre_usuario;
	*/

    // Resetear mensaje de error por si estaba visible
    document.getElementById("vista_imagen").style.display = "block";
    document.getElementById("mensaje_error_imagen").classList.add("d-none");

	console.log("soy marico y no agarro uwu");
	console.log("Respuesta obtenida:", respuesta);

    const imagen = (data.imagen && data.imagen !== "")
        ? `recursos/img/${data.imagen}`
        : "";

    document.getElementById("vista_imagen").setAttribute("src", imagen);

    // Mostrar el modal como los otros
    modalVistaPrevia.show();
}

//si queremos modificar
async function modificar(id) {	
	//Creamos el formData
	let datos_consulta = new FormData();

	//Guardamos los datos del formulario
	let fecha = formulario_usar.querySelector("#fecha").value,
	monto = formulario_usar.querySelector("#monto").value,
	nro_apartamento = formulario_usar.querySelector("#nro_apartamento").value,
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar").value, 	
	estado = formulario_usar.querySelector("#estado").value,
	metodo_pago = formulario_usar.querySelector("#metodo_pago").value;
	banco_id = formulario_usar.querySelector("#banco_id").value;
	nombre_banco = formulario_usar.querySelector("#banco_id").selectedOptions[0].textContent;
	referencia = formulario_usar.querySelector("#referencia").value;
	imagen = formulario_usar.querySelector("#imagen").files[0];
	observacion	= formulario_usar.querySelector("#observacion").value;
	mensualidad_id = formulario_usar.querySelector("#mensualidad_id").value;

	// Le ponemos los datos del formulario
	datos_consulta.append("id_pago",id);

	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("nro_apartamento",nro_apartamento);	
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("estado",estado);
	datos_consulta.append("metodo_pago",metodo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen",imagen);
	datos_consulta.append("observacion",observacion);
	datos_consulta.append("mensualidad_id",mensualidad_id);
	// ...

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','modificar');

	// Solo si hay una nueva imagen seleccionada
    if (imagen !== undefined) {
        datos_consulta.append("imagen", imagen);
    }

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
 
	document.getElementById('titulo_modal').textContent = "Registrar Pago";

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');//Mensaje de que se completo la operacion

	// Obtener ruta de imagen actualizada o previa
    const rutaImagen = respuesta.imagen_url || "recursos/img/default.jpg";

	// esto de abajo es para editar la fila que se modifico en el data table
	let acciones = crearBotones(id); // creamos otro botones (no se que tan necesario sea esto)

	data_table.row(`#fila-${id}`).data([`${fecha}`,`${monto}`,`${tasa_dolar}`,`${estado}`,`${metodo_pago}`,`${nombre_banco}`,`${referencia}`,`${imagen}`,`${observacion}`,`${mensualidad_id}``${acciones.outerHTML}`])
	data_table.draw(); // esta funcion refresca la tabla, por si le da sed

	// se le vuelve a poner el evento al boton
	let fila = document.querySelector(`#fila-${id}`);
	if (fila) {
		fila.querySelector(`[value='${id}']`).addEventListener("click",modificar_formulario);
	}	
}

// BOTON PARA ELIMINAR LA IMAGEN EN EL FORMULARIO DE EDITAR
document.querySelector("#boton_eliminar_imagen").addEventListener("click", function () {
    Swal.fire({
        title: "¿Eliminar imagen?",
        text: "La imagen cargada será eliminada de esta publicación.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            const hiddenEliminar = document.createElement("input");
            hiddenEliminar.type = "hidden";
            hiddenEliminar.name = "eliminar_imagen";
            hiddenEliminar.value = "1";
            formulario_usar.appendChild(hiddenEliminar);

            // llAMA A LOS INPUTS QUE ESTAN EN EL MODAL
            document.querySelector("#nombre_imagen_cargada").textContent = "Imagen eliminada.";
            document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
        }
    });
});

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
	return new DataTable("#tabla_pagos",{
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
			text: "¿Está seguro que desea eliminar este pago?",
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
	document.querySelectorAll("button[title='Vista previa']").forEach(btn => {
        btn.removeEventListener("click", mostrarVistaPrevia);
        btn.addEventListener("click", mostrarVistaPrevia);
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