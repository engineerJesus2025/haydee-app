let nro_apartamento_an;
let tabla_apartamentos;
let id_apartamento_seleccionado;
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_apartamentos"); //La tabla
let boton_formulario = document.querySelector("#boton_formulario"); // el boton
let modal = new bootstrap.Modal("#modal_apartamentos"); // el modal
let modal_carga = new bootstrap.Modal("#modal_carga");
let formulario_usar = document.querySelector(`#form_apartamentos`); // el form
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa")); // Boton vista previa

consultar();

function envio(operacion) {
	if (operacion == "Editar") {
		let id_modificar = boton_formulario.getAttribute("id_modificar");
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
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Apartamento";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
});

document.getElementById('modal_habitantes').addEventListener('hidden.bs.modal', () => {
    modalVistaPrevia.show();
});

document.getElementById('header-toggle').addEventListener("click",e=>{
	setTimeout(function(){
		tabla_apartamentos.columns.adjust().draw();
	},450);
});

document.getElementById('modal_vista_previa').addEventListener('shown.bs.modal', function () {
    if ($.fn.DataTable.isDataTable("#tabla_habitantes")) {
        $('#tabla_habitantes').DataTable().columns.adjust(); //Con lo demas daba error -_--(O_O)--_-
    }
});

async function registrar() {
	datos_consulta = new FormData();
	
	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento").value,
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion").value,	
	gas = formulario_usar.querySelector("#gas").value, 
	agua = formulario_usar.querySelector("#agua").value,
    alquilado = formulario_usar.querySelector("#alquilado").value;

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

	datos_consulta.append('operacion','registrar');
	
	let respuesta = await query(datos_consulta,true); 

	modal.hide();
	formulario_usar.reset();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_apartamentos.ajax.reload();	

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function consultar() {
	const paramentros_consulta = (data)=>{data.operacion = 'consulta';}
	const estructura_tabla_apartamentos = [
 		{
 			"data": null,
            "render": function (row) {            
                return `Nro: ${row.nro_apartamento}`;                 
            }  
        },
		{ 
			"data": null, 
			"render": function (row) {                
                return `${row.porcentaje_participacion + '%'}`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	return `${(row.gas == 1) ? 'TIENE' : 'NO TIENE'}`;
            }
        },
		{ 
            "data": null, 
            "render": function (row) {
            	return `${(row.agua == 1) ? 'TIENE' : 'NO TIENE'}`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	return `${(row.alquilado == 1) ? 'SI' : 'NO'}`;
            }
        },     
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row.id_apartamento;
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	];

 	const configuraciones_tabla_apartamentos = (row, data)=>{
 		const option = new Option(data.nro_apartamento, data.id_apartamento);
 		document.getElementById("apartamento_id").add(option);

		row.setAttribute("id",`fila-${data.id_apartamento}`); 		
		row.querySelector(".vista_previa")?.addEventListener('click',mostrarVistaPrevia);
 		row.querySelector(".editar")?.addEventListener('click',modificar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar);
 	}

 	tabla_apartamentos = crearDataTable('tabla_apartamentos',estructura_tabla_apartamentos,paramentros_consulta,configuraciones_tabla_apartamentos);
}

function llenarTabla(fila) {
	let cuerpo_tabla = document.querySelector(`#tabla_apartamentos tbody`);

	let gas = fila["gas"];
	let agua = fila["agua"];
	let alquilado = fila["alquilado"];
	let porcentaje_participacion = fila["porcentaje_participacion"];

	// Formatear los valores como en la consulta SQL
	let gas_texto = (gas == 1) ? 'TIENE' : 'NO TIENE';
	let agua_texto = (agua == 1) ? 'TIENE' : 'NO TIENE';
	let alquilado_texto = (alquilado == 1) ? 'SI' : 'NO';
	let porcentaje_formateado = porcentaje_participacion + '%';

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");

	let id_campo = fila["id_apartamento"];
	
	let nro_apartamento_td = document.createElement("td"),
	porcentaje_participacion_td = document.createElement("td"),	
	gas_td = document.createElement("td"), 
	agua_td = document.createElement("td");
    alquilado_td = document.createElement("td");

	nro_apartamento_td.textContent = "Nro: " + fila["nro_apartamento"];
	porcentaje_participacion_td.textContent = porcentaje_formateado;
	gas_td.textContent = gas_texto;
	agua_td.textContent = agua_texto;
    alquilado_td.textContent = alquilado_texto;

	let acciones = crearBotones(id_campo); 

	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(nro_apartamento_td);
	fila_tabla.appendChild(porcentaje_participacion_td);
	fila_tabla.appendChild(gas_td);
	fila_tabla.appendChild(agua_td);
	fila_tabla.appendChild(alquilado_td);
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

	// BOTON DE VISTA PREVIA CON EL OJITO
    let boton_vista_previa = document.createElement("button");
    let icono_ver = document.createElement("i");
    icono_ver.setAttribute("class", "bi bi-people-fill");
    boton_vista_previa.appendChild(icono_ver);
    boton_vista_previa.setAttribute("type", "button");
    boton_vista_previa.setAttribute("class", "btn btn-primary btn-sm col-3 vista_previa");
    boton_vista_previa.setAttribute("title", "Detalles Apartamento");
    boton_vista_previa.setAttribute("value", id);
    // boton_vista_previa.addEventListener("click", mostrarVistaPrevia);
    acciones.appendChild(boton_vista_previa);
	// ...

	// Boton de Editar
	let boton_editar = document.createElement("button");
	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square")
	boton_editar.appendChild(icono_editar);
	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-3 editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_apartamentos");
	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);
	// boton_editar.addEventListener("click",modificar_formulario)
	//...

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

async function mostrarVistaPrevia(e) {
    let id = e.target.closest("button").getAttribute("value");

    let datos_consulta = new FormData();
    datos_consulta.append("id_apartamento", id);
    datos_consulta.append("operacion", "consulta_especifica");

    let data = await query(datos_consulta);

	id_apartamento_seleccionado = data.apartamento.id_apartamento;
	
	await consultar_habitantes(data.apartamento.id_apartamento);

    modalVistaPrevia.show();
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este apartamento?",
		showCancelButton: true,
		confirmButtonText: "Si, Eliminar",
		confirmButtonColor: "#e01d22",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((resultado) => {
		if (resultado.isConfirmed) {
			eliminar(boton_eliminar.value);				
		}
	});
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

	tabla_apartamentos.ajax.reload();

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

	data = await query(datos_consulta,true);
	
	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento"),
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion"),	
	gas = formulario_usar.querySelector("#gas"),	
	agua = formulario_usar.querySelector("#agua");
    alquilado = formulario_usar.querySelector("#alquilado");

	nro_apartamento.value = data.apartamento.nro_apartamento;
	porcentaje_participacion.value = data.apartamento.porcentaje_participacion;	
	gas.value = data.apartamento.gas;
	agua.value = data.apartamento.agua;
    alquilado.value = data.apartamento.alquilado;

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}

	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.apartamento.id_apartamento);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Apartamento";

	nro_apartamento_an = nro_apartamento.value;
}

