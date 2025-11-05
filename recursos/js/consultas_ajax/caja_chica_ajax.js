let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let modal_carga = new bootstrap.Modal("#modal_carga");
let tiempoInicio;

let descripciones = {};
let modal_observacion = new bootstrap.Modal(document.querySelector("#modal_descripciones"));
let modal_registro_gastos = new bootstrap.Modal(document.querySelector("#modal_registro_gastos"));
let modal_repocicion_caja = new bootstrap.Modal(document.querySelector("#modal_reponer_caja"));
let tabla_movimientos;
let boton_formulario = document.getElementById("boton_gasto_caja");

let tasa_dolar = localStorage.getItem("tasa_dolar") || 0;
let diferencia = 0; //Valor añadido o sustraido al modificar

// Eventos
document.getElementById('header-toggle').addEventListener("click",e=>{
    setTimeout(function(){
        tabla_movimientos.columns.adjust().draw();
    },450);
});

document.getElementById("mes_select").addEventListener("change",e=>{
	id_caja = e.target.value;

	document.getElementById("descripciones").textContent = descripciones[id_caja];	
	document.getElementById("descripciones").closest(".col-7").removeAttribute("hidden");

	tabla_movimientos.ajax.reload((data)=>{
		// llenarTablaResumen('',data)
		// console.log(data)
	});

	document.getElementById("span_fondo_fijo").textContent = "Fondo fijo de caja: " + e.target.options[e.target.selectedIndex].getAttribute("saldo_inicial") + " Bs. / " + parseFloat(e.target.options[e.target.selectedIndex].getAttribute("saldo_inicial") / tasa_dolar).toFixed(2) + " $";
	
	if (e.target.options[e.target.selectedIndex].getAttribute("activa") == "Cerrada"){
		document.getElementById("botones_movimientos").setAttribute("hidden",'');
		document.getElementById("span_caja_activa").setAttribute("class","text-danger");
		document.getElementById("span_caja_activa").textContent = "Esta caja esta cerrada";
	}else{
		document.getElementById("botones_movimientos").removeAttribute("hidden");
		document.getElementById("span_caja_activa").setAttribute("class","text-success");
		document.getElementById("span_caja_activa").textContent = "Esta es la caja actual";
	}	
});

document.getElementById("boton_editar_observacion").addEventListener("click",e=>{	
	document.getElementById("descripcion_input").value = document.getElementById("descripciones").textContent;
});

document.querySelector(`#boton_intercambio_monto`).addEventListener('click',e=>{
	if (typeof e.preventDefault === 'function') {
  		e.preventDefault();
	}
	let monto = document.getElementById("monto"), 
	cambio = document.getElementById("monto_cambio"),
	valor_temporal = 0;

	if (monto.getAttribute("monto") == "bs") {
		monto.setAttribute("monto",'$');

		valor_temporal = monto.value;
		monto.value = cambio.value;
		cambio.value = valor_temporal;

		monto.parentElement.querySelector(".icono_moneda").textContent = "$";
		cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
	}
	else{
		monto.setAttribute("monto",'bs');

		valor_temporal = monto.value;
		monto.value = cambio.value;
		cambio.value = valor_temporal;

		monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
		cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
	}
});

document.querySelector(`#boton_intercambio_monto_reponer`).addEventListener('click',e=>{
	if (typeof e.preventDefault === 'function') {
  		e.preventDefault();
	}
	let monto = document.getElementById("monto_reponer"), 
	cambio = document.getElementById("monto_cambio_reponer"),
	valor_temporal = 0;

	if (monto.getAttribute("monto") == "bs") {
		monto.setAttribute("monto",'$');

		valor_temporal = monto.value;
		monto.value = cambio.value;
		cambio.value = valor_temporal;

		monto.parentElement.querySelector(".icono_moneda").textContent = "$";
		cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
	}
	else{
		monto.setAttribute("monto",'bs');

		valor_temporal = monto.value;
		monto.value = cambio.value;
		cambio.value = valor_temporal;

		monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
		cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
	}
});

