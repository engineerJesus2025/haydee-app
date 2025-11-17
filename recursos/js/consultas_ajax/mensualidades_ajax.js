let dolar = {};
let tabla_mensualidad_asignar = document.querySelector(`#tabla_mensualidad_asignar`);
let tabla_asignar_inicial = tabla_mensualidad_asignar.innerHTML;
let boton_registrar = document.getElementById("boton_registrar");
let boton_formulario = document.getElementById("boton_formulario");
let tabla_apartamentos;
let tabla_mensualidades;
let modal = new bootstrap.Modal("#modal_mensualidad");
let select_mes_asignar = document.getElementById('mes_select_asignar');
let option_editar = null;

let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let modal_carga = new bootstrap.Modal("#modal_carga");

let tasa_dolar = parseFloat(isNaN(localStorage.getItem("tasa_dolar"))?1:localStorage.getItem("tasa_dolar")).toFixed(2);

//Eventos
select_mes_asignar.addEventListener("change",e=>{
	tabla_mensualidad_asignar.innerHTML = tabla_asignar_inicial;
	let fecha = e.target.selectedOptions[0].id
	llenarTablaNueva(fecha);
});

boton_registrar.addEventListener('click',e=>{
	select_mes_asignar.selectedIndex = 0;
	if (select_mes_asignar.children.length != 0) {
		let fecha = select_mes_asignar.selectedOptions[0].id
		llenarTablaNueva(fecha);
	}
});

document.getElementById('header-toggle').addEventListener("click",e=>{
    setTimeout(function(){
        tabla_mensualidades.columns.adjust().draw();
    },450);
});

document.querySelector(`#modal_mensualidad`).addEventListener("hidden.bs.modal",()=>{	
	tabla_mensualidad_asignar.innerHTML = tabla_asignar_inicial;

	select_mes_asignar.value = "";
	select_mes_asignar.parentElement.removeAttribute("hidden","");
	select_mes_asignar.removeAttribute('disabled');

	boton_formulario.textContent = "Guardar";
	boton_formulario.setAttribute("op","Registrar");

	document.getElementById('titulo_modal').textContent = "Registrar Mensualidad";

	if (option_editar != null) {
		option_editar.parentElement.removeChild(option_editar);
		option_editar = null;
	}
});

document.getElementById('modal_mensualidades_apartamentos').addEventListener('shown.bs.modal', function () {
    if ($.fn.DataTable.isDataTable("#mensualidades_apartamentos")) {
        $('#mensualidades_apartamentos').DataTable().columns.adjust().responsive.recalc();
    }
});

function envio(operacion,boton_eliminar = '') {	
	if (operacion == "Editar") {
		// id_modificar = boton_formulario.getAttribute("id_modificar");//obtenemos el id del registro
		modificar();
	}
	else if(operacion == "Registrar"){
		//sino a registrar
		registrar_mensualidad();
	}
	else{
		// esto es imposible que pase pero aja
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente');
	}
}

async function verificarMes(){
	datos_consulta = new FormData();

	datos_consulta.append('operacion','verificar_meses');

	let meses = await query(datos_consulta);
	
	if(!(meses.estatus == undefined)){
		mensajes('error',4000,'Atencion', meses.mensaje);
		return;
	}

	let meses_sin_mensualidad = [];

	meses.map(registro=>{		
		let fecha = new Date(`${registro.mes_presupuesto}-01-${registro.anio_presupuesto}`);
		meses_sin_mensualidad.push(fecha.toLocaleDateString());
	});

	if (meses_sin_mensualidad.length == 0) {
		boton_registrar.closest(".col").setAttribute("hidden","");
		return;
	}
	else{
		boton_registrar.closest(".col").removeAttribute("hidden","");
	}	
	
	boton_registrar.nextElementSibling.textContent = `*Hay ${meses_sin_mensualidad.length} Mes${(meses_sin_mensualidad.length != 1)?'es':''} que falta${(meses_sin_mensualidad.length != 1)?'n':''} por asignar`;

	let fragment = document.createDocumentFragment();
	meses_sin_mensualidad.map(mes=>{
		let option = document.createElement("option");

		let fecha = new Date(`${mes.split("/")[1]}-01-${mes.split("/")[2]}`);
		mes_buscar = fecha.toLocaleString("es-ES",{month: 'long'});
		anio_buscar = fecha.getFullYear();
		
		option.setAttribute("id",fecha.toLocaleDateString());
		option.textContent = `${mes_buscar} del ${anio_buscar}`;

		fragment.appendChild(option);
	});

	select_mes_asignar.textContent = null;
	select_mes_asignar.appendChild(fragment);

	if (select_mes_asignar.children.length != 0) {
		let fecha = select_mes_asignar.selectedOptions[0].id
		llenarTablaNueva(fecha);
	}
}