async function modificar(id) {
	let datos_consulta = new FormData();

	let nro_apartamento = formulario_usar.querySelector("#nro_apartamento").value,
	porcentaje_participacion = formulario_usar.querySelector("#porcentaje_participacion").value,
	gas = Number(formulario_usar.querySelector("#gas").value), 
	agua = Number(formulario_usar.querySelector("#agua").value),
    alquilado = Number(formulario_usar.querySelector("#alquilado").value);

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
	
	datos_consulta.append('operacion','modificar');

	let respuesta = await query(datos_consulta,true);

	formulario_usar.reset();
 	modal.hide();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";

	document.getElementById('titulo_modal').textContent = "Registrar Apartamento";

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');

	tabla_apartamentos.ajax.reload();
}

async function last_id() {
	datos_consulta = new FormData()
	datos_consulta.append('operacion','ultimo_id');
	let res = await query(datos_consulta);
	return res;
}

async function query(datos,oscuro = false) {
    if (oscuro) {document.getElementById('icono_carga').setAttribute("class",`loader_dark`);}
    else{document.getElementById('icono_carga').setAttribute("class",`loader`);}

	let mostrarModal = false;
    let tiempoCarga;

	tiempoCarga = setTimeout(()=>{
		mostrarModal = true;
		modal_carga.show();
	}, 300);
	
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
	confirmButtonText:'Aceptar',
	confirmButtonColor: "#e01d22",
	});
}

function crearDataTable(id_tabla,estructura_filas,datos_paramentros, configuraciones_post_creacion = ()=>{}){
	return new DataTable(`#${id_tabla}`,{
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
        },
        "ajax": {
            "url": "",
            "dataSrc": "",
            "type": "POST", // Especifica el método de la petición
            "data": datos_paramentros
        },
        "columns":estructura_filas,
        "drawCallback": function( settings ) {            
            $(this).DataTable().columns.adjust();
        },
        "error": function(jqXHR, textStatus, errorThrown) {            
            console.log(jqXHR,textStatus,errorThrown)
        },
        "createdRow": configuraciones_post_creacion
	});
}