document.getElementById("modal_registro_gastos").addEventListener("hidden.bs.modal",e=>{
	document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");
	boton_formulario.textContent = "Registrar";

	document.getElementById("form_registro_gasto").reset();
	document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja").textContent;

	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));

	let monto = document.getElementById("monto"), 
	cambio = document.getElementById("monto_cambio"),
	valor_temporal = 0;

	if (monto.getAttribute("monto") == "$") {
		monto.setAttribute("monto",'bs');

		valor_temporal = monto.value;
		monto.value = cambio.value;
		cambio.value = valor_temporal;

		monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
		cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
	}

	diferencia = 0;
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

function envio(operacion) {	
	if (operacion == "Editar") {

		let id_modificar = boton_formulario.getAttribute("id_modificar");
		modificar(id_modificar);
	}
	else if(operacion == "Registrar"){
		registrar();
	}
	else if (operacion == "reponer_caja") {
		reponer_caja();
	}
	else{
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
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
	boton_editar.setAttribute("data-bs-target", "#modal_registro_gastos");

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

function formatearFecha(fecha) {
    if (!fecha) {
        return "N/A";
    }

    const partes = fecha.split("-");
    if (partes.length === 3) {
        return `${partes[2]}-${partes[1]}-${partes[0]}`; // DD-MM-AAAA
    }
    return fecha;
}

async function consultarCajasChicas() {
	let datos_consulta = new FormData();
	datos_consulta.append("operacion","consultar_cajas_chicas");
	let cajas_chicas = await query(datos_consulta);

	let span_caja_activa = document.querySelector("#span_caja_activa");

	if (cajas_chicas.length == 0) {
		span_caja_activa.textContent = `No hay cajas registradas`;
		return;
	}

	let select = document.querySelector("#mes_select");	
	let fragment = document.createDocumentFragment();
	
	cajas_chicas.map(caja=>{
		let option = document.createElement("option");
		let [anio,mes_d,dia] = caja.fecha_creacion.split("-")
		fecha = new Date(`${anio}/${mes_d}/${dia}`);
		mes_buscar = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
		anio_buscar = fecha.getFullYear();

		option.textContent = `${mes_buscar} del ${anio_buscar}`;
		option.value = caja.id_caja_chica;
		option.setAttribute("saldo_actual",caja.saldo_actual);
		option.setAttribute("saldo_inicial",caja.fondo_fijo);
		option.setAttribute("activa",caja.estado);

		fragment.appendChild(option);

		descripciones[caja.id_caja_chica] = caja.descripcion;//parece fumada pero sirve
	});
	select.options.length = 0;
	select.appendChild(fragment);
	
	if (select.value != "") {
		llenarTablaRegistrosSistema();

		document.getElementById("descripciones").textContent = descripciones[select.value];
		document.getElementById("descripciones").closest(".col-7").removeAttribute("hidden");

		document.getElementById("span_fondo_fijo").textContent = "Fondo fijo de caja: " + select.options[select.selectedIndex].getAttribute("saldo_inicial") + " Bs. / " + parseFloat(select.options[select.selectedIndex].getAttribute("saldo_inicial") / tasa_dolar).toFixed(2) + " $";

		if (select.options[select.selectedIndex].getAttribute("activa") == "Cerrada"){
		document.getElementById("span_caja_activa").setAttribute("class","text-danger");
		document.getElementById("span_caja_activa").textContent = "Esta caja esta cerrada";
		}
		else{
			document.getElementById("botones_movimientos").removeAttribute("hidden");
			document.getElementById("span_caja_activa").setAttribute("class","text-success");
			document.getElementById("span_caja_activa").textContent = "Esta es la caja actual";
		}
	}
}

async function llenarTablaRegistrosSistema(){
	let saldo_inicial = document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].getAttribute("saldo_inicial"),
	saldo_actual = document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].getAttribute("saldo_actual");
	// tabla_resumen.children[3].children[0].children[1].textContent = saldo_inicial;

	document.getElementById("fondos_caja").textContent = saldo_actual + "Bs. / " + (saldo_actual / tasa_dolar).toFixed(2) + "$";
 	document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja").textContent;
 	document.getElementById("fondos_caja_restantes").textContent = document.getElementById("fondos_caja").textContent;
 	document.getElementById("fondos_gastados").textContent = (saldo_inicial - saldo_actual) + "Bs. / " + ((saldo_inicial - saldo_actual) / tasa_dolar).toFixed(2) + "$";

 	if (tabla_movimientos) {
		tabla_movimientos.ajax.reload();
		return;
	}	

	eventosCargaDataTable('tabla_registros_sistema',modal_carga);
	const paramentros_consulta = (data)=>{
		data.operacion = 'consultar_movimientos_caja';
		data.caja_chica_id = document.getElementById("mes_select").value;
	}
	const estructura_tabla_movimientos = [
 		{
 			"data": null,
            "render": function (row) {            	
                return `${formatearFecha(row.fecha)}`;
            }  
        },
		{ 
			"data": null, 
			"render": function (row) {                
                return `${row.monto.toFixed(2)} Bs. / ${(row.monto / tasa_dolar).toFixed(2)} $.`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	return `${row.concepto}`;
            }
        },
		{ 
            "data": null,
            "render": function (row) {
            	return `${row.estado}`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row["id_movimiento_caja"];
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	];

 	const configuraciones_tabla_movimientos = (row, data)=>{
 		row.setAttribute("id",`fila-${data.id_movimiento_caja}`); 		
 		row.querySelector(".editar")?.addEventListener('click',preparar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar); 		
 	}

 	tabla_movimientos = crearDataTable('tabla_registros_sistema',estructura_tabla_movimientos,paramentros_consulta,configuraciones_tabla_movimientos); 	
}

async function registrar() {
	let datos_consulta = new FormData();
	
	let fecha = document.querySelector("#fecha").value,	
	concepto = document.querySelector("#concepto").value,
	caja_chica_id = document.getElementById("mes_select").value,
	monto;

	if (document.querySelector("#monto").getAttribute("monto") == "bs") {
		monto = document.querySelector("#monto").value;
	}
	else{
		monto = document.querySelector("#monto_cambio").value;
	}
	
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("concepto",concepto);
	datos_consulta.append("caja_chica_id",caja_chica_id);

	datos_consulta.append('operacion','registrar');

	let respuesta = await query(datos_consulta,'text-secondary');

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}
	let fondos_gastados = parseFloat(document.getElementById("mes_select").options[document.getElementById("mes_select").selectedIndex].getAttribute("saldo_inicial")) - parseFloat(document.getElementById("fondos_restante").textContent);
	document.getElementById("fondos_caja_restantes").textContent = document.getElementById("fondos_restante").textContent;
 	document.getElementById("fondos_gastados").textContent = fondos_gastados + "Bs. / " + (fondos_gastados / tasa_dolar).toFixed(2) + "$";

	document.getElementById("fondos_caja").textContent = document.getElementById("fondos_restante").textContent;

	modal_registro_gastos.hide();

	tabla_movimientos.ajax.reload();
	// consultarCajasChicas();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function preparar_formulario(e) {
	datos_consulta = new FormData();
		
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value; 
	}
	datos_consulta.append("id_movimiento_caja",id);

	datos_consulta.append('operacion','consultar_movimiento');

	data = await query(datos_consulta,'text-secondary');	
	
	let fecha = document.querySelector("#fecha"),
	monto = document.querySelector("#monto"),	
	concepto = document.querySelector("#concepto");	

	fecha.value = data.fecha;
	monto.value = data.monto;	
	concepto.value = data.concepto;	

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}
	
	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",data.id_movimiento_caja);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal_registro_gasto').textContent = "Modificar Gasto de Caja";		

	let input_convertir = document.getElementById("monto_cambio");
	input_convertir.value = (parseFloat(this.value) / tasa_dolar).toFixed(2) || 0;

	let fondo = document.getElementById("fondos_caja").textContent.split("Bs")[0],
	etiqueta_fondo_restantes = document.getElementById("fondos_restante");
	if ((fondo - this.value) < 0){
		etiqueta_fondo_restantes.textContent = "Excedido";
	}
	else{
		etiqueta_fondo_restantes.textContent = (fondo - this.value).toFixed(2) + "Bs. / " + ((fondo - this.value) / tasa_dolar).toFixed(2) + "$";
	}

	diferencia = parseFloat(data.monto);
	// id_modificar = id;	
}

