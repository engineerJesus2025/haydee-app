consultar(); // Para llenar la tabla al cargar o entrar a la pagina
api();
// Todo lo que esta aqui son variables que se pueden emplear a lo largo del codigo
let data_table, id_eliminado, id_registrado,id_modificar, referencia_an;
let id_detalle_pago = null, id_banco_transaccion = null;
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

document.querySelector("#apartamento_id").addEventListener("change", async function () {
	let id_apartamento = this.value;

	if (!id_apartamento) return;

	let datos_consulta = new FormData();
	datos_consulta.append("operacion", "consultar_mensualidades");
	datos_consulta.append("apartamento_id", id_apartamento);

	let respuesta = await query(datos_consulta);

	console.log(respuesta);

	if (respuesta.estatus === false) {
		mensajes('error', 4000, 'Error', respuesta.mensaje);
		return;
	}

	let select_mensualidades = document.querySelector("#mensualidad_id");
	select_mensualidades.innerHTML = "<option value=''>Seleccione una mensualidad</option>";

	const meses = [
		"", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
		"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
	];

	respuesta.forEach(m => {
		let opcion = document.createElement("option");
		let nombre_mes = meses[parseInt(m.mes)]; // Asegúrate de que sea número
		opcion.value = m.id_mensualidad;
		opcion.textContent = `${nombre_mes}/${m.anio} - ${m.monto}$`;
		opcion.setAttribute("data-monto", m.monto);
		select_mensualidades.appendChild(opcion);
	});
});

document.querySelector("#mensualidad_id").addEventListener("change", function () {
	let seleccion = this.selectedOptions[0];
	let monto = seleccion.getAttribute("data-monto");

	console.log("Monto seleccionado:", monto);

	// Si quieres guardarlo para el backend
	document.querySelector("#monto_mensualidad").value = monto;
});

async function api() {
    try {
        let response = await fetch('https://pydolarve.org/api/v2/dollar?page=alcambio');
        let obj_dolar = await response.json();
        console.log(obj_dolar);

        let tasaBCV = obj_dolar?.monitors?.bcv?.price;
        if (tasaBCV) {
            document.getElementById('tasa_dolar').value = tasaBCV;
			document.getElementById('tasa_dolar_detalles').value = tasaBCV;
			//calcularBolivares();
        } else {
            document.getElementById('tasa_dolar').value = "No disponible";
			document.getElementById('tasa_dolar_detalles').value = "No disponible";
        }
    } catch (error) {
        console.error('Error al obtener la tasa del dólar bcv:', error);
        document.getElementById('tasa_dolar').value = "Error";
		document.getElementById('tasa_dolar_detalles').value = "Error";
    }
}

// Calcular dolares o bolivares en vivo y directo
function calcularBolivares() {
    let dolares = parseFloat(document.getElementById("monto_dolares").value);
    let tasa = parseFloat(document.getElementById("tasa_dolar").value);

    if (!isNaN(dolares) && !isNaN(tasa)) {
        let bolivares = dolares * tasa;
        document.getElementById("monto_bolivares").value = bolivares.toFixed(2);
    } else {
        document.getElementById("monto_bolivares").value = "";
    }
}

function calcularDolares() {
    let bolivares = parseFloat(document.getElementById("monto_bolivares").value);
    let tasa = parseFloat(document.getElementById("tasa_dolar").value);

    if (!isNaN(bolivares) && !isNaN(tasa) && tasa !== 0) {
        let dolares = bolivares / tasa;
        document.getElementById("monto_dolares").value = dolares.toFixed(2);
    } else {
        document.getElementById("monto_dolares").value = "";
    }
}

//document.getElementById("monto_dolares").addEventListener("input", calcularBolivares);
//document.getElementById("monto_bolivares").addEventListener("input", calcularDolares);
// ...

document.getElementById("tipo_pago").addEventListener("change", function () {
    const metodo = this.value;

    // 1. Ocultamos los campos condicionales
    document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => {
        campo.classList.add("d-none");
    });

    // 2. Limpiar los campos de texto y selects
    document.querySelectorAll(".campo-monto input, .campos-bancarios input, .campos-bancarios select").forEach(campo => {
        campo.value = "";
    });

    // 3. Limpiar el input file, nombre de imagen y botón eliminar
    const inputImagen = document.getElementById("imagen");
    const nombreImagen = document.getElementById("nombre_imagen_cargada");
    const botonEliminar = document.getElementById("boton_eliminar_imagen");

    if (inputImagen) inputImagen.value = "";
    if (nombreImagen) nombreImagen.textContent = "";
    if (botonEliminar) botonEliminar.classList.add("d-none");

    // 4. Mostrar según método
    if (metodo === "Efectivo") {
        document.querySelectorAll(".campo-monto").forEach(campo => campo.classList.remove("d-none"));
    } else if (metodo === "Transferencia" || metodo === "Pago Movil") {
        document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => campo.classList.remove("d-none"));
    }
});