async function consultar_mensualidades() {
	const paramentros_consulta = (data)=>{data.operacion = 'consultar_mensualidades_mes';}
	
	const estructura_tabla_mensualidades = [
 		{
 			"data": null,
            "render": function (row) {
            	let fecha = new Date(`${row["mes"]}/01/${row["anio"]}`);
				let mes = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
				let anio = fecha.getFullYear();

                return `${mes} del ${anio}`;
            }  
        },
		{ 
			"data": null, 
			"render": function (row) {				
                return `${row["monto"].toFixed(2)} Bs. / ${(row["monto"] / row["tasa_dolar"]).toFixed(2)} $`;
            }
        },
        { 
            "data": null,
            "render": function (row) {
            	let deuda_cancelada = ((row.monto - row.pagado) < 0)?true:false;
            	let pagado = (deuda_cancelada)?'Deuda Cancelada':(row.monto - row["pagado"]).toFixed(2) + " Bs.";
				let pagado_dolar = (deuda_cancelada)?'':' / ' + ((row.monto - row["pagado"]) / row["tasa_dolar"]).toFixed(2) + ' $';

                return `${pagado}${pagado_dolar}`;
            }
        },
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row["ids"]; 
				let ids_apartamentos = row["ids_apartamentos"];
               	let acciones = crearBotones(id_campo,ids_apartamentos);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	] 	

 	const configuraciones_tabla_mensualidad = (row, data)=>{ 		
 		Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'))

 		row.firstElementChild.id = `${data["mes"]}/${data["anio"]}`;
 		row.setAttribute("id",`fila-01/${data["mes"]}/${data["anio"]}`);
 		row.setAttribute("data-id",`fila-01/${data["mes"]}/${data["anio"]}`);
 		row.setAttribute("intereses",data["porcentaje_interes"]);
 		row.setAttribute("limite",data["limite_mensualidad"]);

 		row.querySelector(".vista_previa").addEventListener('click',llenarTablaMensualidadesApartamentos);
 		row.querySelector(".editar").addEventListener('click',prepararFormulario);
 		row.querySelector(".eliminar").addEventListener('click',eventoEliminar);

 		row.querySelector(".cuadro_pagos").addEventListener('click',e=>{
			e.preventDefault();	
			boton_cuadro_pagos = e.target;
			if (boton_cuadro_pagos.getAttribute("type") != "submit") {
				boton_cuadro_pagos = e.target.parentElement;
			}

			let fecha_buscar = boton_cuadro_pagos.closest("tr").firstElementChild.id.split("/").join("-");		
			// let select_reporte = document.getElementById('mes_select').selectedOptions[0].id.split("/").join("-").split("1-")[1];

			boton_cuadro_pagos.previousElementSibling.value = fecha_buscar;

			Swal.fire({
				title: "¿Estás seguro?",
				text: "¿Está seguro que desea generar el cuadro de pagos de esta mensualidad?",
				showCancelButton: true,
				confirmButtonText: "Si, Generar",
				confirmButtonColor: "#1b8a40",
				cancelButtonText: "Cancelar",
				icon: "warning"
			}).then((resultado) => {
				if (resultado.isConfirmed) {
					boton_cuadro_pagos.closest('form').submit();
				}
			});			
		});
 	}

 	tabla_mensualidades = crearDataTable('tabla_mensualidad',estructura_tabla_mensualidades,paramentros_consulta,configuraciones_tabla_mensualidad);

 	setTimeout(seleccionarMensualidadPorNotificacion, 500);
}