async function modificar(id) {	
	let datos_consulta = new FormData();

	let fecha = document.querySelector("#fecha").value,	
	concepto = document.querySelector("#concepto").value,
	monto;

	if (document.querySelector("#monto").getAttribute("monto") == "bs") {
		monto = document.querySelector("#monto").value;
	}
	else{
		monto = document.querySelector("#monto_cambio").value;
	}
	
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("concepto",concepto);

	datos_consulta.append("id_movimiento_caja",id);

	datos_consulta.append('operacion','editar');

	let respuesta = await query(datos_consulta,'text-secondary');

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	document.getElementById("fondos_caja").textContent = document.getElementById("fondos_restante").textContent;
	document.getElementById("form_registro_gasto").reset();
 	modal_registro_gastos.hide();


	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";

	document.getElementById('titulo_modal_registro_gasto').textContent = "Registrar Gasto de Caja";

	tabla_movimientos.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');
}

async function editarObservacion(id_caja) {	
	let datos_consulta = new FormData();

	//Creamos las variables con los datos de los inputs
	let descripcion = document.getElementById("descripcion_input").value;

	datos_consulta.append("descripcion",descripcion);
	datos_consulta.append("id_caja",id_caja);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','editar_observacion');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta,'text-secondary'); 
	
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

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este Gasto?",
		showCancelButton: true,
		confirmButtonText: "Si, Eliminar",
		confirmButtonColor: "#e01d22",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((resultado) => {
		if (resultado.isConfirmed) {
			eliminar(boton_eliminar.value,boton_eliminar);		
		}
	});
}

