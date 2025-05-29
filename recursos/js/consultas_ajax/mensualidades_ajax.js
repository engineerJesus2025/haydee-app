consultar(); // para llenar la tabla al cargar
//Para tomar el precio del dolar
let dolar = {};
api();
//Tablas
let tabla_mensualidad = document.querySelector(`#tabla_mensualidad`);
let tabla_mensualidad_asignar = document.querySelector(`#tabla_mensualidad_asignar`);

//Botones
let boton_registrar = document.getElementById("boton_registrar");
let boton_editar = document.getElementById("boton_editar");
let boton_formulario = document.getElementById("boton_formulario");
let boton_eliminar = document.getElementById("boton_eliminar");
// Modal
let modal = new bootstrap.Modal("#modal_mensualidad"); // el modal
//Select
let select_mes = document.getElementById('mes_select');

//Eventos
select_mes.addEventListener("change",e=>{
	let fecha = e.target.selectedOptions[0].id
	consultarMensualidad(fecha);
	// console.log(e)
});
boton_registrar.addEventListener("click",e=>{
	let fecha = select_mes.selectedOptions[0].id
	llenarTablaNueva(fecha);
	// console.log()
});
boton_editar.addEventListener("click",e=>{
	let fecha = select_mes.selectedOptions[0].id
	llenarTablaNueva(fecha);
	llenarTablaEditar(fecha);
});

document.querySelector(`#modal_mensualidad`).addEventListener("hide.bs.modal",()=>{	
	// No me di mala vida
	tabla_mensualidad_asignar.innerHTML= `<caption>Tabla de Mensualidades</caption><thead><tr><th>Apartamentos</th></tr></thead><tbody></tbody><tfoot><tr><td>Total:</td></tr></tfoot></table>`;

	boton_formulario.textContent = "Guardar Mensualidad";
	document.getElementById('titulo_modal').textContent = "Registrar Mensualidad";
});


//Funciones
async function consultar() {
	//Creamos el formData
	datos_consulta = new FormData();

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar');

	//Llamamos a la funcion para hacer la consulta
	let data = await query(datos_consulta);
	
	// Resvisamos el resultado
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', data.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	let fragment = document.createDocumentFragment();
	let fechas_mensualidades = [];

	data.map(registro=>{
		let option = document.createElement("option");

		let fecha = new Date(`${registro.mes}-01-${registro.anio}`);
		mes_buscar = fecha.toLocaleString("es-ES",{month: 'long'});
		anio_buscar = fecha.getFullYear();
		
		option.setAttribute("id",fecha.toLocaleDateString());
		option.textContent = `${mes_buscar} del ${anio_buscar}`;

		fragment.appendChild(option);
		fechas_mensualidades.push(new Date(registro.anio,parseInt(registro.mes - 1),1))
	});

	// console.log(fechas_mensualidades);
	verificarMeses(fechas_mensualidades);

	select_mes.appendChild(fragment);

	let fecha_buscar = select_mes.selectedOptions[0].id

	consultarMensualidad(fecha_buscar);
}

async function consultarMensualidad(fecha_buscar) {
	//Creamos el formData
	datos_consulta = new FormData();

	datos_consulta.append('fecha',fecha_buscar);
	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_mensualidad');

	//Llamamos a la funcion para hacer la consulta
	data = await query(datos_consulta);
	// Resvisamos el resultado
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	tabla_mensualidad.getElementsByTagName('tbody')[0].textContent = null;

	//recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
	await data.map(fila=>{
		llenarTabla(fila);
	});
	
	// data_table = init_data_table(); //iniciamos el dataTable de jquery
}

function llenarTabla(fila) {
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_mensualidad tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	// let id_campo = fila["id_mensualidad"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let mes_anio_td = document.createElement("td"),
	apartamento_td = document.createElement("td"),	
	propietario_td = document.createElement("td"), 
	monto_td = document.createElement("td");

	fecha = new Date(`${fila["mes"]}/01/${fila["anio"]}`);
	mes = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;	
	anio = fecha.getFullYear();

	// le damos el contenido de la consulta
	mes_anio_td.textContent = `${mes} del ${anio}`;
	apartamento_td.textContent = `Apatamento Nº ${fila["nro_apartamento"]}`;
	propietario_td.textContent = `${fila["nombre"]} ${fila["apellido"]}`;
	monto_td.textContent = `${fila["monto"]}Bs.`;

	
	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(mes_anio_td);
	fila_tabla.appendChild(apartamento_td);
	fila_tabla.appendChild(propietario_td);
	fila_tabla.appendChild(monto_td);

	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);
}