function crearBotones(ids_mensualidades,ids_apartamentos) {
	let td = document.createElement("td");
	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");

	//Boton para la vista previa
	let div_vista_previa = document.createElement("div");
	div_vista_previa.setAttribute("class", "col-lg-3 col-6 mt-2 mt-lg-0");

	let boton_vista_previa = document.createElement("button");

	let icono_vista_previa = document.createElement("i");
	icono_vista_previa.setAttribute("class", "bi bi-eye-fill")
	boton_vista_previa.appendChild(icono_vista_previa);

	boton_vista_previa.setAttribute("type", "button");
	boton_vista_previa.setAttribute("class", "btn btn-primary vista_previa");
	boton_vista_previa.setAttribute("tabindex", "-1");
	boton_vista_previa.setAttribute("role", "button");
	boton_vista_previa.setAttribute("aria-disabled", "true");
	boton_vista_previa.setAttribute("data-bs-toggle", "modal");
	boton_vista_previa.setAttribute("data-bs-target", "#modal_mensualidades_apartamentos");

	boton_vista_previa.setAttribute("title","Ver detalles");

	div_vista_previa.appendChild(boton_vista_previa);

	acciones.appendChild(div_vista_previa);

	//Boton para el cuadro de pagos
	let formulario_cuadro = document.createElement("form");
	formulario_cuadro.setAttribute("class", "col-lg-3 col-6 mt-2 mt-lg-0");
	formulario_cuadro.setAttribute("action", "?pagina=reportes_controlador.php&accion=cuadro_pagos");
	formulario_cuadro.setAttribute("method", "POST");

	let input_select_cuadro = document.createElement("input");
	input_select_cuadro.setAttribute("type", "hidden");
	input_select_cuadro.setAttribute("name", "select_reporte");

	let boton_cuadro_pagos = document.createElement("button");
	let icono_cuadro = document.createElement("i");
	icono_cuadro.setAttribute("class", "bi bi-card-checklist")
	boton_cuadro_pagos.appendChild(icono_cuadro);

	boton_cuadro_pagos.setAttribute("class", "btn btn-outline-light cuadro_pagos");	
	boton_cuadro_pagos.setAttribute("title","click para generar cuadro de pagos");
	boton_cuadro_pagos.setAttribute("style", "background-color:#3939a9");
	boton_cuadro_pagos.setAttribute("type","submit");

	formulario_cuadro.appendChild(input_select_cuadro);
	formulario_cuadro.appendChild(boton_cuadro_pagos);
	
	acciones.appendChild(formulario_cuadro);

	let div_editar = document.createElement("div");
	div_editar.setAttribute("class", "col-lg-3 col-6 mt-2 mt-lg-0");

	//Boton para editar
	let boton_editar = document.createElement("button");

	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square")
	boton_editar.appendChild(icono_editar);

	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_mensualidad");
	boton_editar.setAttribute("value",ids_mensualidades);// el valor del id para eliminar	
	boton_editar.setAttribute("ids_apartamentos",ids_apartamentos);

	boton_editar.setAttribute("title","Editar");
	//Le ponemos los botones al <td><td> de las acciones

	div_editar.appendChild(boton_editar);

	acciones.appendChild(div_editar);

	if (permiso_eliminar) {
		let div_eliminar = document.createElement("div");
		div_eliminar.setAttribute("class", "col-lg-3 col-6 mt-2 mt-lg-0");
		//creamos el boton de eliminar, le damos valor, y le asignamos la funcion para eliminar
		let boton_eliminar = document.createElement("button");

		let icono_eliminar = document.createElement("i");// le ponemos un icono
		icono_eliminar.setAttribute("class", "bi bi-trash");// y estilos
		boton_eliminar.appendChild(icono_eliminar);
				
		boton_eliminar.setAttribute("type", "button");
		boton_eliminar.setAttribute("class", "btn btn-danger eliminar");
		boton_eliminar.setAttribute("tabindex", "-1"); 
		boton_eliminar.setAttribute("role", "button");
		boton_eliminar.setAttribute("aria-disabled", "true");

		boton_eliminar.setAttribute("title","Eliminar");		

		div_eliminar.appendChild(boton_eliminar);

		acciones.appendChild(div_eliminar);
	}

	td.appendChild(acciones);

	return td;
}

