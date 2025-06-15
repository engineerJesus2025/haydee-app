consultar(); 
let data_table, id_eliminado, id_registrado,id_modificar, nro_apartamento_an;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_apartamentos"); //La tabla
let boton_formulario = document.querySelector("#boton_formulario"); // el boton
let modal = new bootstrap.Modal("#modal_apartamentos"); // el modal
let formulario_usar = document.querySelector(`#form_apartamentos`); // el form

function envio(operacion) {	
	if (operacion == "Editar") {
		id_modificar = boton_formulario.getAttribute("id_modificar");
		modificar(id_modificar);
	}
	else if(operacion == "Registrar"){
		registrar();
	}else{
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

document.querySelector(`#modal_apartamentos`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";
	document.getElementById('titulo_modal').textContent = "Registrar Apartamento";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");
});

async function registrar() {
	datos_consulta = new FormData();
	
	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento").value,
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion").value,	
	gas = Number(formulario_usar.querySelector("#gas").value), 
	agua = Number(formulario_usar.querySelector("#agua").value),
    alquilado = Number(formulario_usar.querySelector("#alquilado").value),
    propietario_id = formulario_usar.querySelector("#propietario_id").value;
	propietario_texto = formulario_usar.querySelector("#propietario_id").selectedOptions[0].text;

	// Formatear los valores como en la consulta SQL
	let gas_texto = (gas == 1) ? 'TIENE' : 'NO TIENE';
	let agua_texto = (agua == 1) ? 'TIENE' : 'NO TIENE';
	let alquilado_texto = (alquilado == 1) ? 'SI' : 'NO';
	let porcentaje_formateado = porcentaje_participacion + '%';

	datos_consulta.append("nro_apartamento",nro_apartamento);
	datos_consulta.append("porcentaje_participacion",porcentaje_participacion);	
	datos_consulta.append("gas",gas);
	datos_consulta.append("agua",agua);
	datos_consulta.append("alquilado",alquilado);
    datos_consulta.append("propietario_id",propietario_id);

	datos_consulta.append('operacion','registrar');
	
	let respuesta = await query(datos_consulta); 

	modal.hide();
	formulario_usar.reset();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	id_registrado = await last_id();
	
	let acciones = crearBotones(id_registrado.mensaje);
	
	let res_data_table = await data_table.row.add([`${nro_apartamento}`,`${porcentaje_formateado}`,`${gas_texto}`,`${agua_texto}`,`${alquilado_texto}`,`${propietario_texto}`,`${acciones.outerHTML}`]).draw();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function consultar() {
	datos_consulta = new FormData();

	datos_consulta.append('operacion','consulta');

	data = await query(datos_consulta)
	vaciar_tabla();
	
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;
	}

	await data.map(fila=>{
		llenarTabla(fila);
	})
	
	data_table = init_data_table();
}

function vaciar_tabla() {
	let cuerpo_tabla = document.querySelector(`#tabla_apartamentos tbody`);
	cuerpo_tabla.textContent = null;
}

function llenarTabla(fila) {
	let cuerpo_tabla = document.querySelector(`#tabla_apartamentos tbody`);

	console.log(fila);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");

	let id_campo = fila["id_apartamento"];
	
	let nro_apartamento_td = document.createElement("td"),
	porcentaje_participacion_td = document.createElement("td"),	
	gas_td = document.createElement("td"), 
	agua_td = document.createElement("td");
    alquilado_td = document.createElement("td");
    propietario_id_td = document.createElement("td");

	nro_apartamento_td.textContent = fila["nro_apartamento"];
	porcentaje_participacion_td.textContent = fila["porcentaje_participacion"];
	gas_td.textContent = fila["gas"];
	agua_td.textContent = fila["agua"];
    alquilado_td.textContent = fila["alquilado"];
    propietario_id_td.textContent = fila["propietario_id"];

	let acciones = crearBotones(id_campo); 

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(nro_apartamento_td);
	fila_tabla.appendChild(porcentaje_participacion_td);
	fila_tabla.appendChild(gas_td);
	fila_tabla.appendChild(agua_td);
	fila_tabla.appendChild(alquilado_td);
    fila_tabla.appendChild(propietario_id_td);
    fila_tabla.appendChild(acciones);

	fila_tabla.setAttribute("id",`fila-${id_campo}`);
	// le ponemos un id a las fila para cuando las eliminemos
	
	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);	
}

function crearBotones(id) {
	let td = document.createElement("td");
	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");

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
	boton_editar.setAttribute("data-bs-target", "#modal_apartamentos");

	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);
	boton_editar.addEventListener("click",modificar_formulario)

	//Le ponemos los botones al <td><td> de las acciones
	acciones.appendChild(boton_editar);

	if (permiso_eliminar) {
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
		boton_eliminar.setAttribute("value",id);	

		acciones.appendChild(boton_eliminar);
	}

	td.appendChild(acciones);

	return td;
}

