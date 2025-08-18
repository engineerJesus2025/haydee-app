let tabla_resumen = document.querySelector("#tabla_resumen");

let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let modal_carga = new bootstrap.Modal("#modal_carga");
let tiempoInicio;

let observaciones = {};
let modal_observacion = new bootstrap.Modal(document.querySelector("#modal_observaciones"));
let tabla_movimientos;

const resizeObserver = new ResizeObserver(entries => {
	if (tabla_movimientos) {
		tabla_movimientos.draw();
	}
});
resizeObserver.observe(document.querySelector("#tabla_registros_sistema"));

document.getElementById("mes_select").addEventListener("change",e=>{	
	id_caja = e.target.value;

	document.getElementById("observaciones").textContent = observaciones[id_caja];	
	document.getElementById("observaciones").closest(".col-7").removeAttribute("hidden");

	tabla_movimientos.ajax.reload((data)=>{llenarTablaResumen('',data)});
	
	if (e.target.options[e.target.selectedIndex].getAttribute("activa") == "Cerrada"){
		document.getElementById("span_select").setAttribute("class","text-danger");
		document.getElementById("span_select").textContent = "Esta caja esta cerrada";
	}else{
		document.getElementById("span_select").setAttribute("class","text-success");
		document.getElementById("span_select").textContent = "Esta es la caja actual";
	}	
});

document.getElementById("boton_editar_observacion").addEventListener("click",e=>{	
	document.getElementById("observacion_input").value = document.getElementById("observaciones").textContent;
});

consultarCajasChicas();

//Funciones:
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

function llenarTablaResumen(settings,data){
	tabla_resumen.children[2].children[0].children[1].textContent = 0;
	tabla_resumen.children[2].children[1].children[1].textContent = 0;
	tabla_resumen.children[3].children[0].children[1].textContent = 0;
	
	let total_ingresos = 0;
	let total_egresos = 0;
 	data.map(fila=>{
 		if (fila.movimiento == "Ingreso") {			
			total_ingresos += fila.monto;
		}else{			
			total_egresos += fila.monto;
		}
 	});

 	tabla_resumen.children[2].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[2].children[0].children[1].textContent) + parseFloat(total_ingresos));
 	tabla_resumen.children[2].children[1].children[1].textContent = (parseFloat(tabla_resumen.children[2].children[1].children[1].textContent) + parseFloat(total_egresos));

 	tabla_resumen.children[3].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[3].children[0].children[1].textContent) - parseFloat(total_egresos));
 	tabla_resumen.children[3].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[3].children[0].children[1].textContent) + parseFloat(total_ingresos));

 	tabla_resumen.children[2].children[0].children[1].textContent += "Bs.";
	tabla_resumen.children[2].children[1].children[1].textContent += "Bs.";
	tabla_resumen.children[3].children[0].children[1].textContent += "Bs.";

	tabla_resumen.closest(".card").removeAttribute("hidden","");
}

function crearDataTable(id_tabla,estructura_filas,datos_paramentros, configuraciones_post_creacion = ()=>{}, acciones_finales = ()=>{}){
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
        "createdRow": configuraciones_post_creacion,
        "initComplete" : acciones_finales
	});
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
		let [anio,mes_d,dia] = caja.fecha_apertura.split("-")
		fecha = new Date(`${anio}/${mes_d}/${dia}`);
		mes_buscar = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
		anio_buscar = fecha.getFullYear();

		option.textContent = `${mes_buscar} del ${anio_buscar} - Inicial ${caja.monto_inicial}Bs.`;
		option.value = caja.id_caja_chica;
		option.setAttribute("saldo_actual",caja.saldo_actual);
		option.setAttribute("saldo_inicial",caja.monto_inicial);
		option.setAttribute("activa",caja.estado);

		fragment.appendChild(option);

		observaciones[caja.id_caja_chica] = caja.observaciones;//parece fumada pero sirve
	});
	select.appendChild(fragment);
	
	if (select.value != "") {
		llenarTablaRegistrosSistema();

		document.getElementById("observaciones").textContent = observaciones[select.value];
		document.getElementById("observaciones").closest(".col-7").removeAttribute("hidden");

		if (select.options[select.selectedIndex].getAttribute("activa") == "Cerrada"){
		document.getElementById("span_select").setAttribute("class","text-danger");
		document.getElementById("span_select").textContent = "Esta caja esta cerrada";
		}else{
			document.getElementById("span_select").setAttribute("class","text-success");
			document.getElementById("span_select").textContent = "Esta es la caja actual";
		}
	}
}

async function llenarTablaRegistrosSistema(){
	let saldo_inicial = document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].getAttribute("saldo_inicial");	
	tabla_resumen.children[3].children[0].children[1].textContent = saldo_inicial;

	eventosCargaDataTable('tabla_registros_sistema',modal_carga);
	const paramentros_consulta = (data)=>{
		data.operacion = 'buscar_mes';
		data.id_caja = document.getElementById("mes_select").value;
	}
	const estructura_tabla_movimientos = [
 		{
 			"data": null,
            "render": function (data, type, row) {            	
                return `${row.movimiento}`;
            }  
        },
		{ 
			"data": null, 
			"render": function (data, type, row) {                
                return `${row.fecha}`;
            }
        },
        { 
            "data": null, 
            "render": function (data, type, row) {
            	return `${row.monto}Bs.`;
            }
        },
		{ 
            "data": null,
            "render": function (data, type, row) {
            	return `${row.remitente}`;
            }
        },
        // { 
        //     "data": null, 
        //     "render": function (data, type, row) {
        //     	let id_campo = row["id_usuario"];
        //        	let acciones = crearBotones(id_campo);

        //         return `${acciones.innerHTML}`;
        // 	}
        // } 		
 	];

 	const configuraciones_tabla_movimientos = (row, data, dataIndex)=>{
 		Array.from(row.children).map((td,index)=>{
 			if (!((row.children.length - 1) == index)) {
 				td.setAttribute("class",'align-middle text-center')
 			}
 		});
 		row.children[2].setAttribute("class",`text-center ${(data.movimiento == "Ingreso")?"text-success":"text-danger"}`);

		if (data.movimiento == "Ingreso") {			
			let ingresos = data.monto;
			tabla_resumen.children[2].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[2].children[0].children[1].textContent) + parseFloat(ingresos));
			tabla_resumen.children[3].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[3].children[0].children[1].textContent) + parseFloat(ingresos))
		}else{			
			let egresos = data.monto;
			tabla_resumen.children[2].children[1].children[1].textContent = (parseFloat(tabla_resumen.children[2].children[1].children[1].textContent) + parseFloat(egresos));
			tabla_resumen.children[3].children[0].children[1].textContent = (parseFloat(tabla_resumen.children[3].children[0].children[1].textContent) - parseFloat(egresos))
		}
 	} 	

 	tabla_movimientos = crearDataTable('tabla_registros_sistema',estructura_tabla_movimientos,paramentros_consulta,configuraciones_tabla_movimientos,llenarTablaResumen);	
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

async function query(datos) {
	peticionesActivas++;

	const tiempoInicio = performance.now();

	ultimaPeticion = tiempoInicio;

	if (peticionesActivas === 1) {
		tiempoCarga = setTimeout(()=>{
			modal_carga.show();
		}, 300);
	}

	try{
		let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;
		});
		return data;
	}
	catch(error){
		console.log(error);
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
	finally{
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
	}
}


// Aqui habian 1296 lineas de codigo :..