async function prepararFormulario(e){
	let boton_editar = e.target;	
	if (boton_editar.value == undefined) {
		boton_editar = e.target.parentElement;
	}
	let fecha_buscar = boton_editar.closest("tr").firstElementChild.id;
	let fecha = '01/' + fecha_buscar;

	option_editar = document.createElement("option");

	let fecha_opcion = new Date(`${fecha_buscar.split("/")[0]}-01-${fecha_buscar.split("/")[1]}`);	
	option_editar.setAttribute("id",fecha_opcion.toLocaleDateString());
	option_editar.setAttribute("selected",'');

	option_editar.textContent = boton_editar.closest("tr").firstElementChild.textContent;

	select_mes_asignar.appendChild(option_editar);
	select_mes_asignar.setAttribute("disabled",'');

	await llenarTablaNueva(fecha);
	await llenarTablaEditar(boton_editar);
}

async function llenarTablaNueva(fecha) {
	tabla_mensualidad_asignar.innerHTML = tabla_asignar_inicial;
	//Llenamos el thead
	let datos_consulta = new FormData();
	
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("operacion","consultar_presupuestos_mensualidades");

	let detalles_presupuesto = await query(datos_consulta);
	
	if(!(detalles_presupuesto.estatus == undefined)){
		mensajes('error',4000,'Atencion', detalles_presupuesto.mensaje);
		return;
	}

	let fila_cabecera = tabla_mensualidad_asignar.querySelector("thead tr");
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");
	let filas_footer = tabla_mensualidad_asignar.querySelector("tfoot tr");

	let fragment = document.createDocumentFragment();
	let fragment_footer = document.createDocumentFragment();
	
	detalles_presupuesto.map(detalle=>{		
		let th = document.createElement("th");
		
		th.textContent = detalle.nombre;		
		th.setAttribute("class","text-center");
		
		filas_cuerpo.forEach(fila=>{
			let td = document.createElement("td");
			td.setAttribute("class","text-center");

			let checkbox  = document.createElement("input");
			checkbox.setAttribute("type","checkbox");
			checkbox.setAttribute("class","form-check-input border-primary");
			checkbox.setAttribute("style","cursor:pointer;");
			checkbox.setAttribute("detalle_monto",detalle.monto);
			checkbox.setAttribute("id_presupuestos_asociados",detalle.id_presupuestos_asociados);
			//Asignar el evento a los checkbox
			checkbox.addEventListener("change",e=>{
				let participacion = fila.getAttribute("participacion");
				let monto_apartamento = (participacion * checkbox.getAttribute("detalle_monto")) / 100;

				if (checkbox.checked) {
					fila.lastElementChild.previousElementSibling.textContent = (parseFloat(fila.lastElementChild.previousElementSibling.textContent) + monto_apartamento).toFixed(2);
					filas_footer.lastElementChild.previousElementSibling.textContent = (parseFloat(filas_footer.lastElementChild.previousElementSibling.textContent) + monto_apartamento).toFixed(2);
				}else{
					fila.lastElementChild.previousElementSibling.textContent = (parseFloat(fila.lastElementChild.previousElementSibling.textContent) - monto_apartamento).toFixed(2);
					filas_footer.lastElementChild.previousElementSibling.textContent = (parseFloat(filas_footer.lastElementChild.previousElementSibling.textContent) - monto_apartamento).toFixed(2);
				}
			});

			td.appendChild(checkbox);

			fila.appendChild(td);
		});

		let td = document.createElement("td");
		fragment_footer.appendChild(td);

		fragment.appendChild(th);
	});
	//Llenando la ultima fila, que es la del total
	let th = document.createElement("th");
	th.setAttribute('colspan',2);
	th.textContent = "Total a Pagar";

	fragment.appendChild(th);

	filas_cuerpo.forEach(fila=>{
		let td = document.createElement("td");
		td.textContent = 0;
		td.setAttribute("class","text-end pe-0");
		let td_2 = document.createElement("td");
		td_2.textContent = "Bs.";

		fila.appendChild(td);
		fila.appendChild(td_2);
	});

	fila_cabecera.appendChild(fragment);

	let td_footer = document.createElement("td");
	td_footer.setAttribute("class","text-end pe-0");
	td_footer.textContent = 0;
	let td_footer_2 = document.createElement("td");
	td_footer_2.textContent = "Bs.";

	fragment_footer.appendChild(td_footer);
	fragment_footer.appendChild(td_footer_2);

	filas_footer.appendChild(fragment_footer);
}

