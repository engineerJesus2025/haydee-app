let correo_an;

let tabla_notificaciones;
//Eventos:
window.addEventListener('DOMContentLoaded',()=>{
	llenarCardUsuario();
	llenarTablaNotificaciones();
});

document.querySelector(`#modal_contra`).addEventListener("hide.bs.modal",()=>{
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
	correo_an = null;
	document.querySelectorAll('input').forEach(input=>{
		if (input.id.includes('contra')) {
			input.nextElementSibling.classList.remove('border-danger');
			input.nextElementSibling.classList.remove('text-danger');
			input.nextElementSibling.classList.remove('border-success');
			input.nextElementSibling.classList.remove('text-success');

			input.nextElementSibling.firstElementChild.classList.replace('bi-eye-slash','bi-eye');
		}
		input.value = '';
	});
});

// Efecto de mostrar/ocultar la constraseña
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

document.getElementById('boton_editar').addEventListener('click',e=>{
	document.getElementById("nombre").value = document.getElementById("p_nombre").textContent;
	document.getElementById("apellido").value = document.getElementById("p_apellido").textContent;
	document.getElementById("correo").value = document.getElementById("p_correo").textContent;

	document.getElementById('body_perfil').setAttribute('hidden','');
	document.getElementById('form_perfil').removeAttribute('hidden');

	document.getElementById('boton_editar').setAttribute('disabled','');
});

document.getElementById('boton_cancelar').addEventListener('click',e=>{
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));

	document.getElementById('form_perfil').setAttribute('hidden','');
	document.getElementById('body_perfil').removeAttribute('hidden');

	document.getElementById('boton_editar').removeAttribute('disabled','');
});

document.getElementById('modal_notificaciones').addEventListener('shown.bs.modal', function () {
    if ($.fn.DataTable.isDataTable("#tabla_notificaciones")) {
        $('#tabla_notificaciones').DataTable().columns.adjust().draw();
    }
});


async function llenarCardUsuario(){
	let datos_consulta = new FormData();

	datos_consulta.append('operacion','consultar_perfil_usuario');

	let usuario = await query(datos_consulta);
	
	let [clases_badge_rol,clases_icono_rol] = definirColorBadge(usuario.nombre_rol);

	document.getElementById("titulo_nombre").textContent = `${usuario.nombre_usuario} ${usuario.apellido}`
	document.getElementById("titulo_rol").textContent = usuario.nombre_rol;
	document.getElementById("p_nombre").textContent = usuario.nombre_usuario;
	document.getElementById("p_apellido").textContent = usuario.apellido;
	document.getElementById("p_correo").textContent = usuario.correo;
	document.getElementById("spam_rol").textContent = usuario.nombre_rol;
	
	document.getElementById("ultimo_acceso").textContent = formatearUltimoAcceso(usuario.ultima_vez);

	correo_an = usuario.correo;

	let icono_rol = document.createElement("i");
	icono_rol.setAttribute('class',clases_icono_rol);
	icono_rol.setAttribute('style',"font-size: 4rem !important;");

	document.getElementById("titulo_icono").textContent = null;
	document.getElementById("titulo_icono").appendChild(icono_rol);	

	document.getElementById("spam_rol").setAttribute('class',clases_badge_rol);

	document.getElementById('boton_editar').removeAttribute('disabled');
	document.getElementById('boton_editar').innerHTML = `<i class="bi bi-pencil me-1"></i>Editar`;
}

function llenarTablaNotificaciones() {
  const paramentros_consulta = (data)=>{data.operacion = 'consultar_notificaciones_usuario';}
  const estructura_tabla_notificaciones = [
    {	
		"data": null,
      	"render": function (row) {                
        	return `${row["titulo"]}`;
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
        	return `${formatearFechaHora(row["fecha"])}`;
        }
    },
    {
        "data": null,
        "render": function (row) {
        	let spam = document.createElement("span");
            spam.setAttribute("class",(row["activo"] == 1)?"badge bg-primary":"badge bg-warning text-dark");
            spam.textContent = (row["activo"] == 1) ? "SI" : "NO";
        	return `${spam.outerHTML}`;
        }
    },
	{
        "data": null,
        "render": function (row) {
        	let botonVerNotificacion = document.createElement("button");
			botonVerNotificacion.setAttribute("class","btn btn-sm btn-primary");
			botonVerNotificacion.setAttribute("title","Ver Notificación");
			botonVerNotificacion.setAttribute("type","button");
			botonVerNotificacion.setAttribute("data-bs-toggle","tooltip");
			botonVerNotificacion.setAttribute("data-nombre_modulo",row["nombre_modulo"]);
			botonVerNotificacion.setAttribute("data-referencia",row["referencia"]);
			let iconoVer = document.createElement("i");
			iconoVer.setAttribute("class","bi bi-eye");
			botonVerNotificacion.appendChild(iconoVer);
        	return `${botonVerNotificacion.outerHTML}`;
        }
    }
  ];

  const configuraciones_tabla_notificaciones = (row)=>{
    Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));

	// seleccionamos el boton de la ultima columna
	let botonVer = row.children[row.children.length - 1].firstElementChild;
	botonVer.parentElement.setAttribute('class','align-middle text-center');

	botonVer.addEventListener('click',()=>{
		//Tal vez incluir un mensaje de confirmacion de redireccionamiento				
		let url = `?pagina=${botonVer.dataset.nombre_modulo}_controlador.php&accion=inicio&referencia=${botonVer.dataset.referencia}`;
		window.location.href = url;
	});
  }

  tabla_notificaciones = crearDataTable('tabla_notificaciones',estructura_tabla_notificaciones,paramentros_consulta,configuraciones_tabla_notificaciones);

  document.getElementById('notificaciones').removeAttribute('disabled');
}

