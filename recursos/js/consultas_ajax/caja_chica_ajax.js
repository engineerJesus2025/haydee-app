// reportes estadiscticos y dashboar en inicio
consultarCajasChicas();
// Creacion de Variables
let fecha_seleccionada = null, data_table = null, referencia_an = null;

//guardamos los permisos del usuario
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
let permiso_registrar = document.querySelector("#permiso_registrar").value;

//guardamos las tablas que usaremos
let tabla_registros_sistema = document.querySelector("#tabla_registros_sistema");
let tabla_resumen = document.querySelector("#tabla_resumen");

// Utilties
let total_ingresos = 0, total_egresos = 0, saldo_inicial = 0;
let observaciones = {};
let modal_observacion = new bootstrap.Modal(document.querySelector("#modal_observaciones"));

//Eventos:
//Asignamos el evento select para que cada que cambie llene la tabla de registros del sistema
document.getElementById("mes_select").addEventListener("change",e=>{	
	total_egresos = 0;
	total_ingresos = 0;

	saldo_inicial = e.target.options[e.target.selectedIndex].getAttribute("saldo_inicial");	
	fecha_selecc = e.target.value;

	document.getElementById("observaciones").textContent = observaciones[fecha_selecc];	
	document.getElementById("observaciones").closest(".col-7").removeAttribute("hidden");

	llenarTablaRegistrosSistema(fecha_selecc);
	
	if (e.target.options[e.target.selectedIndex].getAttribute("activa") == "Cerrada"){
		document.getElementById("span_select").setAttribute("class","text-danger");
		document.getElementById("span_select").textContent = "Esta Caja esta cerrada";
	}else{
		document.getElementById("span_select").setAttribute("class","text-success");
		document.getElementById("span_select").textContent = "Esta es la caja actual";
	}
});

document.getElementById("boton_editar_observacion").addEventListener("click",e=>{	
	document.getElementById("observacion_input").value = document.getElementById("observaciones").textContent;
});

//Funciones:
async function consultarCajasChicas() {
	let datos_consulta = new FormData();
	datos_consulta.append("operacion","consultar_cajas_chicas");
	let cajas_chicas = await query(datos_consulta);

	let span_select = document.querySelector("#span_select");

	if (cajas_chicas.length == 0) {
		span_select.textContent = `No hay caja registradas`;
		return;
	}	

	let select = document.querySelector("#mes_select");
	let fragment = document.createDocumentFragment();

	cajas_chicas.map(caja=>{
		let option = document.createElement("option");
		fecha = new Date(caja.fecha_apertura);
		mes_buscar = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
		anio_buscar = fecha.getFullYear();

		option.textContent = `${mes_buscar} del ${anio_buscar}`;
		option.value = `${caja.fecha_apertura}`;
		option.id = caja.id_caja_chica;
		option.setAttribute("saldo_actual",caja.saldo_actual);
		option.setAttribute("saldo_inicial",caja.monto_inicial);
		option.setAttribute("activa",caja.estado);

		fragment.appendChild(option);

		observaciones[caja.fecha_apertura] = caja.observaciones;//parece fumada pero sirve
	});
	select.appendChild(fragment);

	if (select.value != "") {
		document.getElementById("observaciones").textContent = observaciones[select.value];
	}
}

async function llenarTablaRegistrosSistema(fecha_seleccionada){
	let datos_consulta = new FormData();

	datos_consulta.append("operacion","buscar_mes");
	datos_consulta.append("fecha",fecha_seleccionada);

	let resultados = await query(datos_consulta);

	let tabla = document.querySelector("#tabla_registros_sistema tbody");

	// console.log(tabla, resultados);

	let fragment = document.createDocumentFragment();

	resultados.map(resultado=>{
		if (resultado.movimiento == "Ingreso") {
			total_ingresos += parseInt(resultado.monto);
		}else{
			total_egresos += parseInt(resultado.monto);
		}

		let fila = document.createElement("tr");		

		let ingreso_egreso_td = document.createElement("td");
		let fecha_td = document.createElement("td");
		let monto_td = document.createElement("td");
		let apartamento_proveedor_td = document.createElement("td");

		ingreso_egreso_td.setAttribute("class","text-center");
		fecha_td.setAttribute("class","text-center");
		monto_td.setAttribute("class",`text-center ${(resultado.movimiento == "Ingreso")?"text-success":"text-danger"}`);
		
		ingreso_egreso_td.textContent = resultado.movimiento;
		fecha_td.textContent = resultado.fecha;
		monto_td.textContent = resultado.monto + "Bs.";
		apartamento_proveedor_td.textContent = resultado.remitente;

		fila.appendChild(ingreso_egreso_td);
		fila.appendChild(fecha_td);
		fila.appendChild(monto_td);
		fila.appendChild(apartamento_proveedor_td);

		fragment.appendChild(fila);
	});

	tabla.textContent = null;
	tabla.appendChild(fragment);

	if (!data_table) {
		data_table = await init_data_table();
	}
	else{
		data_table.draw(); // glu glu *Se refresca*
	}

	llenarTablaResumen();
}

function llenarTablaResumen(){
	tabla_resumen.children[2].children[0].children[1].textContent = parseInt(total_ingresos) + "Bs.";
	tabla_resumen.children[2].children[1].children[1].textContent = parseInt(total_egresos) + "Bs.";
	tabla_resumen.children[3].children[0].children[1].textContent = `${parseInt(saldo_inicial) + parseInt(total_ingresos - total_egresos)}Bs.`;

	ordenarTablaPorFecha(tabla_registros_sistema,1)

	tabla_resumen.closest(".card").removeAttribute("hidden","");
									// Que chulada el ->  ?. 
	// document.getElementById("boton_registrar_conciliacion")?.parentElement.removeAttribute("hidden");
	// document.getElementById("observaciones").closest(".col-5").removeAttribute("hidden");
}

function ordenarTablaPorFecha(tabla,columna) {
  const filas = Array.from(tabla.querySelectorAll('tr')).slice(1); // Excluye el encabezado
  const ordenadas = filas.sort((filaA, filaB) => {
    const fechaA = new Date(filaA.cells[columna].textContent);
    const fechaB = new Date(filaB.cells[columna].textContent);

    if (fechaA < fechaB) {
      return -1;
    }
    if (fechaA > fechaB) {
      return 1;
    }
    return 0;
  });

  const tbody = tabla.querySelector('tbody');
  tbody.innerHTML = '';

  ordenadas.forEach(fila => {
    tbody.appendChild(fila);
  });
}

async function editarObservacion(id_caja) {	
	let datos_consulta = new FormData();

	//Creamos las variables con los datos de los inputs
	let observaciones = document.getElementById("observacion_input").value;

	datos_consulta.append("observaciones",observaciones);
	datos_consulta.append("id_caja",id_caja);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','editar_observacion');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta); 
	
	modal_observacion.hide(); //Esconde el modal

	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	consultarCajasChicas();	

	// Dar mensaje de exito
	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');//Mensaje de que se completo la operacion
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
	return new DataTable("#tabla_registros_sistema",{
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

// Aqui habian 1296 lineas de codigo :..