async function llenarTablaEditar(boton_editar) {
	let ids = boton_editar.value.split(',');
	let ids_apartamentos = boton_editar.getAttribute("ids_apartamentos").split(',');

	let datos_consulta = new FormData();

	datos_consulta.append('ids_mensualidades',ids);
	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_presupuestos_asociados');

	let presupuesto_mes = await query(datos_consulta,true);
	// Resvisamos el resultado
	if(!(presupuesto_mes.estatus == undefined)){
		mensajes('error',4000,'Atencion', presupuesto_mes.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	let mensualidad_seleccionada = [];

	ids_apartamentos.map((id_ap,index)=>{
		mensualidad_seleccionada.push({
			id_apartamento: id_ap,
			id_mensualidad: ids[index]
		})
	});
	
	// Todo esto para llenar los checkbox
	let filas_footer = tabla_mensualidad_asignar.querySelector("tfoot tr");
	// console.log()
	mensualidad_seleccionada.map(mensualidad_apartamento=>{
		let fila_tabla = tabla_mensualidad_asignar.querySelector(`[id='${mensualidad_apartamento.id_apartamento}']`);
		presupuesto_mes.map(mensualidades=>{
			if (mensualidades[0].id_mensualidad == mensualidad_apartamento.id_mensualidad){
				mensualidades.map(mensualidad=>{
					fila_tabla.querySelectorAll("[id_presupuestos_asociados]").forEach(checkbox=>{						
						if (checkbox.getAttribute("id_presupuestos_asociados").includes(mensualidad.id_detalle_presupuesto)) {
							if (!checkbox.checked) {
								checkbox.checked = true;
								let participacion = fila_tabla.getAttribute("participacion");
								let monto_apartamento = (participacion * checkbox.getAttribute("detalle_monto")) / 100;

								fila_tabla.lastElementChild.previousElementSibling.setAttribute("id",mensualidad.id_mensualidad);

								fila_tabla.lastElementChild.previousElementSibling.textContent = (parseFloat(fila_tabla.lastElementChild.previousElementSibling.textContent) + monto_apartamento).toFixed(2);
								filas_footer.lastElementChild.previousElementSibling.textContent = (parseFloat(filas_footer.lastElementChild.previousElementSibling.textContent) + monto_apartamento).toFixed(2);
							}
						}
					});
				});
			}
		});
	});

	document.getElementById("porcentaje_demora").value = boton_editar.closest("tr").getAttribute("intereses");
	document.getElementById("dia_limite").value = boton_editar.closest("tr").getAttribute("limite");

	boton_formulario.textContent = "Guardar Cambios";
	boton_formulario.setAttribute("op","Editar");
	document.getElementById('titulo_modal').textContent = "Modificar Mensualidad";
}

async function llenarTablaMensualidadesApartamentos(e){
	let boton_eliminar = e.target;	
	if (boton_eliminar.value == undefined) {
		boton_eliminar = e.target.parentElement;
	}
	let fecha_buscar = boton_eliminar.closest("tr").firstElementChild.id;
	let fecha = '01/' + fecha_buscar;
	
 	const estructura_tabla_apartamentos = [
 		{
 			"data": null, // No asignamos una clave específica aquí
            "render": function (data, type, row) {
                return `Apartamento Nº ${row.nro_apartamento}`;
            }
        },
		{
			"data": null, // No asignamos una clave específica aquí
			"render": function (data, type, row) {
                return row.nombre + ' ' + row.apellido;
            }
        },
        {
            "data": null, // No asignamos una clave específica aquí
            "render": function (data, type, row) {
                return `${row.monto.toFixed(2)} Bs. / ${(row.monto / row.tasa_dolar).toFixed(2)}$`;
            }
        },
        {
            "data": null, // No asignamos una clave específica aquí
            "render": function (data, type, row) {
            	let deuda_cancelada = ((row.monto - row.pagado) < 0)?true:false;
               	let pagado = (deuda_cancelada)?'Deuda Cancelada':(row.monto - row.pagado).toFixed(2) + " Bs.";
				let pagado_dolar = (deuda_cancelada)?'':' / ' + (row.monto / row.tasa_dolar).toFixed(2) + '$';
                return `${pagado}${pagado_dolar}`;	
        	}
        } 		
 	]

 	const paramentros_consulta = (data)=>{
 		data.operacion = 'consultar_mensualidades_apartamentos';
		data.fecha = fecha;
 	}

 	tabla_apartamentos = crearDataTable('mensualidades_apartamentos',estructura_tabla_apartamentos,paramentros_consulta); 	

    setTimeout(function() {
        tabla_apartamentos.columns.adjust().draw();
    }, 100);
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	let fecha_buscar = boton_eliminar.closest("tr").firstElementChild.id;
	let fecha = '01/' + fecha_buscar;

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar esta mensualidad?",
		showCancelButton: true,
		confirmButtonText: "Si, Eliminar",
		confirmButtonColor: "#e01d22",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((resultado) => {
		if (resultado.isConfirmed) {
			eliminar(fecha);				
		}
	});
}

async function registrar_mensualidad() {
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");	

	let porcentaje_interes = document.getElementById("porcentaje_demora").value,
	limite_mensualidad = document.getElementById("dia_limite").value;

	let mes = parseInt(select_mes_asignar.selectedOptions[0].id.split("/")[1]);
	let anio = parseInt(select_mes_asignar.selectedOptions[0].id.split("/")[2]);

	let ids_mensualidades = [], ids_apartamentos = [];
	for (const tr of filas_cuerpo){
		let apartamento_id;

		let monto = parseFloat(tr.lastElementChild.previousElementSibling.textContent);		
		
		apartamento_id = tr.id;

		let datos_consulta = new FormData();

		datos_consulta.append("operacion","registrar_mensualidad");

		datos_consulta.append("monto",monto);
		datos_consulta.append("tasa_dolar",tasa_dolar);
		datos_consulta.append("mes",mes);
		datos_consulta.append("anio",anio);
		datos_consulta.append("apartamento_id",apartamento_id);
		datos_consulta.append("limite_mensualidad",limite_mensualidad);
		datos_consulta.append("porcentaje_interes",porcentaje_interes);
		
		let respuesta = await query(datos_consulta,true);
		
		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			break;
		}
		
		let id_mensualidad = respuesta.lastId;
		let id_presupuestos = [];
		tr.querySelectorAll("td").forEach(td=>{
			if(td.firstElementChild != null){
				if(td.firstElementChild.checked){
					let grupo_id = td.firstElementChild.getAttribute("id_presupuestos_asociados").split(",");
					grupo_id.map(id_presupuesto=>{
						id_presupuestos.push(parseInt(id_presupuesto));
					});
				}
			}
		});

		datos_consulta = new FormData();

		datos_consulta.append("operacion","registrar_presupuestos_mensualidades");
		datos_consulta.append("id_mensualidad",id_mensualidad);
		datos_consulta.append("id_presupuestos",id_presupuestos);
		
		respuesta = await query(datos_consulta,true);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			break;
		}

		ids_mensualidades.push(id_mensualidad);
		ids_apartamentos.push(apartamento_id);
	}

	datos_consulta = new FormData();

	datos_consulta.append("operacion","registrar_bitacora");

	let monto_total_mensualidades = document.querySelector("tfoot tr").lastElementChild.previousElementSibling.textContent;

	datos_consulta.append("monto",monto_total_mensualidades);
	datos_consulta.append("mes",mes);
	datos_consulta.append("anio",anio);	
	
	let respuesta_bitacora = await query(datos_consulta,true);

	if (!respuesta_bitacora.estatus) {
		modal.hide();		
 		tabla_mensualidades.ajax.reload();
 		await verificarMes();
 		
		mensajes('error',4000,'Atencion',respuesta_bitacora.mensaje);
		return;
	}

	modal.hide();

 	tabla_mensualidades.ajax.reload();

	await verificarMes();
	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function modificar() {
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");
	
	for (const tr of filas_cuerpo){		
		let monto, mes, anio, apartamento_id, id_mensualidad;

		let porcentaje_interes = document.getElementById("porcentaje_demora").value,
		limite_mensualidad = document.getElementById("dia_limite").value;

		let fecha_buscar = tr.firstElementChild.id;

		mes = parseInt(select_mes_asignar.selectedOptions[0].id.split("/")[1]);
		anio = parseInt(select_mes_asignar.selectedOptions[0].id.split("/")[2]);

		monto = parseFloat(tr.lastElementChild.previousElementSibling.textContent);		
		
		apartamento_id = tr.id;
		id_mensualidad = tr.lastElementChild.previousElementSibling.id;

		let datos_consulta = new FormData();

		datos_consulta.append("operacion","editar_mensualidad");

		datos_consulta.append("monto",monto);
		datos_consulta.append("tasa_dolar",tasa_dolar);
		datos_consulta.append("mes",mes);
		datos_consulta.append("anio",anio);
		datos_consulta.append("apartamento_id",apartamento_id);
		datos_consulta.append("id_mensualidad",id_mensualidad);
		datos_consulta.append("limite_mensualidad",limite_mensualidad);
		datos_consulta.append("porcentaje_interes",porcentaje_interes);

		let respuesta = await query(datos_consulta,true);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}

		//Por cada mensualidad registrada se registra en la puente los gastos asignados		
		let id_presupuestos = [];
		tr.querySelectorAll("td").forEach(td=>{
			if(td.firstElementChild != null){
				if(td.firstElementChild.checked){
					let grupo_id = td.firstElementChild.getAttribute("id_presupuestos_asociados").split(",");
					grupo_id.map(id_presupuesto=>{
						id_presupuestos.push(parseInt(id_presupuesto));
					});
				}
			}
		});

		datos_consulta = new FormData();

		datos_consulta.append("operacion","editar_presupuesto_mensualidades");
		datos_consulta.append("id_mensualidad",id_mensualidad);
		datos_consulta.append("id_presupuestos",id_presupuestos);

		respuesta = await query(datos_consulta,true);

		if (!respuesta.estatus) {
			mensajes('error',4000,'Atencion',respuesta.mensaje);
			return;// en caso de error mandamos un mensaje con el error y nos vamos
		}
	}
	tabla_mensualidades.ajax.reload();

	mensajes('success',4000,'Atencion','Se han editado las mensualidades exitosamente');
	modal.hide();
}