// 5. Al cargar: limpiar tipo_pago y ocultar campos
window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("tipo_pago").value = "";
    document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => {
        campo.classList.add("d-none");
    });

    // Limpiar imagen por si acaso
    const inputImagen = document.getElementById("imagen");
    const nombreImagen = document.getElementById("nombre_imagen_cargada");
    const botonEliminar = document.getElementById("boton_eliminar_imagen");

    if (inputImagen) inputImagen.value = "";
    if (nombreImagen) nombreImagen.textContent = "";
    if (botonEliminar) botonEliminar.classList.add("d-none");
});

function obtenerImagen(imagen) {
    let texto = "";
    let color = "";

    switch (imagen) {
        case "(Sin imagen)":
        case 1:
            texto = "Sin Imagen";
            color = "warning";
            break;
		case "(Imagen)":
        case 2:
            texto = "Imagen";
            color = "success";
            break;
        default:
            texto = "Sin Imagen";
            color = "warning";
    }

    return `<span class="badge bg-${color}">${texto}</span>`;
}

function obtenerEstado(estado) {
    let texto = "";
    let color = "";

    switch (estado) {
        case "COMPROBADO":
        case 1:
            texto = "Comprobado";
            color = "success";
            break;
        case "PENDIENTE":
        case 2:
            texto = "Pendiente";
            color = "warning";
            break;
		case "No procesado":
		case 3:
            texto = "No procesado";
            color = "warning";
            break;
		case "Procesado":
		case 4:
            texto = "Procesado";
            color = "success";
            break;
		case "No hay banco registrado":
		case 5:
            texto = "No hay banco registrado";
            color = "secondary";
            break;
		case "No hay referencia registrada":
		case 6:
            texto = "No hay referencia registrada";
            color = "secondary";
            break;
        default:
            texto = "Desconocida";
            color = "secondary";
    }

    return `<span class="badge bg-${color}">${texto}</span>`;
}

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
	let monto = formulario_usar.querySelector("#monto").value,
	estado = formulario_usar.querySelector("#estado").value,
	observacion = formulario_usar.querySelector("#observacion").value;
	fecha = formulario_usar.querySelector("#fecha").value;
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar").value;
	tipo_pago = formulario_usar.querySelector("#tipo_pago").value;
	banco_id = formulario_usar.querySelector("#banco_id").value;
	referencia = formulario_usar.querySelector("#referencia").value;
	imagen = formulario_usar.querySelector("#imagen").files[0];
	apartamento_id = formulario_usar.querySelector("#apartamento_id").value;
	monto_mensualidad = formulario_usar.querySelector("#monto_mensualidad").value;
	mensualidad_id = formulario_usar.querySelector("#mensualidad_id").value;

	// le pasamos los datos por el formData
	datos_consulta.append("monto",monto);
	datos_consulta.append("estado",estado);
	datos_consulta.append("observacion",observacion);
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("tipo_pago",tipo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen",imagen);
	datos_consulta.append("apartamento_id",apartamento_id);
	datos_consulta.append("monto_mensualidad",monto_mensualidad);
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
	let fila = {
		id_pago: id_registrado.mensaje,
		fecha, // importante para los botones
		monto_mensualidad,
		estado,
		observacion,
	};

	let datos = fila;

	console.log("registro de datos",datos);

	await data_table.row.add([
		formatearFecha(datos.fecha),
		datos.monto_mensualidad + " $",
		obtenerEstado(datos.estado),
		datos.observacion,
		acciones.outerHTML
	]).draw();
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
	console.log("Se llena la tabla: ",fila);
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_pagos tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	let id_campo = fila["id_pago"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let fecha_td = document.createElement("td"),
	monto_td = document.createElement("td"),
    estado_td = document.createElement("td");
	observacion_td = document.createElement("td");

	// le damos el contenido de la consulta
	let datos = fila;

	fecha_td.textContent = formatearFecha(datos.primera_fecha_detalle);
	monto_td.textContent = datos.monto + " $";
	estado_td.innerHTML = obtenerEstado(datos.estado);
	observacion_td.textContent = datos.observacion;

	let acciones = crearBotones(id_campo); 
	// creamos los botones de eliminar y modificar

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(fecha_td);
	fila_tabla.appendChild(monto_td);
	fila_tabla.appendChild(estado_td);
	fila_tabla.appendChild(observacion_td);
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

	// Para los pdf
	/*let boton_pdf = document.createElement("button");
	let icono_pdf = document.createElement("i");
	icono_pdf.setAttribute("class", "bi bi-file-pdf-fill");
	boton_pdf.appendChild(icono_pdf);
	boton_pdf.setAttribute("type", "button");
	boton_pdf.setAttribute("class", "btn btn-secondary btn-sm col-2 me-1");
	boton_pdf.setAttribute("title", "Generar PDF");
	boton_pdf.setAttribute("value", id);
	boton_pdf.addEventListener("click", generarReciboPDF); // Cambia a la función que uses para PDF
	acciones.appendChild(boton_pdf);*/

	// BOTON DE VISTA PREVIA CON EL OJITO
    let boton_vista_previa = document.createElement("button");
    let icono_ver = document.createElement("i");
    icono_ver.setAttribute("class", "bi bi-eye-fill");
    boton_vista_previa.appendChild(icono_ver);
    boton_vista_previa.setAttribute("type", "button");
    boton_vista_previa.setAttribute("class", "btn btn-primary btn-sm col-2 me-1");
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
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-2 me-1");
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
		boton_eliminar.setAttribute("class", "btn btn-danger btn-sm col-2 me-1");
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

	console.log("Modificar formulario:",data);
	
	// ahora seleccionamos los inputs
	let monto = formulario_usar.querySelector("#monto"),	
	estado = formulario_usar.querySelector("#estado");	
	observacion = formulario_usar.querySelector("#observacion");
	apartamento_id = formulario_usar.querySelector("#apartamento_id");
	mensualidad_id = formulario_usar.querySelector("#mensualidad_id");
	monto_mensualidad = formulario_usar.querySelector("#monto_mensualidad");
	fecha = formulario_usar.querySelector("#fecha");
	tipo_pago = formulario_usar.querySelector("#tipo_pago");
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar");
	referencia = formulario_usar.querySelector("#referencia");
	banco_id = formulario_usar.querySelector("#banco_id");

	mostrarCamposPorTipo(data.tipo_pago, formulario_usar);

	// le damos valor
	monto.value = data.monto;
	estado.value = data.estado;
	observacion.value = data.observacion;
	apartamento_id.value = data.apartamento_id;
	fecha.value = data.fecha;
	tipo_pago.value = data.tipo_pago;
	tasa_dolar.value = data.tasa_dolar;
	referencia.value = data.referencia;
	banco_id.value = data.banco_id;
	monto_mensualidad.value = data.monto_mensualidad;

	// Variables globales para poder modificar después
	id_detalle_pago = data.id_detalle_pago;
	id_banco_transaccion = data.id_banco_transaccion;

	// Mensualidad
	let mensualidades_respuesta = new FormData();
	mensualidades_respuesta.append("operacion", "consultar_mensualidades");
	mensualidades_respuesta.append("apartamento_id", data.apartamento_id);

	let mensualidades = await query(mensualidades_respuesta);

	// Limpiar mensualidades
	mensualidad_id.innerHTML = "<option value=''>Seleccione una mensualidad</option>";

	const meses = [
		"", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
		"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
	];

	mensualidades.forEach(m => {
		let opcion = document.createElement("option");
		let nombre_mes = meses[parseInt(m.mes)];
		opcion.value = m.id_mensualidad;
		opcion.text = `${nombre_mes}/${m.anio} - ${m.monto}$`;
		opcion.setAttribute("data-monto", m.monto);

		if (m.id_mensualidad == data.mensualidad_id) {
			opcion.selected = true;
			monto_mensualidad.value = m.monto;
		}

		mensualidad_id.appendChild(opcion);
	});
 
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

function mostrarCamposPorTipo(tipo_pago, formulario) {
    const camposBancarios = formulario.querySelectorAll(".campos-bancarios");
    const campoMonto = formulario.querySelector(".campo-monto");

    if (campoMonto) campoMonto.classList.remove("d-none");

    if (tipo_pago === "Efectivo") {
        camposBancarios.forEach(campo => campo.classList.add("d-none"));
    } else {
        camposBancarios.forEach(campo => campo.classList.remove("d-none"));
    }
}

let pago_actual = {
	id: null,
	mensualidad_id: null,
	monto_mensualidad: null,
	apartamento_id: null,
	nro_apartamento: null
};

//FUNCIONALIDAD DE LA VISTA PREVIA
async function mostrarVistaPrevia(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_pago", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta;

	pago_actual.id = data.id_pago;
	pago_actual.mensualidad_id = data.mensualidad_id;
	pago_actual.monto_mensualidad = data.monto_mensualidad;
	pago_actual.apartamento_id = data.apartamento_id;
	pago_actual.nro_apartamento = data.nro_apartamento;

	document.getElementById("vista_fecha").textContent = formatearFecha(data.fecha);
	document.getElementById("vista_monto_mensualidad").textContent = data.monto_mensualidad + " $";
	document.getElementById("vista_estado").innerHTML = obtenerEstado(data.estado);
	document.getElementById("vista_apartamento").textContent = data.nro_apartamento;
	document.getElementById("vista_observacion").textContent = data.observacion;

	document.getElementById("apartamento_id_detalles").value = pago_actual.apartamento_id;
	await cargarMensualidades(pago_actual.apartamento_id); // Llenamos el select
	document.getElementById("mensualidad_id_detalles").value = pago_actual.mensualidad_id;
	document.getElementById("monto_mensualidad_detalles").value = pago_actual.monto_mensualidad;

	console.log("Mostrar vista previa TABLA PRINCIPAL");
	console.log("Respuesta obtenida:", respuesta);

	await consultar_detalles(data.id_pago);

    // Mostrar el modal como los otros
    modalVistaPrevia.show();

	asignarEventoRegistrar();
}

function asignarEventoRegistrar() {
    const btnRegistrar = document.querySelector('#modal_vista_previa .btn-primary'); // ajusta selector si tu botón tiene otra clase o id

    if (btnRegistrar) {
        btnRegistrar.removeEventListener('click', abrirModalRegistrar);
        btnRegistrar.addEventListener('click', abrirModalRegistrar);
    }
}

function abrirModalRegistrar() {
    const modalVistaPreviaEl = document.getElementById('modal_vista_previa');

    function abrirDetalles() {
        modal_detalles.show();
        modalVistaPreviaEl.removeEventListener('hidden.bs.modal', abrirDetalles);
    }

    // Primero elimina para evitar duplicados
    modalVistaPreviaEl.removeEventListener('hidden.bs.modal', abrirDetalles);
    // Luego añade el listener
    modalVistaPreviaEl.addEventListener('hidden.bs.modal', abrirDetalles);

    // Finalmente cierra la vista previa
    modalVistaPrevia.hide();
}

document.getElementById('modal_detalles_pagos').addEventListener('hidden.bs.modal', () => {
    modalVistaPrevia.show();
});

//si queremos modificar
async function modificar(id) {	
	//Creamos el formData
	let datos_consulta = new FormData();

	//Guardamos los datos del formulario
	let monto = formulario_usar.querySelector("#monto").value,
	estado = formulario_usar.querySelector("#estado").value,
	observacion = formulario_usar.querySelector("#observacion").value;
	fecha = formulario_usar.querySelector("#fecha").value;
	tasa_dolar = formulario_usar.querySelector("#tasa_dolar").value;
	tipo_pago = formulario_usar.querySelector("#tipo_pago").value;
	banco_id = formulario_usar.querySelector("#banco_id").value;
	referencia = formulario_usar.querySelector("#referencia").value;
	imagen = formulario_usar.querySelector("#imagen").files[0];
	apartamento_id = formulario_usar.querySelector("#apartamento_id").value;
	monto_mensualidad = formulario_usar.querySelector("#monto_mensualidad").value;
	mensualidad_id = formulario_usar.querySelector("#mensualidad_id").value;

	// Le ponemos los datos del formulario
	datos_consulta.append("id_pago",id);
	
	datos_consulta.append("id_detalle_pago",id_detalle_pago);
	datos_consulta.append("id_banco_transaccion",id_banco_transaccion);

	datos_consulta.append("monto",monto);
	datos_consulta.append("estado",estado);
	datos_consulta.append("observacion",observacion);
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("tipo_pago",tipo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen",imagen);
	datos_consulta.append("apartamento_id",apartamento_id);
	datos_consulta.append("monto_mensualidad",monto_mensualidad);
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

	// Vaciamos por si acaso
	id_detalle_pago = null;
	id_banco_transaccion = null;

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

	let fila_datos = {
		id_pago: id,
		fecha,
		monto_mensualidad,
		estado,
		observacion
	};

	let datos = fila_datos;

	data_table.row(`#fila-${id}`).data([
		formatearFecha(datos.fecha),
		datos.monto_mensualidad + " $",
		obtenerEstado(datos.estado),
		datos.observacion,
		acciones.outerHTML
	]).draw();

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

// ============================= DETALLES PAGOS =========================================

let data_table_detalles, id_eliminado_detalles, id_registrado_detalles, id_modificar_detalles, referencia_an_detalles;
let tabla_detalles = document.querySelector("#tabla_detalles_pagos");
let boton_formulario_detalles = document.querySelector("#boton_formulario_detalles");
let modal_detalles = new bootstrap.Modal("#modal_detalles_pagos");
let formulario_usar_detalles = document.querySelector(`#form_detalles_pagos`);
let modalVistaPrevia_detalles = new bootstrap.Modal(document.querySelector("#modal_vista_previa_detalles"));

function envio_detalles(operacion) {	
	if (operacion == "Editar") {
		/*
			- getAttribute: es un método que permite obtener el valor de un atributo 
			específico de un elemento HTML.

			y "envio", "modificar", "registrar", "mensajes" son funciones definidas 
			por nosotroso mismos para que no te confundas.
		*/
		id_modificar_detalles = boton_formulario_detalles.getAttribute("id_modificar");//obtenemos el id del registro
		modificar_detalles(id_modificar_detalles);
	}
	else if(operacion == "Registrar"){
		//sino a registrar
		registrar_detalles();
	}else{
		// esto es imposible que pase pero aja
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

document.addEventListener("DOMContentLoaded", () => {
	const modal_detalles = document.querySelector(`#modal_detalles_pagos`);
	if (!modal_detalles) return console.error("❌ No se encontró el modal de detalles en el DOM.");

	modal_detalles.addEventListener("hide.bs.modal", () => {
		formulario_usar_detalles.reset();
		boton_formulario_detalles.removeAttribute("modificar");
		boton_formulario_detalles.removeAttribute("id_modificar");
		boton_formulario_detalles.textContent = "Registrar";

		const tituloModal = document.getElementById('titulo_modal_detalles');
		if (tituloModal) {
			tituloModal.textContent = "Registrar Detalle Pago";
		}

		formulario_usar_detalles.querySelectorAll("[class='w-100']").forEach(el => el.textContent = "");

		api();

		const nombreImagen = document.querySelector("#nombre_imagen_cargada_detalles");
		if (nombreImagen) nombreImagen.textContent = "";

		const btnEliminarImg = document.querySelector("#boton_eliminar_imagen_detalles");
		if (btnEliminarImg) {
			btnEliminarImg.classList.add("d-none");
			btnEliminarImg.removeAttribute("data-nombre");
		}

		const inputOculto = formulario_usar_detalles.querySelector("input[name='eliminar_imagen']");
		if (inputOculto) inputOculto.remove();
	});
});

// Cambiar el monto de mensualidad pero en detalles
document.querySelector("#mensualidad_id_detalles").addEventListener("change", function () {
	let seleccion = this.selectedOptions[0];
	let monto = seleccion.getAttribute("data-monto");

	console.log("Monto mensualidad seleccionada:", monto);
	document.querySelector("#monto_mensualidad_detalles").value = monto;
});

// Ocultar campos y esas cosas
document.getElementById("tipo_pago_detalles").addEventListener("change", function () {
    const metodo = this.value;

    // 1. Ocultamos los campos condicionales
    document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => {
        campo.classList.add("d-none");
    });

    // 2. Limpiar los campos de texto y selects
    document.querySelectorAll(".campo-monto input, .campos-bancarios input, .campos-bancarios select").forEach(campo => {
        campo.value = "";
    });

    // 3. Limpiar el input file, nombre de imagen y botón eliminar
    const inputImagen = document.getElementById("imagen_detalles");
    const nombreImagen = document.getElementById("nombre_imagen_cargada_detalles");
    const botonEliminar = document.getElementById("boton_eliminar_imagen_detalles");

    if (inputImagen) inputImagen.value = "";
    if (nombreImagen) nombreImagen.textContent = "";
    if (botonEliminar) botonEliminar.classList.add("d-none");

    // 4. Mostrar según método
    if (metodo === "Efectivo") {
        document.querySelectorAll(".campo-monto").forEach(campo => campo.classList.remove("d-none"));
    } else if (metodo === "Transferencia" || metodo === "Pago Movil") {
        document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => campo.classList.remove("d-none"));
    }
});

// 5. Al cargar: limpiar tipo_pago y ocultar campos
window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("tipo_pago_detalles").value = "";
    document.querySelectorAll(".campo-monto, .campos-bancarios").forEach(campo => {
        campo.classList.add("d-none");
    });

    // Limpiar imagen por si acaso
    const inputImagen = document.getElementById("imagen_detalles");
    const nombreImagen = document.getElementById("nombre_imagen_cargada_detalles");
    const botonEliminar = document.getElementById("boton_eliminar_imagen_detalles");

    if (inputImagen) inputImagen.value = "";
    if (nombreImagen) nombreImagen.textContent = "";
    if (botonEliminar) botonEliminar.classList.add("d-none");
});
// ...

async function registrar_detalles() {
	// el async vuelve la funcion asincrona	
	//Creamos el formData
	let datos_consulta = new FormData();
	//Creamos las variables con los datos de los inputs
	let monto = formulario_usar_detalles.querySelector("#monto_detalles").value,
	fecha = formulario_usar_detalles.querySelector("#fecha_detalles").value;
	tasa_dolar = formulario_usar_detalles.querySelector("#tasa_dolar_detalles").value;
	tipo_pago = formulario_usar_detalles.querySelector("#tipo_pago_detalles").value;
	banco_id = formulario_usar_detalles.querySelector("#banco_id_detalles").value;
	referencia = formulario_usar_detalles.querySelector("#referencia_detalles").value;
	imagen = formulario_usar_detalles.querySelector("#imagen_detalles").files[0];

	console.log("pago_actual.id:", pago_actual.id);
	console.log("pago_actual.mensualidad_id:", pago_actual.mensualidad_id);

	// le pasamos los datos por el formData
	datos_consulta.append("monto",monto);
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("tipo_pago",tipo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen",imagen);
	datos_consulta.append("pago_id", pago_actual.id);
	datos_consulta.append("mensualidad_id",pago_actual.mensualidad_id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','registrar_detalles');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta); // El await es para que espere el resultado, al ser asincrono, normalmente no lo esperaria
	// wait = esperar (english)
	modal_detalles.hide(); //Esconde el modal
	formulario_usar_detalles.reset();//Limpia el formulario

	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	id_registrado_detalles = await last_id(); //Guarda el nuevo id registrado, para darselo al evento de modificar
	
	let acciones = crearBotones_detalles(id_registrado_detalles.mensaje); //Crea botones
	
	// esta variable no hace nada, pero me dio error cuando la quite XD
	let fila = {
		id_detalles_pago: id_registrado_detalles.mensaje,
		fecha, // importante para los botones
		monto,
		tasa_dolar,
		tipo_pago,
	};

	let datos = fila;

	console.log(datos);

	await consultar_detalles(pago_actual.id); // recarga la tabla con todo fresco desde el backend

	/*await data_table_detalles.row.add([
		formatearFecha(datos.fecha),
		datos.monto + " $",
		datos.tasa_dolar,
		datos.tipo_pago,
		acciones.outerHTML
	]).draw();*/
	// Tiene el await para que lo espere, sino no la pone en la tabla

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');//Mensaje de que se completo la operacion
}

async function cargarMensualidades(apartamento_id) {
	let datos = new FormData();
	datos.append("operacion", "consultar_mensualidades");
	datos.append("apartamento_id", apartamento_id);

	let respuesta = await query(datos);
	let select = document.getElementById("mensualidad_id_detalles");

	select.innerHTML = '<option value="">Seleccione una mensualidad</option>';

	const meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
		"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

	respuesta.forEach(m => {
		let opcion = document.createElement("option");
		let nombre_mes = meses[parseInt(m.mes)];
		opcion.value = m.id_mensualidad;
		opcion.textContent = `${nombre_mes}/${m.anio} - ${m.monto}$`;
		opcion.setAttribute("data-monto", m.monto);
		select.appendChild(opcion);
	});
}

async function consultar_detalles(id_pago) {
	if ($.fn.DataTable.isDataTable("#tabla_detalles_pagos")) {
		$('#tabla_detalles_pagos').DataTable().clear().destroy();
	}

	//Creamos el formData
	datos_consulta = new FormData();

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_detalles');
	datos_consulta.append('id_pago', id_pago);

	//Llamamos a la funcion para hacer la consulta
	data = await query(datos_consulta)
	vaciar_tabla_detalles(); //Vaciamos la tabla de lo que tenia antes
	
	// Resvisamos el resultado
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	//recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
	await data.map(fila=>{
		llenarTabla_detalles(fila);
	})
	
	data_table_detalles = init_data_table_detalles(); //iniciamos el dataTable de jquery
}

function vaciar_tabla_detalles() {
	let cuerpo_tabla = document.querySelector(`#tabla_detalles_pagos tbody`);
	cuerpo_tabla.textContent = null;
}

function llenarTabla_detalles(fila) {
	console.log("Se llena tabla secundaria:",fila);
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_detalles_pagos tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	let id_campo = fila["id_detalle_pago"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let fecha_td = document.createElement("td"),
	monto_td = document.createElement("td"),
	tasa_dolar_td = document.createElement("td"),
    tipo_pago_td = document.createElement("td");

	// le damos el contenido de la consulta
	let datos = fila;

	fecha_td.textContent = formatearFecha(datos.fecha);
	monto_td.textContent = datos.monto + " $";
	tasa_dolar_td.textContent = datos.tasa_dolar;
	tipo_pago_td.textContent = datos.tipo_pago;

	let acciones = crearBotones_detalles(id_campo); 
	// creamos los botones de eliminar y modificar

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(fecha_td);
	fila_tabla.appendChild(monto_td);
	fila_tabla.appendChild(tasa_dolar_td);
	fila_tabla.appendChild(tipo_pago_td);
    fila_tabla.appendChild(acciones);

	fila_tabla.setAttribute("id",`fila-${id_campo}`);
	// le ponemos un id a las fila para cuando las eliminemos
	
	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);
}

function crearBotones_detalles(id) {
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
    boton_vista_previa.setAttribute("title", "Informacion");
    boton_vista_previa.setAttribute("value", id);
    boton_vista_previa.addEventListener("click", mostrarVistaPrevia_detalles);
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
	boton_editar.setAttribute("data-bs-target", "#modal_detalles_pagos");
	boton_editar.setAttribute("title","Editar Detalles");
	boton_editar.setAttribute("value",id);
	boton_editar.addEventListener("click",modificar_formulario_detalles)//Esa funcion esta mas abajo

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

		boton_eliminar.setAttribute("title","Eliminar Detalles");
		boton_eliminar.setAttribute("value",id);// el valor del id para eliminar	

		acciones.appendChild(boton_eliminar);
	}

	td.appendChild(acciones);

	return td;
}

async function eliminar_detalles(id) {
	//Creamos el formData
	datos_consulta = new FormData()

	// Le ponemos el id al FormData
	datos_consulta.append("id_detalle_pago",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','eliminar_detalles');

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);
	
	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	id_eliminado_detalles = id; 
	// con esto indicamos que se elimino un registro
	// en caso de que lo de abajo no lo elimine

	data_table_detalles.row(`#fila-${id}`).remove().draw(); // esto es para eliminar la fila del data table

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

async function modificar_formulario_detalles(e) {
	// primero buscamos el registro a modificar
	//Creamos el formData
	datos_consulta = new FormData();
		
	let id = e.target.value; // tomamos el id
	if (id === undefined) {
		id = e.target.parentElement.value; 
		//esto es por si seleciona el icono en vez del boton al dar click
	}
	// le damos el id
	datos_consulta.append("id_detalle_pago",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta_especifica_detalles');

	//Llamamos a la funcion para hacer la consulta y guardamos los datos
	data = await query(datos_consulta);

	console.log("Modificar Formulario Detalles:",data);
	
	// ahora seleccionamos los inputs
	let monto = formulario_usar_detalles.querySelector("#monto_detalles"),	
	apartamento_id = formulario_usar_detalles.querySelector("#apartamento_id_detalles");
	mensualidad_id = formulario_usar_detalles.querySelector("#mensualidad_id_detalles");
	monto_mensualidad = formulario_usar_detalles.querySelector("#monto_mensualidad_detalles");
	fecha = formulario_usar_detalles.querySelector("#fecha_detalles");
	tipo_pago = formulario_usar_detalles.querySelector("#tipo_pago_detalles");
	tasa_dolar = formulario_usar_detalles.querySelector("#tasa_dolar_detalles");
	referencia = formulario_usar_detalles.querySelector("#referencia_detalles");
	banco_id = formulario_usar_detalles.querySelector("#banco_id_detalles");

	mostrarCamposPorTipo(data.tipo_pago, formulario_usar_detalles);

	// le damos valor
	monto.value = data.monto;
	apartamento_id.value = data.apartamento_id;
	fecha.value = data.fecha;
	tipo_pago.value = data.tipo_pago;
	tasa_dolar.value = data.tasa_dolar;
	referencia.value = data.referencia;
	banco_id.value = data.id_banco;
	monto_mensualidad.value = data.monto_mensualidad;

	// Variables globales para poder modificar después
	id_detalle_pago = data.id_detalle_pago;
	id_banco_transaccion = data.id_banco_transaccion;

	// Mensualidad
	let mensualidades_respuesta = new FormData();
	mensualidades_respuesta.append("operacion", "consultar_mensualidades");
	mensualidades_respuesta.append("apartamento_id", data.apartamento_id);

	let mensualidades = await query(mensualidades_respuesta);

	// Limpiar mensualidades
	mensualidad_id.innerHTML = "<option value=''>Seleccione una mensualidad</option>";

	const meses = [
		"", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
		"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
	];

	mensualidades.forEach(m => {
		let opcion = document.createElement("option");
		let nombre_mes = meses[parseInt(m.mes)];
		opcion.value = m.id_mensualidad;
		opcion.text = `${nombre_mes}/${m.anio} - ${m.monto}$`;
		opcion.setAttribute("data-monto", m.monto);

		if (m.id_mensualidad == data.mensualidad_id) {
			opcion.selected = true;
			monto_mensualidad.value = m.monto;
		}

		mensualidad_id.appendChild(opcion);
	});
 
	// Mostrar nombre de imagen, esos id estan en el formulario
    const nombreImagen = document.querySelector("#nombre_imagen_cargada_detalles");
    const botonEliminarImagen = document.querySelector("#boton_eliminar_imagen_detalles");

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
		boton_formulario_detalles.setAttribute("hidden",true);
		boton_formulario_detalles.setAttribute("disabled",true);
		//si no los tiene apaga el boton.
	}

	// aqui cambiamos los datos del boton para registrar, para saber que ahora se va es a modificar un registro
	boton_formulario_detalles.setAttribute("modificar",true);
	boton_formulario_detalles.setAttribute("id_modificar",data.id_detalle_pago);
	boton_formulario_detalles.textContent = "Modificar";
	document.getElementById('titulo_modal_detalles').textContent = "Modificar Detalle Pago";

	id_modificar_detalles = id;
	referencia_an_detalles = referencia.value;
	//guardamos el orginal del correo, para que no choquen con las validaciones
}

async function mostrarVistaPrevia_detalles(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_detalle_pago", id);
    datos_consulta.append("operacion", "consulta_especifica_detalles");

    const respuesta = await query(datos_consulta);
    const data = respuesta;

	document.getElementById("vista_fecha_detalles").textContent = formatearFecha(data.fecha);
    document.getElementById("vista_monto_detalles").textContent = data.monto;
    document.getElementById("vista_tasa_dolar_detalles").textContent = data.tasa_dolar;
    //document.getElementById("vista_prioridad").innerHTML = obtenerPrioridadTexto(data.prioridad);
    document.getElementById("vista_tipo_pago_detalles").textContent = data.tipo_pago;

	document.getElementById("vista_nombre_banco_detalles").innerHTML = data.nombre_banco && data.nombre_banco.trim() !== ""
    ? data.nombre_banco
    : "No hay banco registrado";

	document.getElementById("vista_referencia_detalles").innerHTML = data.referencia && data.referencia.trim() !== ""
    ? data.referencia
    : "No hay referencia registrada";

    // Resetear mensaje de error por si estaba visible
    document.getElementById("vista_imagen_detalles").style.display = "block";
    document.getElementById("mensaje_error_imagen_detalles").classList.add("d-none");

	console.log("Vista previa de tabla secundaria");
	console.log("Respuesta obtenida:", respuesta);

    const imagen = (data.imagen && data.imagen !== "")
        ? `recursos/img/${data.imagen}`
        : "";

    document.getElementById("vista_imagen_detalles").setAttribute("src", imagen);

    // Mostrar el modal como los otros
    modalVistaPrevia_detalles.show();
}

async function modificar_detalles(id) {	
	//Creamos el formData
	let datos_consulta = new FormData();

	//Guardamos los datos del formulario
	let monto = formulario_usar_detalles.querySelector("#monto_detalles").value,
	fecha = formulario_usar_detalles.querySelector("#fecha_detalles").value;
	tasa_dolar = formulario_usar_detalles.querySelector("#tasa_dolar_detalles").value;
	tipo_pago = formulario_usar_detalles.querySelector("#tipo_pago_detalles").value;
	banco_id = formulario_usar_detalles.querySelector("#banco_id_detalles").value;
	referencia = formulario_usar_detalles.querySelector("#referencia_detalles").value;
	imagen = formulario_usar_detalles.querySelector("#imagen_detalles").files[0];

	console.log("pago_actual.id:", pago_actual.id);
	console.log("pago_actual.mensualidad_id:", pago_actual.mensualidad_id);
	console.log("id_banco_transaccion:", id_banco_transaccion);

	// Le ponemos los datos del formulario
	datos_consulta.append("id_detalle_pago",id);
	datos_consulta.append("id_pago",pago_actual.id);
	
	datos_consulta.append("id_banco_transaccion",id_banco_transaccion);

	datos_consulta.append("monto",monto);
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("tasa_dolar",tasa_dolar);
	datos_consulta.append("tipo_pago",tipo_pago);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("referencia",referencia);
	datos_consulta.append("imagen", imagen);
	datos_consulta.append("apartamento_id",apartamento_id);
	datos_consulta.append("monto_mensualidad",monto_mensualidad);
	datos_consulta.append("mensualidad_id",pago_actual.mensualidad_id);
	// ...

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','modificar_detalles');

	// Solo si hay una nueva imagen seleccionada
    if (imagen !== undefined) {
        datos_consulta.append("imagen", imagen);
    }

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);

	formulario_usar_detalles.reset(); //Limpiamos el formulario
 	modal_detalles.hide(); // escondemos el modal

 	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	// Vaciamos por si acaso
	id_detalle_pago = null;
	id_banco_transaccion = null;

	// al terminar le damos al boton su valores originales

	boton_formulario_detalles.removeAttribute("modificar");
	boton_formulario_detalles.removeAttribute("id_modificar");	
	boton_formulario_detalles.textContent = "Registrar";
 
	document.getElementById('titulo_modal_detalles').textContent = "Registrar Detalle Pago";

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');//Mensaje de que se completo la operacion

	// Obtener ruta de imagen actualizada o previa
    const rutaImagen = respuesta.imagen_url || "recursos/img/default.jpg";

	// esto de abajo es para editar la fila que se modifico en el data table
	let acciones = crearBotones(id); // creamos otro botones (no se que tan necesario sea esto)

	let fila_datos = {
		id_detalles_pago: id,
		fecha, 
		monto,
		tasa_dolar,
		tipo_pago,
	};

	let datos = fila_datos;

	await consultar_detalles(pago_actual.id);

	/*data_table.row(`#fila-${id}`).data([
		datos.fecha,
		datos.monto_mensualidad + " $",
		obtenerEstado(datos.estado),
		datos.observacion,
		acciones.outerHTML
	]).draw();*/

	// se le vuelve a poner el evento al boton
	let fila = document.querySelector(`#fila-${id}`);
	if (fila) {
		fila.querySelector(`[value='${id}']`).addEventListener("click",modificar_formulario);
	}	
}

document.querySelector("#boton_eliminar_imagen_detalles").addEventListener("click", function () {
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
            document.querySelector("#nombre_imagen_cargada_detalles").textContent = "Imagen eliminada.";
            document.querySelector("#boton_eliminar_imagen_detalles").classList.add("d-none");
        }
    });
});

