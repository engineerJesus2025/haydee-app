let id_modificar, correo_an;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let boton_formulario = document.querySelector("#boton_formulario"); 
let modal = new bootstrap.Modal("#modal_usuario");
let modal_carga = new bootstrap.Modal("#modal_carga");
let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let tiempoInicio;

let formulario_usar = document.querySelector(`#form_usuario`); 
let tabla_usuarios;

consultar();

document.querySelector(`#modal_usuario`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar Usuario";		
	
	formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Confirmar Contraseña";
	formulario_usar.querySelector("#confir_contra").placeholder = "Confirmar Contraseña";
	formulario_usar.querySelector("#contra").placeholder = "Contraseña";

	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
	document.querySelectorAll('input').forEach(input=>{
		if (input.id.includes('contra')) {
			input.nextElementSibling.classList.remove('border-danger');
			input.nextElementSibling.classList.remove('text-danger');
			input.nextElementSibling.classList.remove('border-success');
			input.nextElementSibling.classList.remove('text-success');

			input.nextElementSibling.firstElementChild.classList.replace('bi-eye-slash','bi-eye');
		}
	});
});

document.querySelectorAll('.contra').forEach(boton=>{
	boton.addEventListener('click',e=>{
		e.preventDefault();

		let i;
		if (e.target.firstElementChild == null) {
			i = e.target;
		}
		else{
			i = e.target.firstElementChild;
		}

		if (i.classList.contains('bi-eye')){
			i.parentElement.previousElementSibling.setAttribute('type','text')
			i.classList.replace('bi-eye','bi-eye-slash');
		}
		else{
			i.parentElement.previousElementSibling.setAttribute('type','password')
			i.classList.replace('bi-eye-slash','bi-eye');
		}
	});
});