async function eliminar(id,boton_eliminar) {
	datos_consulta = new FormData();

	datos_consulta.append("id_movimiento_caja",id);

	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	let monto = parseFloat(boton_eliminar.closest("tr").children[1].textContent.split("Bs")[0]);
	let saldo_actual = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]) + monto;
	document.getElementById("fondos_caja").textContent = saldo_actual + "Bs. / " + (saldo_actual / tasa_dolar).toFixed(2) + "$";
 	document.getElementById("fondos_restante").textContent = document.getElementById("fondos_caja").textContent;

	tabla_movimientos.ajax.reload();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

async function reponer_caja(){	
	let id_caja_chica = document.getElementById("mes_select").value,
	monto;

	if (document.querySelector("#monto_reponer").getAttribute("monto") == "bs") {
		monto = document.querySelector("#monto_reponer").value;
	}
	else{
		monto = document.querySelector("#monto_cambio_reponer").value;
	}

	let datos_consulta = new FormData();

	datos_consulta.append("id_caja_chica",id_caja_chica);
	datos_consulta.append("monto",monto);
	
	datos_consulta.append('operacion','reponer_caja');

	let respuesta = await query(datos_consulta,'text-secondary');

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	consultarCajasChicas();

	modal_repocicion_caja.hide();

	mensajes('success',4000,'Atencion','Se ha repuesto la caja exitosamente');
}

async function query(datos,color_carga = 'text-light') {
    document.getElementById('icono_carga').setAttribute("class",`spinner-border ${color_carga}`);
    
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