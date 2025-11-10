let nombre_anterior;
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#modal_roles");
let modal_carga = new bootstrap.Modal("#modal_carga");
let formulario_usar = document.querySelector(`#form_rol`);
let input_permisos = document.querySelectorAll("[name='permisos[]']"); 
let tabla_roles;

consultar();

document.querySelector(`#modal_roles`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Rol";	
	formulario_usar.querySelectorAll("[class='w-100']").forEach(el=>el.textContent="");
	input_permisos.forEach(input=>{
		input.closest(".accordion-collapse").classList.remove("show");
		input.closest(".accordion-collapse").previousElementSibling.children[0].classList.add("collapsed");
		input.closest(".accordion-collapse").previousElementSibling.children[0].setAttribute("aria-expanded",false)		
	});

	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
});

document.getElementById('header-toggle').addEventListener("click",e=>{
	setTimeout(function(){
		tabla_roles.columns.adjust().draw();
	},450);
});

document.querySelectorAll(".seleccionar_todo").forEach(checkbox=>{
	checkbox.addEventListener("click",()=>{		
		checkbox.closest("tr").querySelectorAll(".form-check-input").forEach(checkbox_permisos=>{
			checkbox_permisos.checked = checkbox.checked;			
		});
	});
});

document.querySelectorAll("[name='permisos[]']").forEach(checkbox=>{
	checkbox.addEventListener("click",e=>{
		let alguno_inactivo = false;
		checkbox.closest(".row").querySelectorAll("input").forEach(checkbox_permisos=>{			
			if (!checkbox_permisos.checked) alguno_inactivo = true;
		});
		if (!alguno_inactivo) {
			checkbox.closest("tr").querySelector(".seleccionar_todo").checked = true;
		}else{
			checkbox.closest("tr").querySelector(".seleccionar_todo").checked = false;
		}
	});
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
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

function crearBotones(id) {
	let td = document.createElement("td");

	if (id == 1) {
		td.textContent = 'No Modificable';
		return td;
	}

	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");

	let boton_editar = document.createElement("button");
	
	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square")
	boton_editar.appendChild(icono_editar);

	boton_editar.appendChild(icono_editar);

	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success col-lg-2 col-sm-3 col-4 editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_roles");

	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);

	acciones.appendChild(boton_editar);

	if (permiso_eliminar) {
		let boton_eliminar = document.createElement("button");

		let icono_eliminar = document.createElement("i");
		icono_eliminar.setAttribute("class", "bi bi-trash");
		boton_eliminar.appendChild(icono_eliminar);
		
		boton_eliminar.appendChild(icono_eliminar);
		
		boton_eliminar.setAttribute("type", "button");
		boton_eliminar.setAttribute("class", "btn btn-danger col-lg-2 col-sm-3 col-4 eliminar");
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

async function consultar() {
	eventosCargaDataTable('tabla_roles',modal_carga);

	const paramentros_consulta = (data)=>{data.operacion = 'consulta';}
	const estructura_tabla_roles = [
 		{
 			"data": null,
            "render": function (row) {            	
                return `${row.nombre}`;
            }  
        }, 
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row["id_rol"];
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	];

 	const configuraciones_tabla_roles = (row, data)=>{
 		row.setAttribute("id",`fila-${data.id_rol}`); 		
 		row.querySelector(".editar")?.addEventListener('click',modificar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar);

 		Array.from(row.children).map(td=>{ 			
 			if (data.id_rol == 1 && row.lastElementChild == td) {
 				td.setAttribute("style","font-family: var(--bs-font-monospace) !important; text-align: center !important;");
 			}
 			else{
 				td.setAttribute("class",'align-middle');
 			}
 		}); 		 	
		
 	}

 	tabla_roles = crearDataTable('tabla_roles',estructura_tabla_roles,paramentros_consulta,configuraciones_tabla_roles);
}

async function registrar() {
	let datos_consulta = new FormData();
	
	let nombre = formulario_usar.querySelector("#nombre").value;
	
	datos_consulta.append("nombre",nombre);
	
	let permisos_selecionados = [];
	input_permisos.forEach(permiso=>{
		if (permiso.checked) {
			permisos_selecionados.push(parseInt(permiso.value));
		}
	})

	datos_consulta.append("permisos",permisos_selecionados);

	datos_consulta.append('operacion','registrar_rol');

	let respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	modal.hide();

	tabla_roles.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este rol?",
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

	datos_consulta.append("id_rol",id);

	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_roles.ajax.reload();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');
}

async function modificar_formulario(e) {
	let datos_consulta = new FormData();
	
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value; 
	}
	
	datos_consulta.append("id_rol",id);

	datos_consulta.append('operacion','consulta_especifica');

	let data = await query(datos_consulta,true);

	let nombre = formulario_usar.querySelector("#nombre");

	nombre.value = data.nombre;

	datos_consulta = new FormData();

	datos_consulta.append("id_rol",id);

	datos_consulta.append('operacion','consulta_permisos');

	let data_permisos = await query(datos_consulta);	
	
	data_permisos.map(permisos=>{
		input_permisos.forEach(input=>{
			if (permisos.permiso_usuario_id == input.value) {
				input.checked = true;
				input.closest(".accordion-collapse").classList.add("show");
				input.closest(".accordion-collapse").previousElementSibling.children[0].classList.remove("collapsed");
				input.closest(".accordion-collapse").previousElementSibling.children[0].setAttribute("aria-expanded",true)
			}
		})
	})
	
	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}

	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_rol);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Rol";
	
	nombre_anterior = nombre.value; 
}

async function modificar(id) {	
	let datos_consulta = new FormData();

	let nombre = formulario_usar.querySelector("#nombre").value;	
	
	datos_consulta.append("id_rol",id);
	datos_consulta.append("nombre",nombre);

	let permisos_selecionados = [];
	input_permisos.forEach(permiso=>{
		if (permiso.checked) {
			permisos_selecionados.push(parseInt(permiso.value));
		}
	})

	datos_consulta.append("permisos",permisos_selecionados);

	datos_consulta.append('operacion','modificar');

	let respuesta = await query(datos_consulta,true);
 	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}
	modal.hide();

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";

	document.getElementById('titulo_modal').textContent = "Registrar Rol";

	tabla_roles.ajax.reload();

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