document.getElementById('header-toggle').addEventListener("click",e=>{
	setTimeout(function(){
		tabla_usuarios.columns.adjust().draw();
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
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

function eventosCargaDataTable(id_tabla,modal){	
	$('#'+id_tabla).on("preXhr.dt",function (e, settings, data) {
		peticionesActivas++;

		tiempoInicio = performance.now();

		ultimaPeticion = tiempoInicio;

		if (peticionesActivas === 1) {
			tiempoCarga = setTimeout(()=>{
				modal_carga.show();
			}, 300);
		}
	});

	$('#'+id_tabla).on("xhr.dt",function (e, settings, json, xhr) {
		peticionesActivas--;

		if (peticionesActivas === 0) {
			const espera = 50;
			setTimeout(()=>{
				if (peticionesActivas === 0) {
					clearTimeout(tiempoCarga);

					const tiempoTranscurido = performance.now() - tiempoInicio;
					const tiempoEsperaMin = 700;

					if (tiempoTranscurido < tiempoEsperaMin) {
						const restante = tiempoEsperaMin - tiempoTranscurido;
						setTimeout(()=>{
							if (performance.now() - ultimaPeticion >= restante) {
								modal_carga.hide();
							}
						},restante);
					}
					else{
						modal_carga.hide();
					}
				}
			}, espera);
		}
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

function crearBotones(id) {
	let td = document.createElement("td");
	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");

	let boton_editar = document.createElement("button");

	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square")
	boton_editar.appendChild(icono_editar);

	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-lg-3 col-4 editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_usuario");

	boton_editar.setAttribute("title","Editar");
	boton_editar.setAttribute("value",id);	

	acciones.appendChild(boton_editar);

	if (permiso_eliminar) {
		let boton_eliminar = document.createElement("button");

		let icono_eliminar = document.createElement("i");
		icono_eliminar.setAttribute("class", "bi bi-trash");
		boton_eliminar.appendChild(icono_eliminar);
		
		boton_eliminar.setAttribute("type", "button");
		boton_eliminar.setAttribute("class", "btn btn-danger btn-sm col-lg-3 col-4 eliminar");
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

async function consultar() {
	eventosCargaDataTable('tabla_usuario',modal_carga);

	const paramentros_consulta = (data)=>{data.operacion = 'consulta';}
	const estructura_tabla_usuarios = [
 		{
 			"data": null,
            "render": function (data, type, row) {            	
                return `${row.nombre_usuario}`;
            }  
        },
		{ 
			"data": null, 
			"render": function (data, type, row) {                
                return `${row["apellido"]}`;
            }
        },
        { 
            "data": null, 
            "render": function (data, type, row) {
            	return `${row["correo"]}`;
            }
        },
		{ 
            "data": null,
            "render": function (data, type, row) {
            	return `${row["nombre_rol"]}`;
            }
        },      
        { 
            "data": null, 
            "render": function (data, type, row) {
            	let id_campo = row["id_usuario"];
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	];

 	const configuraciones_tabla_usuarios = (row, data, dataIndex)=>{
 		Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));
 		 		
		row.setAttribute("id",`fila-${data.id_usuario}`); 		
 		row.querySelector(".editar")?.addEventListener('click',preparar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar);
 	}

 	tabla_usuarios = crearDataTable('tabla_usuario',estructura_tabla_usuarios,paramentros_consulta,configuraciones_tabla_usuarios);
}

async function registrar() {
	let datos_consulta = new FormData();
	
	let nombre = formulario_usar.querySelector("#nombre").value,
	apellido = formulario_usar.querySelector("#apellido").value,	
	correo = formulario_usar.querySelector("#correo").value, 
	contra = formulario_usar.querySelector("#contra").value, 
	rol = formulario_usar.querySelector("#rol").value,
	rol_nombre = formulario_usar.querySelector("#rol").selectedOptions[0].textContent;

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);	
	datos_consulta.append("correo",correo);
	datos_consulta.append("contra",contra);
	datos_consulta.append("rol",rol);

	datos_consulta.append('operacion','registrar');
	
	let respuesta = await query(datos_consulta);

	modal.hide();
	formulario_usar.reset();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_usuarios.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function preparar_formulario(e) {
	datos_consulta = new FormData();
		
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value; 
	}
	datos_consulta.append("id_usuario",id);

	datos_consulta.append('operacion','consulta_especifica');

	data = await query(datos_consulta);	
	
	let nombre = formulario_usar.querySelector("#nombre"),
	apellido = formulario_usar.querySelector("#apellido"),	
	correo = formulario_usar.querySelector("#correo"),	
	rol = formulario_usar.querySelector("#rol");	

	nombre.value = data.nombre_usuario;
	apellido.value = data.apellido;	
	correo.value = data.correo;
	rol.value = data.rol_id;	

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}
	
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_usuario);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar Usuario";
	formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Nueva Contraseña" 
	formulario_usar.querySelector("#confir_contra").placeholder = "Escriba su Nueva Contraseña" 
	formulario_usar.querySelector("#contra").placeholder = "Escriba su Contraseña";

	id_modificar = id;
	correo_an = correo.value;
}

async function modificar(id) {	
	let datos_consulta = new FormData();

	let nombre = formulario_usar.querySelector("#nombre").value,
	apellido = formulario_usar.querySelector("#apellido").value,
	correo = formulario_usar.querySelector("#correo").value, 	
	nueva_contra = formulario_usar.querySelector("#confir_contra").value || formulario_usar.querySelector("#contra").value,
	rol = formulario_usar.querySelector("#rol").value,
	rol_nombre = formulario_usar.querySelector("#rol").selectedOptions[0].textContent;

	datos_consulta.append("id_usuario",id);

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);	
	datos_consulta.append("correo",correo);
	datos_consulta.append("contra",nueva_contra);
	datos_consulta.append("rol",rol);

	datos_consulta.append('operacion','editar_usuario');

	let respuesta = await query(datos_consulta);

	formulario_usar.reset();
 	modal.hide();

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";

	document.getElementById('titulo_modal').textContent = "Registrar Usuario";

	tabla_usuarios.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este Usuario?",
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

	datos_consulta.append("id_usuario",id);

	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_usuarios.ajax.reload();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

async function query(datos) {
	let tiempoCarga = setTimeout(()=>{
		modal_carga.show();
	}, 100);
	
	try{
		const tiempoInicio = performance.now();

		let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;
		});

		const tiempoTranscurido = performance.now() - tiempoInicio;
		const tiempoEsperaMin = 500;

		if (tiempoTranscurido < tiempoEsperaMin) {
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
		modal_carga.hide();
	}
}