let tabla_notificaciones;
let modal_carga = new bootstrap.Modal("#modal_carga");

window.addEventListener('DOMContentLoaded',()=>{
  eventosCargaDataTable('tabla_notificaciones',modal_carga);
  consultar();  
});

document.getElementById('header-toggle').addEventListener("click",e=>{
    setTimeout(function(){
        tabla_notificaciones.columns.adjust().draw();
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

function formatearFechaHora(fechaHoraStr) {  
  const fecha = new Date(fechaHoraStr);

  const dia = String(fecha.getDate()).padStart(2, '0');
  const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Se suma 1 porque los meses van de 0 a 11
  const anio = fecha.getFullYear();  
  
  return `${dia}-${mes}-${anio}`;
}

async function consultar() {
  const paramentros_consulta = (data)=>{data.operacion = 'consultar';}
  const estructura_tabla_notificaciones = [
    {
      "data": null,
            "render": function (row) {              
                return `${row["nombre"]}`;
            }  
        },
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
        }
  ];

  const configuraciones_tabla_notificaciones = (row)=>{
    Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));
  }

  tabla_notificaciones = crearDataTable('tabla_notificaciones',estructura_tabla_notificaciones,paramentros_consulta,configuraciones_tabla_notificaciones);
}