function init_data_table_detalles() {
	return new DataTable("#tabla_detalles_pagos",{
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

const observer_detalles = new MutationObserver(() => {
	reasignarEventos_detalles();
});

observer_detalles.observe(tabla_detalles, {childList:true});

function reasignarEventos_detalles() {
	console.log("me ejecuto");
	if (id_eliminado_detalles){ //Si hay un eliminado que no se ha quitado de la tabla
		let existe_fila = tabla.querySelector(`#fila-${id_eliminado_detalles}`)
		if (existe_fila) {
			data_table.row(`#fila-${id_eliminado_detalles}`).remove().draw();
			id_eliminado_detalles = null;	
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
			text: "¿Está seguro que desea eliminar este detalle pago?",
			showCancelButton: true,
			confirmButtonText: "Eliminar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
				if (resultado.isConfirmed) {
					eliminar_detalles(id);				
				}
			});
	});
	
	document.querySelectorAll("button[title='Informacion']").forEach(btn => {
        btn.removeEventListener("click", mostrarVistaPrevia_detalles);
        btn.addEventListener("click", mostrarVistaPrevia_detalles);
    });

	document.querySelectorAll("button[title='Editar Detalles']").forEach(btn => {
        btn.removeEventListener("click", modificar_formulario_detalles);
        btn.addEventListener("click", modificar_formulario_detalles);
    });

	if (id_registrado_detalles) { // en caso de que se haya registrado y no se haya añadido a la tabla
		let boton_modificar = tabla_detalles.querySelector(`[value='${id_registrado_detalles.mensaje}']`); 
		// captura el boton de editar, sino lo encuentra es que no esta en su pagina, y no tiene caso ponerle evento
		if (boton_modificar) {
			// si lo encuentra le pone el evento de modificar
			boton_modificar.addEventListener("click",modificar_formulario_detalles);
			boton_modificar.parentElement.parentElement.parentElement.setAttribute("id",`fila-${id_registrado_detalles.mensaje}`);
			id_registrado_detalles = null;
		}
	}
}

const resizeObserver = new ResizeObserver(entries => {
	if (data_table_detalles){
		data_table_detalles.draw();
	}
})

resizeObserver.observe(tabla_detalles);