async function llenarTablaNueva(fecha) {
	//Llenamos el tbody
	let datos_consulta = new FormData();

	datos_consulta.append("operacion","consultar_apartamentos");

	let respuesta = await query(datos_consulta);

	if(!(respuesta.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	let tbody_tabla = tabla_mensualidad_asignar.querySelector("tbody");
	let fragment_tbody = document.createDocumentFragment();

	respuesta.map(apartamento=>{
		let tr = document.createElement("tr");
		tr.setAttribute("id",apartamento.id_apartamento);
		tr.setAttribute("participacion",apartamento.porcentaje_participacion);

		let td_1 = document.createElement("td");
		td_1.textContent = `Apartamento Nº${apartamento.nro_apartamento}`;

		tr.appendChild(td_1);
		fragment_tbody.appendChild(tr);
	});

	tbody_tabla.appendChild(fragment_tbody);

	//Llenamos el thead
	datos_consulta = new FormData();

	datos_consulta.append("fecha",fecha);
	datos_consulta.append("operacion","consultar_gastos");

	respuesta = await query(datos_consulta);

	if(!(respuesta.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	let fila_cabecera = tabla_mensualidad_asignar.querySelector("thead tr");
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");
	let filas_footer = tabla_mensualidad_asignar.querySelector("tfoot tr");

	let fragment = document.createDocumentFragment();
	let fragment_footer = document.createDocumentFragment();
	
	respuesta.map(gasto=>{
		let th = document.createElement("th");
		
		th.textContent = gasto.tipo_gasto;		
		th.setAttribute("class","text-center");
		
		filas_cuerpo.forEach(fila=>{
			let td = document.createElement("td");
			td.setAttribute("class","text-center");

			let checkbox  = document.createElement("input");
			checkbox.setAttribute("type","checkbox");
			checkbox.setAttribute("class","form-check-input");
			checkbox.setAttribute("gasto",gasto.monto);
			checkbox.setAttribute("id_gasto",gasto.id_gasto);
			//Asignar el evento a los checkbox
			checkbox.addEventListener("change",e=>{
				let participacion = fila.getAttribute("participacion");
				let monto_apartamento = (participacion * checkbox.getAttribute("gasto")) / 100;

				if (checkbox.checked) {
					fila.lastElementChild.textContent = parseFloat(fila.lastElementChild.textContent) + monto_apartamento;
					filas_footer.lastElementChild.textContent = parseFloat(filas_footer.lastElementChild.textContent) + monto_apartamento;
				}else{
					fila.lastElementChild.textContent = parseFloat(fila.lastElementChild.textContent) - monto_apartamento;
					filas_footer.lastElementChild.textContent = parseFloat(filas_footer.lastElementChild.textContent) - monto_apartamento;
				}
			});

			td.appendChild(checkbox);

			fila.appendChild(td);
		});

		let td = document.createElement("td");
		fragment_footer.appendChild(td);

		fragment.appendChild(th);
	});
	//Llenando la ultima fila, que es la del total
	let th = document.createElement("th");
	th.textContent = "Total a Pagar";

	fragment.appendChild(th);

	filas_cuerpo.forEach(fila=>{
		let td = document.createElement("td");
		td.textContent = 0;
		td.setAttribute("class","ps-3");

		fila.appendChild(td);
	});

	fila_cabecera.appendChild(fragment);

	let td_footer = document.createElement("td");
	td_footer.textContent = 0;
	td_footer.setAttribute("class","ps-3");

	fragment_footer.appendChild(td_footer);

	filas_footer.appendChild(fragment_footer);
}

function verificarMeses(fechas) {
  // Ordena las fechas por mes y año
  const fechasOrdenadas = fechas.sort((a, b) => a.getFullYear() - b.getFullYear() || a.getMonth() - b.getMonth());

  // Verifica si hay algún mes faltante entre las fechas consecutivas
  for (let i = 0; i < fechasOrdenadas.length - 1; i++) {
    const fechaActual = fechasOrdenadas[i];
    const fechaSiguiente = fechasOrdenadas[i + 1];

    // Si el mes siguiente no es consecutivo al actual, retorna true
    if (fechaSiguiente.getFullYear() > fechaActual.getFullYear() || fechaSiguiente.getMonth() > fechaActual.getMonth() + 1) {
      console.log(fechaActual);
    }
  }
}

function envio(operacion) {	
	if (operacion == "Editar") {
		// id_modificar = boton_formulario.getAttribute("id_modificar");//obtenemos el id del registro
		modificar();
	}
	else if(operacion == "Registrar"){
		//sino a registrar
		registrar();
	}else if(operacion == "Eliminar"){
		let fecha = select_mes.selectedOptions[0].id
		eliminar(fecha);
	}
	else{
		// esto es imposible que pase pero aja
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente');
	}
}

async function registrar() {
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");

	//Recorrer por apartamentos
	filas_cuerpo.forEach(async (tr)=>{
	//Por cada apartamento se registra una mensualidad con el total del monto
		let monto, tasa_dolar, mes, anio, apartamento_id;

		let fecha_actual = new Date();
		// mes = parseInt(select_mes.selectedOptions[0].id.split("/")[1]);
		// anio = parseInt(select_mes.selectedOptions[0].id.split("/")[2]);
		mes = fecha_actual.getMonth();
		anio = fecha_actual.getFullYear();

		monto = parseFloat(tr.lastElementChild.textContent);
		tasa_dolar = dolar.bcv;
		
		apartamento_id = tr.id;

		let datos_consulta = new FormData();

		datos_consulta.append("operacion","registrar_mensualidad");

		datos_consulta.append("monto",monto);
		datos_consulta.append("tasa_dolar",tasa_dolar);
		datos_consulta.append("mes",mes);
		datos_consulta.append("anio",anio);
		datos_consulta.append("apartamento_id",apartamento_id);

		let respuesta = await query(datos_consulta);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}

		//Por cada mensualidad registrada se registra en la puente los gastos asignados
		let id_mensualidad = respuesta.lastId;
		let id_gastos = [];
		tr.querySelectorAll("td").forEach(td=>{			
			if(td.firstElementChild != null){
				if(td.firstElementChild.checked){
					id_gastos.push(parseInt(td.firstElementChild.getAttribute("id_gasto")));					
				}
			}
		});

		datos_consulta = new FormData();

		datos_consulta.append("operacion","registrar_gastos_mensualidades");
		datos_consulta.append("id_mensualidad",id_mensualidad);
		datos_consulta.append("id_gastos",id_gastos);
		console.log(datos_consulta);
		respuesta = await query(datos_consulta);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}
	});
	modal.hide();
	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
	consultar();
}

async function llenarTablaEditar(fecha) {
	//Creamos el formData
	let datos_consulta = new FormData();

	datos_consulta.append('fecha',fecha);
	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_mensualidad');

	//Llamamos a la funcion para hacer la consulta
	let mensualidades = await query(datos_consulta);
	// Resvisamos el resultado
	if(!(mensualidades.estatus == undefined)){
		mensajes('error',4000,'Atencion', mensualidades.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	let ids = [];
	mensualidades.map(mensualidad_apartamento=>{ids.push(mensualidad_apartamento.id_mensualidad)});

	console.log(mensualidades);
	datos_consulta = new FormData();

	datos_consulta.append('ids_mensualidades',ids);
	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_gastos_mes');

	let gastos_mes = await query(datos_consulta);
	// Resvisamos el resultado
	if(!(gastos_mes.estatus == undefined)){
		mensajes('error',4000,'Atencion', gastos_mes.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}
	console.log(gastos_mes);
	// Todo esto para llenar los chebox
	let filas_footer = tabla_mensualidad_asignar.querySelector("tfoot tr");
	mensualidades.map(mensualidad_apartamento=>{
		let fila_tabla = tabla_mensualidad_asignar.querySelector(`[id='${mensualidad_apartamento.apartamento_id}']`);
		gastos_mes.map(mensualidades=>{
			if (mensualidades[0].mensualidad_id == mensualidad_apartamento.id_mensualidad){
				mensualidades.map(mensualidad=>{
					fila_tabla.querySelectorAll("[id_gasto]").forEach(checkbox=>{
						if (checkbox.getAttribute("id_gasto") == mensualidad.gasto_id) {
							checkbox.checked = true;
							let participacion = fila_tabla.getAttribute("participacion");
							let monto_apartamento = (participacion * checkbox.getAttribute("gasto")) / 100;

							fila_tabla.lastElementChild.setAttribute("id",mensualidad.mensualidad_id);
							fila_tabla.lastElementChild.textContent = parseFloat(fila_tabla.lastElementChild.textContent) + monto_apartamento;
							filas_footer.lastElementChild.textContent = parseFloat(filas_footer.lastElementChild.textContent) + monto_apartamento;
						}
					});
				});
			}
		});
	});
	boton_formulario.textContent = "Editar Mensualidad";
	boton_formulario.setAttribute("op","Editar");
}

async function modificar() {
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");

	//Recorrer por apartamentos
	filas_cuerpo.forEach(async (tr)=>{
	//Por cada apartamento se registra una mensualidad con el total del monto
		let monto, tasa_dolar, mes, anio, apartamento_id, id_mensualidad;

		let fecha_actual = new Date();
		mes = parseInt(select_mes.selectedOptions[0].id.split("/")[1]);
		anio = parseInt(select_mes.selectedOptions[0].id.split("/")[2]);
		// mes = fecha_actual.getMonth();
		// anio = fecha_actual.getFullYear();

		monto = parseFloat(tr.lastElementChild.textContent);
		tasa_dolar = dolar.bcv;
		
		apartamento_id = tr.id;
		id_mensualidad = tr.lastElementChild.id;

		let datos_consulta = new FormData();

		datos_consulta.append("operacion","editar_mensualidad");

		datos_consulta.append("monto",monto);
		datos_consulta.append("tasa_dolar",tasa_dolar);
		datos_consulta.append("mes",mes);
		datos_consulta.append("anio",anio);
		datos_consulta.append("apartamento_id",apartamento_id);
		datos_consulta.append("id_mensualidad",id_mensualidad);

		let respuesta = await query(datos_consulta);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}

		//Por cada mensualidad registrada se registra en la puente los gastos asignados		
		let id_gastos = [];
		tr.querySelectorAll("td").forEach(td=>{			
			if(td.firstElementChild != null){
				if(td.firstElementChild.checked){
					id_gastos.push(parseInt(td.firstElementChild.getAttribute("id_gasto")));					
				}
			}
		});

		datos_consulta = new FormData();

		datos_consulta.append("operacion","editar_gastos_mensualidades");
		datos_consulta.append("id_mensualidad",id_mensualidad);
		datos_consulta.append("id_gastos",id_gastos);

		respuesta = await query(datos_consulta);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}
	});
	mensajes('success',4000,'Atencion','Se han editado las mensualidaddes exitosamente');
	consultar();

	modal.hide();
}

async function eliminar(fecha) {
	let datos_consulta = new FormData();

	datos_consulta.append("operacion","eliminar_mensualidad");
	datos_consulta.append("fecha",fecha);
	
	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	mensajes('success',4000,'Atencion','Se ha eliminado la mensualidad exitosamente');
	consultar();
}

async function api() {
    try {
        let response = await fetch('https://pydolarve.org/api/v2/dollar?page=alcambio');
        let obj_dolar = await response.json();
        // console.log(obj_dolar);

		dolar.bcv = obj_dolar.monitors.bcv.price;
		dolar.paralelo = obj_dolar.monitors.enparalelovzla.price;      
    } 
    catch (error) {document.getElementById('tasa_dolar').value = "Error";}
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

// esta funcion es para incializar el data table
function init_data_table() {
	return new DataTable("#tabla_mensualidad",{
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
}

