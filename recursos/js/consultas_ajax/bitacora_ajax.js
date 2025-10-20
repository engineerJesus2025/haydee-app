let tabla_bitacora;
let modal_carga = new bootstrap.Modal("#modal_carga");

window.addEventListener('DOMContentLoaded',()=>{
	eventosCargaDataTable('tabla_bitacora',modal_carga);
	consultar();	
});

document.getElementById('header-toggle').addEventListener("click",e=>{
    setTimeout(function(){
        tabla_bitacora.columns.adjust().draw();
    },450);
});

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

function formatearFechaHora(fechaHoraStr) {
  // 1. Crear un objeto de fecha a partir del texto
  const fecha = new Date(fechaHoraStr);

  // 2. Obtener las partes de la fecha y la hora
  let horas = fecha.getHours();
  let minutos = fecha.getMinutes();
  let segundos = fecha.getSeconds();
  
  const dia = String(fecha.getDate()).padStart(2, '0');
  const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Se suma 1 porque los meses van de 0 a 11
  const anio = fecha.getFullYear();

  // 3. Convertir la hora a formato de 12 horas y determinar AM/PM
  const ampm = horas >= 12 ? 'PM' : 'AM';
  horas = horas % 12;
  horas = horas ? horas : 12; // La hora '0' debe ser '12'

  // 4. Asegurar que los componentes de la hora tengan dos dígitos
  const horasFormateadas = String(horas).padStart(2, '0');
  const minutosFormateados = String(minutos).padStart(2, '0');
  const segundosFormateados = String(segundos).padStart(2, '0');

  // 5. Unir todo en el formato final
  return `${horasFormateadas}:${minutosFormateados}:${segundosFormateados} ${ampm} ${dia}-${mes}-${anio}`;
}

function definirColorAccion(nombre_accion){
    switch (nombre_accion){
        case 'consultar':
            return "badge bg-info text-dark";
            break;
        case 'eliminar':
            return "badge bg-danger";
            break;
        case 'registrar':
            return "badge bg-primary";
            break;

        case 'modificar':
            return "badge bg-success";
            break;
        case 'iniciar sesion':
            return "badge bg-warning text-dark";
            break;
        case 'cerrar sesion':
            return "badge bg-secondary";
            break;
        default:
        return "badge bg-secondary";
        break;
    }
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

	const paramentros_consulta = (data)=>{data.operacion = 'consultar';}
	const estructura_tabla_bitacora = [
 		{
 			"data": null,
            "render": function (data, type, row) {            	
                return `${row.nombre_usuario}`;
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
            	return `${formatearFechaHora(row["fecha_hora"])}`;
            }
        },
		{ 
            "data": null,
            "render": function (data, type, row) {
            	return `${row["nombre_modulo"].split("_").join(" ")}`;
            }
        },
        { 
            "data": null,
            "render": function (data, type, row) {
                let spam = document.createElement("span");
                spam.setAttribute("class",definirColorAccion(row["accion"]));
                spam.textContent = row["accion"];
            	return `${spam.outerHTML}`;
            }
        },
        { 
            "data": null,
            "render": function (data, type, row) {
            	return `${row["registro_alterado"]}`;
            }
        }
 	];

 	const configuraciones_tabla_bitacora = (row, data, dataIndex)=>{
 		Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));
 	}

 	tabla_bitacora = crearDataTable('tabla_bitacora',estructura_tabla_bitacora,paramentros_consulta,configuraciones_tabla_bitacora);
}