function definirColorBadge(nombre_rol){
	switch (nombre_rol){
		case 'Administrador Global':
			return ["badge bg-warning text-dark","bi bi-globe me-3"];
		case 'Administrador':
			return ["badge bg-primary","bi bi-person-fill-gear me-3"];
		case 'Propietario':
			return ["badge bg-success","bi bi-key-fill me-3"];
		case 'Contador':
			return ["badge bg-danger","bi bi-calculator-fill me-3"];
		case 'Presidente':
			return ["badge bg-info text-dark","bi bi-award-fill me-3"];
		default:
		return ["badge bg-secondary","bi bi-person-circle me-3"];
	}
}

function formatearUltimoAcceso(fecha) {
	const ahora = new Date();
	if(!fecha){
		return ahora.toLocaleDateString('es-ES');
	}
    const fechaISO = fecha.replace(" ", "T");
    const fechaAcceso = new Date(fechaISO);
    

    const diffMs = ahora - fechaAcceso;
    const diffDias = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    let hora = fechaAcceso.getHours();
    const minutos = fechaAcceso.getMinutes().toString().padStart(2, '0');
    const amPm = hora >= 12 ? 'p.m.' : 'a.m.';
    
    hora = hora % 12 || 12; // Convierte 0 a 12
    const horaFormateada = `${hora}:${minutos}${amPm}`;
    
    if (diffDias === 0) {
        return `Hoy a las ${horaFormateada}`;
    } else if (diffDias === 1) {
        return `Ayer a las ${horaFormateada}`;
    } else if (diffDias <= 7) {
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return `El ${diasSemana[fechaAcceso.getDay()]} a las ${horaFormateada}`;
    } else {
        return fechaAcceso.toLocaleDateString('es-ES') + ` a las ${horaFormateada}`;
    }
}

function formatearFechaHora(fechaHoraStr) {
  const fecha = new Date(fechaHoraStr);
  
  const dia = String(fecha.getUTCDate()).padStart(2, '0');
  const mes = String(fecha.getUTCMonth() + 1).padStart(2, '0');
  const anio = fecha.getUTCFullYear();  
  
  return `${dia}-${mes}-${anio}`;
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
            "type": "POST", 
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

async function modificar() {
	let datos_consulta = new FormData();

	let nombre = document.querySelector("#nombre").value,
	apellido = document.querySelector("#apellido").value,
	correo = document.querySelector("#correo").value;	

	datos_consulta.append("nombre",nombre);
	datos_consulta.append("apellido",apellido);	
	datos_consulta.append("correo",correo);

	datos_consulta.append('operacion','editar_perfil');

	let respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	document.querySelectorAll('input').forEach(input=>input.value = '');

	let boton_accion_usuario = document.getElementById('boton_accion_usuario');

	boton_accion_usuario.textContent = boton_accion_usuario.textContent.replace(boton_accion_usuario.textContent.trim().split(" ")[1],nombre);

 	llenarCardUsuario();

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');

	document.getElementById('form_perfil').setAttribute('hidden','');
	document.getElementById('body_perfil').removeAttribute('hidden');
	document.getElementById('boton_editar').removeAttribute('disabled','');
}

async function modificarContra() {
	let datos_consulta = new FormData();

	let nueva_contra = document.getElementById("contra").value;	
	let correo = document.getElementById("p_correo").textContent;

	datos_consulta.append("contra",nueva_contra);
	datos_consulta.append("correo",correo);

	datos_consulta.append('operacion','cambiar_contrasenia');

	let respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	document.querySelectorAll('input').forEach(input=>input.value = '');

	mensajes('success',4000,'Atencion','La contraseña se ha modificado exitosamente');
	
	modal.hide();
}

async function query(datos,oscuro = false) {
    if (oscuro) {document.getElementById('icono_carga').setAttribute("class",`loader_dark`);}
    else{document.getElementById('icono_carga').setAttribute("class",`loader`);}
    
	try{
		const res = await fetch("", { method: "POST", body: datos });
    	const data = await res.json();

		return data;
	}
	catch(error){
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
}