async function eliminar(id) {
	datos_consulta = new FormData()

	datos_consulta.append("id_apartamento",id);

	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	id_eliminado = id; 

	data_table.row(`#fila-${id}`).remove().draw();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');
}

async function modificar_formulario(e) {
	datos_consulta = new FormData();
		
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value; 
	}
	
	datos_consulta.append("id_apartamento",id);

	datos_consulta.append('operacion','consulta_especifica');

	data = await query(datos_consulta);	
	
	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento"),
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion"),	
	gas = formulario_usar.querySelector("#gas"),	
	agua = formulario_usar.querySelector("#agua");
    alquilado = formulario_usar.querySelector("#alquilado");
    propietario_id = formulario_usar.querySelector("#propietario_id");

	nro_apartamento.value = data.nro_apartamento;
	porcentaje_participacion.value = data.porcentaje_participacion;	
	gas.value = data.gas;
	agua.value = data.agua;
    alquilado.value = data.alquilado;
    propietario_id.value = data.propietario_id;

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}

	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_apartamento);
	boton_formulario.textContent = "Modificar";
	document.getElementById('titulo_modal').textContent = "Modificar Apartamento";

	id_modificar = id;
	nro_apartamento_an = nro_apartamento.value;
}

async function modificar(id) {	
	let datos_consulta = new FormData();

	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento").value,
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion").value,
	gas = Number(formulario_usar.querySelector("#gas").value), 
	agua = Number(formulario_usar.querySelector("#agua").value),
    alquilado = Number(formulario_usar.querySelector("#alquilado").value),
    propietario_id = formulario_usar.querySelector("#propietario_id").value;
	propietario_texto = formulario_usar.querySelector("#propietario_id").selectedOptions[0].text;

	// Formatear los valores como en la consulta SQL
	let gas_texto = (gas == 1) ? 'TIENE' : 'NO TIENE';
	let agua_texto = (agua == 1) ? 'TIENE' : 'NO TIENE';
	let alquilado_texto = (alquilado == 1) ? 'SI' : 'NO';
	let porcentaje_formateado = porcentaje_participacion + '%';

	datos_consulta.append("id_apartamento",id);

	datos_consulta.append("nro_apartamento",nro_apartamento);
	datos_consulta.append("porcentaje_participacion",porcentaje_participacion);	
	datos_consulta.append("gas",gas);
	datos_consulta.append("agua",agua);
	datos_consulta.append("alquilado",alquilado);
    datos_consulta.append("propietario_id",propietario_id);
	
	datos_consulta.append('operacion','modificar');

	let respuesta = await query(datos_consulta);

	formulario_usar.reset();
 	modal.hide();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";

	document.getElementById('titulo_modal').textContent = "Registrar Apartamento";

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');

	let acciones = crearBotones(id);

	data_table.row(`#fila-${id}`).data([`${nro_apartamento}`,`${porcentaje_formateado}`,`${gas_texto}`,`${agua_texto}`,`${alquilado_texto}`,`${propietario_texto}`,`${acciones.outerHTML}`])
	data_table.draw();

	let fila = document.querySelector(`#fila-${id}`);
	if (fila) {
		fila.querySelector(`[value='${id}']`).addEventListener("click",modificar_formulario);
	}	
}

async function last_id() {
	datos_consulta = new FormData()
	datos_consulta.append('operacion','ultimo_id');
	let res = await query(datos_consulta);
	return res;
}

async function query(datos){
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json();		
		return result;
	})
	
	return data;
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

function init_data_table() {
	return new DataTable("#tabla_apartamentos",{
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

const observer = new MutationObserver(() => {
	reasignarEventos();
});

observer.observe(tabla, {childList:true});

function reasignarEventos() {
	console.log("me ejecuto");
	if (id_eliminado){
		let existe_fila = tabla.querySelector(`#fila-${id_eliminado}`)
		if (existe_fila) {
			data_table.row(`#fila-${id_eliminado}`).remove().draw();
			id_eliminado = null;	
		}
	
	}

	$(".eliminar").on("click",function(e){
		id = e.target.value;
		if (id == undefined) {	
			id = e.target.parentElement.value;
		}
		Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea eliminar este apartamento?",
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

	if (id_registrado) {
		let boton_modificar = tabla.querySelector(`[value='${id_registrado.mensaje}']`); 
		
		if (boton_modificar) {
			
			boton_modificar.addEventListener("click",modificar_formulario);
			boton_modificar.parentElement.parentElement.parentElement.setAttribute("id",`fila-${id_registrado.mensaje}`);
			id_registrado = null;
		}
	}
}