async function eliminar(fecha) {
	let datos_consulta = new FormData();

	datos_consulta.append("operacion","eliminar_mensualidad");
	datos_consulta.append("fecha",fecha);
	
	let respuesta = await query(datos_consulta);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	tabla_mensualidades.ajax.reload();

	verificarMes();
	mensajes('success',4000,'Atencion','Se ha eliminado la mensualidad exitosamente');
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

async function query(datos,oscuro = false) {
    if (oscuro) {document.getElementById('icono_carga').setAttribute("class",`loader_dark`);}
    else{document.getElementById('icono_carga').setAttribute("class",`loader`);}
    
	peticionesActivas++;

	const tiempoInicio = performance.now();

	ultimaPeticion = tiempoInicio;

	if (peticionesActivas === 1) {
		tiempoCarga = setTimeout(()=>{
			modal_carga.show();
		}, 200);
	}

	try{
		let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;//Convertimos el resultado de json a js y lo mandamos
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
					const tiempoEsperaMin = 400; //lo mini que debe durar la peticion

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

consultar_mensualidades(); 
verificarMes();

function seleccionarMensualidadPorNotificacion() {
    const urlParams = new URLSearchParams(window.location.search);
    const idMensualidad = urlParams.get('referencia');
    
    if (idMensualidad) {
        // Esperar a que la DataTable esté completamente cargada
        const checkDataTable = setInterval(() => {
            if (tabla_mensualidades && tabla_mensualidades.rows().count() > 0) {
                clearInterval(checkDataTable);
                
                // Buscar la fila que coincida con el ID
                let filaEncontrada = null;
                
                tabla_mensualidades.rows().every(function() {
                    const data = this.data();
                    const row = this.node();
                    const rowId = row.getAttribute('id'); // Esto debería ser 'fila-01/MM/YYYY'
                    
                    // Verificar si esta fila coincide con el ID que buscamos
                    if (rowId && rowId.includes(idMensualidad)) {
                        filaEncontrada = this;
                        return false; // Salir del bucle
                    }
                });
                
                if (filaEncontrada) {
                    // Seleccionar y resaltar la fila
                    const node = filaEncontrada.node();
                    
                    // Remover highlight previo
                    tabla_mensualidades.rows().nodes().to$().removeClass('table-primary highlight-row');
                    
                    // Aplicar highlight
                    $(node).addClass('table-primary highlight-row');
                    
                    // Hacer scroll a la fila
                    $('html, body').animate({
                        scrollTop: $(node).offset().top - 100
                    }, 1000);                    
                }
            }
        }, 100);
    }
}

// Opcional: Abrir automáticamente los detalles
// setTimeout(() => {
// const botonVer = node.querySelector('.vista_previa');
//     if (botonVer) {
//         botonVer.click();
//     }
// }, 1500);