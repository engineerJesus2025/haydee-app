let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#modal_anio_fiscal");
let modal_carga = new bootstrap.Modal("#modal_carga");
let formulario_usar = document.querySelector(`#form_anio_fiscal`); 
let tabla_anio_fiscal;

consultar();

document.querySelector(`#modal_anio_fiscal`).addEventListener("hidden.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Año Fiscal";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");
	formulario_usar.querySelector("#fecha_cierre").setAttribute("disabled","");
	formulario_usar.querySelector("#estado").setAttribute("disabled","");

	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
});

document.getElementById('header-toggle').addEventListener("click",e=>{
	setTimeout(function(){
		tabla_anio_fiscal.columns.adjust().draw();
	},450);
});

function envio(operacion) {	
	if (operacion == "Editar") {
		let id_modificar = boton_formulario.getAttribute("id_modificar");
		modificar(id_modificar);
	}
	else if(operacion == "Registrar"){
		registrar();
	}else{
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente');
	}
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
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-3 editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_anio_fiscal");

	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);	
	
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

function eventosCargaDataTable(id_tabla,modal){
	const tiempoMinimoCarga = 700; // 500 milisegundos
    let inicioPeticion;
    let temporizadorModal;
  	let modalVisible = false;

	$('#'+id_tabla).on("preXhr.dt",function (e, settings, data) {
		inicioPeticion = new Date().getTime();

		temporizadorModal = setTimeout(() => {
	      modal.show();
	      modalVisible = true;
	    }, 200);
	});

	$('#'+id_tabla).on("xhr.dt",function (e, settings, json, xhr) {
		clearTimeout(temporizadorModal);

		const finPeticion = new Date().getTime();
    	const tiempoTranscurrido = finPeticion - inicioPeticion;

		if (modalVisible && tiempoTranscurrido < tiempoMinimoCarga) {            
            const  tiempoEspera = tiempoMinimoCarga - tiempoTranscurrido;
            setTimeout(function() {
            	modal.hide();
        		modalVisible = false;                
            }, tiempoEspera);
        } 
        else if (modalVisible) {
	      // Si el modal se hizo visible, pero ya se cumplió el tiempo mínimo, se oculta
	      modal.hide();
	      modalVisible = false;
	    }
    });
}

function formatearFecha(fecha) {
    if (!fecha) {
        return "Aún sin Cerrar";
    }

    const partes = fecha.split("-");
    if (partes.length === 3) {
        return `${partes[2]}-${partes[1]}-${partes[0]}`; // DD-MM-AAAA
    }
    return fecha;
}

async function consultar() {
	eventosCargaDataTable('tabla_anio_fiscal',modal_carga);

	const paramentros_consulta = (data)=>{data.operacion = 'consultar_anios_fiscales';}
	const estructura_tabla_anio_fiscal = [
 		{
 			"data": null,
            "render": function (row) {
            	let spam = document.createElement("span");
                spam.setAttribute("class",row.estado == "Cerrada"?"badge bg-secondary":"badge bg-primary");
                spam.textContent = row.estado;
            	return `${spam.outerHTML}`;                
            }
        },
		{ 
			"data": null, 
			"render": function (row) {                
                return `${formatearFecha(row["fecha_inicio"])}`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	return `${row.estado == "Cerrada"?formatearFecha(row["fecha_cierre"]):"Aún sin cerrar"}`;
            }
        },
		{ 
            "data": null,
            "render": function (row) {
            	return `${row["descripcion"]}`;
            }
        },   
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row["id_anio_fiscal"];
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	];

 	const configuraciones_tabla_anio_fiscal = (row, data)=>{
 		Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));
 		 		
		row.setAttribute("id",`fila-${data.id_anio_fiscal}`); 		
 		row.querySelector(".editar")?.addEventListener('click',modificar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar);
 	}

 	tabla_anio_fiscal = crearDataTable('tabla_anio_fiscal',estructura_tabla_anio_fiscal,paramentros_consulta,configuraciones_tabla_anio_fiscal);
}

async function registrar() {
	let datos_consulta = new FormData();
	
	let fecha_inicio = formulario_usar.querySelector("#fecha_inicio").value,
	fecha_cierre = formulario_usar.querySelector("#fecha_cierre").value,
	estado = formulario_usar.querySelector("#estado").value,	
	descripcion = formulario_usar.querySelector("#descripcion").value;	
	
	datos_consulta.append("fecha_inicio",fecha_inicio);
	datos_consulta.append("fecha_cierre",fecha_cierre);
	datos_consulta.append("estado",estado);	
	datos_consulta.append("descripcion",descripcion);	

	datos_consulta.append('operacion','registrar');
	
	let respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	modal.hide();
	
	tabla_anio_fiscal.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');//Mensaje de que se completo la operacion
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este presupuesto?",
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
	datos_consulta = new FormData();

	datos_consulta.append("id_anio_fiscal",id);

	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_anio_fiscal.ajax.reload();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

async function modificar_formulario(e) {

	datos_consulta = new FormData();
		
	let id = e.target.value; // tomamos el id
	if (id === undefined) {
		id = e.target.parentElement.value; 
		//esto es por si seleciona el icono en vez del boton al dar click
	}
	// le damos el id
	datos_consulta.append("id_anio_fiscal",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta_especifica');

	//Llamamos a la funcion para hacer la consulta y guardamos los datos
	anio_fiscal = await query(datos_consulta,true);	
	
	// ahora seleccionamos los inputs
	let fecha_inicio = formulario_usar.querySelector("#fecha_inicio"),
	fecha_cierre = formulario_usar.querySelector("#fecha_cierre"),
	estado = formulario_usar.querySelector("#estado"),
	descripcion = formulario_usar.querySelector("#descripcion");

	// le damos valor
	fecha_inicio.value = anio_fiscal.fecha_inicio;
	fecha_cierre.value = anio_fiscal.fecha_cierre;
	estado.value = anio_fiscal.estado;
	descripcion.value = anio_fiscal.descripcion;

	// este if revisa si tiene permiso para editar, en caso de que no, quitamos el boton
	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
		//si no los tiene apaga el boton.
	}

	// aqui cambiamos los datos del boton para registrar, para saber que ahora se va es a modificar un registro
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",anio_fiscal.id_anio_fiscal);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Año Fiscal";
	formulario_usar.querySelector("#fecha_cierre").removeAttribute("disabled");
	formulario_usar.querySelector("#estado").removeAttribute("disabled");

	id_modificar = id;
}

async function modificar(id) {
	let datos_consulta = new FormData();

	let fecha_inicio = formulario_usar.querySelector("#fecha_inicio").value,
	fecha_cierre = formulario_usar.querySelector("#fecha_cierre").value,
	estado = formulario_usar.querySelector("#estado").value,
	descripcion = formulario_usar.querySelector("#descripcion").value;

	datos_consulta.append("id_anio_fiscal",id);

	datos_consulta.append("fecha_inicio",fecha_inicio);
	datos_consulta.append("fecha_cierre",fecha_cierre);
	datos_consulta.append("estado",estado);	
	datos_consulta.append("descripcion",descripcion);

	datos_consulta.append('operacion','modificar');

	let respuesta = await query(datos_consulta,true);
	
 	modal.hide();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";

	document.getElementById('titulo_modal').textContent = "Registrar Año Fiscal";

	tabla_anio_fiscal.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');
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