let graficaChart;
let modal = new bootstrap.Modal(document.getElementById("modal_reporte"));
const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
let fechas_asignadas_ingresos,fechas_asignadas_egresos;
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar")).toFixed(2) || 1;
document.getElementById("filtro").addEventListener("change",e=>{
	if (e.target.value == "Otro") {
		document.getElementById("label_fechas").removeAttribute("hidden");
		document.getElementById("div_fecha_inicio").removeAttribute("hidden");
		document.getElementById("div_fecha_cierre").removeAttribute("hidden");
	}
	else{
		let fecha_inicio = document.getElementById('fecha_inicio').value = null;
		let fecha_fin = document.getElementById('fecha_fin').value = null;
		document.getElementById("label_fechas").setAttribute("hidden","");
		document.getElementById("div_fecha_inicio").setAttribute("hidden","");
		document.getElementById("div_fecha_cierre").setAttribute("hidden","");
	}
});

document.getElementById("boton_vista_previa").addEventListener("click",async e=>{
	let fecha_inicio = document.getElementById('fecha_inicio').value;
	let fecha_fin = document.getElementById('fecha_fin').value;
	let filtro = document.getElementById('filtro').value;

	if (filtro == "") {
		mensajes('error',4000,'Atencion', "Debe seleccionar una opcion para buscar");
		return;// en caso de error mandamos un mensaje con el error y nos vamos}
	}

	if (filtro == "Otro" && (fecha_inicio == "" || fecha_fin == "")) {
		mensajes('error',4000,'Atencion', "Debe escoger la fecha inicio y final para la consulta");
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	else if (filtro == "mes") {
		fecha_inicio = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
		fecha_fin = new Date().toISOString().split('T')[0];
	}
	else if (filtro == "trimestre") {
		fecha_inicio = new Date();
		fecha_inicio.setMonth(fecha_inicio.getMonth() - 3);
		fecha_inicio = fecha_inicio.toISOString().split('T')[0];
		fecha_fin = new Date().toISOString().split('T')[0];
	}
    else if (filtro == "semestre") {
    	fecha_inicio = new Date();
    	fecha_inicio.setMonth(fecha_inicio.getMonth() - 6);
		fecha_inicio = fecha_inicio.toISOString().split('T')[0];
		fecha_fin = new Date().toISOString().split('T')[0];
	}
	else if (filtro == "año") {
		fecha_inicio = 	new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0];
		fecha_fin = new Date().toISOString().split('T')[0];
	}

	document.getElementById("fecha_grafico").textContent = `Mostrando resultados desde el ${fecha_inicio.split("-")[2]} de ${meses[parseInt(fecha_inicio.split("-")[1]) - 1]} del ${fecha_inicio.split("-")[0]} hasta ${fecha_fin.split("-")[2]} de ${meses[parseInt(fecha_fin.split("-")[1]) - 1]} del ${fecha_fin.split("-")[0]}`;
	document.getElementById("fecha_grafico_input").value = `Mostrando resultados desde el ${fecha_inicio.split("-")[2]} de ${meses[parseInt(fecha_inicio.split("-")[1]) - 1]} del ${fecha_inicio.split("-")[0]} hasta ${fecha_fin.split("-")[2]} de ${meses[parseInt(fecha_fin.split("-")[1]) - 1]} del ${fecha_fin.split("-")[0]}`;
	
	let balance = document.getElementById('select_balance').value;
	let metodo_pago = document.getElementById('select_metodo_pago').value;	
	let tipo_gasto = document.getElementById('select_tipo_gasto').value;	

	let datos_consulta = new FormData();

	datos_consulta.append("balance",balance);
	datos_consulta.append("metodo_pago",metodo_pago);
	datos_consulta.append("tipo_gasto",tipo_gasto);
	datos_consulta.append("filtro",filtro);
	datos_consulta.append("fecha_inicio",fecha_inicio);
	datos_consulta.append("fecha_fin",fecha_fin);

	datos_consulta.append("operacion","consultar_ingresos_egresos");

	let resultado = await query(datos_consulta);

	// console.log(resultado);	
	if(!(resultado.estatus)){
		mensajes('error',4000,'Atencion', resultado.mensaje);
		return;
	}

	if (resultado.mensaje.length == 0) {
		mensajes('warning',4000,'Atencion', "No hay resultados para esos filtros de busqueda");
		return;
	}

	datos_consulta = new FormData();

	datos_consulta.append("balance",balance);
	datos_consulta.append("metodo_pago",metodo_pago);
	datos_consulta.append("tipo_gasto",tipo_gasto);
	datos_consulta.append("filtro",filtro);
	datos_consulta.append("fecha_inicio",fecha_inicio);
	datos_consulta.append("fecha_fin",fecha_fin);

	datos_consulta.append("operacion","consultar_estadisticas_ingresos_egresos");

	let resultado_estadisticas = await query(datos_consulta);

	if(!(resultado_estadisticas.estatus)){
		mensajes('error',4000,'Atencion', resultado_estadisticas.mensaje);
		return;
	}

	modal.show();

	//Reseteamos valores anteriores
	obj_labels = [];
	meses_anios = [];
	
	obj_egreso = {
		label:'Egreso (BS.)',
		data:[],
		backgroundColor: ['#e01d22'],
		fechas_asignadas:{}
	};
	obj_ingreso = {
		label:'Ingreso (BS.)',
		data:[],		
		backgroundColor: ['#0079C2'],
		fechas_asignadas:{}
	};

	resultado.mensaje.map(registro=>{
		let fecha_seleccionada;
		if(registro.balance == "Ingreso"){
			if (filtro == "año" || filtro == "semestre") {
				let [anio,mes_d,dia] = registro.fecha.split("-")
				let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
				let mes = fecha_buscar.getMonth();
				let nombreMes = meses[mes];
				let anio_fecha = fecha_buscar.getFullYear();

				fecha_seleccionada = `${nombreMes} del ${anio_fecha}`;
			}
			else if (filtro == "mes" || filtro == "trimestre") {
				let [anio,mes_d,dia] = registro.fecha.split("-")
				let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
				let semanaDelMes = Math.ceil(fecha_buscar.getDate() / 7);
				let mes = fecha_buscar.getMonth();
				let nombreMes = meses[mes];
				let sufijo = ((semanaDelMes == 1 || semanaDelMes == 3)?"ra":((semanaDelMes == 2)?"da":"ta"));

				fecha_seleccionada = `${semanaDelMes}${sufijo} Semana, ${nombreMes}`;
			}
			else if (filtro == "Otro") {
				let dia = registro.fecha.split("-")[2]
				let mes = parseInt(registro.fecha.split("-")[1]) - 1;
				fecha_seleccionada = `${meses[mes]} ${dia}`;
			}
		
			if (!meses_anios.includes(fecha_seleccionada)) meses_anios.push(fecha_seleccionada);

			if (!obj_labels.includes(`Ingreso ${fecha_seleccionada}`)) {
				obj_labels.push(`Ingreso ${fecha_seleccionada}`);
				obj_ingreso.fechas_asignadas[`${fecha_seleccionada}`] = parseInt(registro.monto);
			}
			else{
				obj_ingreso.fechas_asignadas[`${fecha_seleccionada}`] += parseInt(registro.monto);
			}
		}
		else{
			if (filtro == "año" || filtro == "semestre") {
				let [anio,mes_d,dia] = registro.fecha.split("-")
				let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
				let mes = fecha_buscar.getMonth();
				let nombreMes = meses[mes];
				let anio_fecha = fecha_buscar.getFullYear();

				fecha_seleccionada = `${nombreMes} del ${anio_fecha}`;
			}
			else if (filtro == "mes" || filtro == "trimestre") {
				let [anio,mes_d,dia] = registro.fecha.split("-")
				let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
				let semanaDelMes = Math.ceil(fecha_buscar.getDate() / 7);
				let mes = fecha_buscar.getMonth();
				let nombreMes = meses[mes];
				let sufijo = ((semanaDelMes == 1 || semanaDelMes == 3)?"ra":((semanaDelMes == 2)?"da":"ta"));

				fecha_seleccionada = `${semanaDelMes}${sufijo} Semana, ${nombreMes}`;
				
			}
			else if (filtro == "Otro") {
				let dia = registro.fecha.split("-")[2]
				let mes = parseInt(registro.fecha.split("-")[1]) - 1;
				fecha_seleccionada = `${meses[mes]} ${dia}`;
			}

			if (!meses_anios.includes(fecha_seleccionada)) meses_anios.push(fecha_seleccionada);

			if (!obj_labels.includes(`Egreso ${fecha_seleccionada}`)) {
				obj_labels.push(`Egreso ${fecha_seleccionada}`);				
				obj_egreso.fechas_asignadas[`${fecha_seleccionada}`] = parseInt(registro.monto);
			}
			else{
				obj_egreso.fechas_asignadas[`${fecha_seleccionada}`] += parseInt(registro.monto);
			}
		}
	});
	
	obj_labels.map(label=>{
		let balance = (label.includes("Egreso"))?"Egreso":"Ingreso";
		if (balance == "Egreso") {
			if(!(obj_labels.includes("Ingreso"+label.split("Egreso")[1]))){
				obj_labels.push("Ingreso"+label.split("Egreso")[1]);
				obj_ingreso.fechas_asignadas[label.split("Egreso ")[1]] = 0;
			}
		}
		else{
			if(!(obj_labels.includes("Egreso"+label.split("Ingreso")[1]))){
				obj_labels.push("Egreso"+label.split("Ingreso")[1]);
				obj_egreso.fechas_asignadas[label.split("Ingreso ")[1]] = 0;
			}
		}		
	});

	let arreglo_obj = Object.entries(obj_ingreso.fechas_asignadas);

	let arreglo_ordenado = [];
	//Es para semanas
	if (filtro == "mes" || filtro == "trimestre") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(", ")[1]) - meses.indexOf(b[0].split(", ")[1]);
		});
	}
	//Es para meses
	else if (filtro == "año" || filtro == "semestre") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(" ")[0]) - meses.indexOf(b[0].split(" ")[0]);
		});
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return a[0].split(" ")[2] - b[0].split(" ")[2];
		});
	}
	else if (filtro == "Otro") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return a[0].split(" ")[1] - b[0].split(" ")[1];
		});
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(" ")[0]) - meses.indexOf(b[0].split(" ")[0]);
		});
	}
	arreglo_ordenado.map(fecha=>{		
		obj_ingreso.data.push(fecha[1]);
	});

	arreglo_obj = Object.entries(obj_egreso.fechas_asignadas);

	//Es para semanas
	if (filtro == "mes" || filtro == "trimestre") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(", ")[1]) - meses.indexOf(b[0].split(", ")[1]);
		});
	}
	//Es para meses
	else if (filtro == "año" || filtro == "semestre") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(" ")[0]) - meses.indexOf(b[0].split(" ")[0]);
		});
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return a[0].split(" ")[2] - b[0].split(" ")[2];
		});
	}
	else if (filtro == "Otro") {
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return a[0].split(" ")[1] - b[0].split(" ")[1];
		});
		arreglo_ordenado = arreglo_obj.sort((a, b) => {
			return meses.indexOf(a[0].split(" ")[0]) - meses.indexOf(b[0].split(" ")[0]);
		});
	}
	arreglo_ordenado.map(fecha=>{		
		obj_egreso.data.push(fecha[1]);
	});

	fechas_asignadas_ingresos = obj_ingreso.fechas_asignadas;
	fechas_asignadas_egresos = obj_egreso.fechas_asignadas;

	if (document.getElementById('select_mostrar_datos').value == "solo_texto") {
		if (graficaChart) {
			graficaChart.clear();
		  	graficaChart.destroy();
		}
		document.getElementById('canva').setAttribute("hidden","");
		document.getElementById('titulo_grafico').setAttribute("hidden","");
		crearContanierEstadistica(resultado_estadisticas.mensaje);
		document.getElementById('contenedor_estadistica').removeAttribute("hidden");
		return;
	}
	else if (document.getElementById('select_mostrar_datos').value == "grafico_texto") {
		crearContanierEstadistica(resultado_estadisticas.mensaje);
		document.getElementById('contenedor_estadistica').removeAttribute("hidden");
	}
	else{
		document.getElementById('contenedor_estadistica').setAttribute("hidden",'');
	}

	document.getElementById('canva').removeAttribute("hidden");
	document.getElementById('titulo_grafico').removeAttribute("hidden");

	const ctx = document.getElementById('canva').getContext('2d');

	if (graficaChart) {
		graficaChart.clear();
	  	graficaChart.destroy();
	}

	graficaChart = new Chart(ctx, {
	    type: 'bar',
	    data: {
	        labels: meses_anios,
	        datasets: [{
	        	label: obj_ingreso.label,
	            data: obj_ingreso.data,
	            backgroundColor: obj_ingreso.backgroundColor
	        },{
	        	label: obj_egreso.label,
	            data: obj_egreso.data,
	            backgroundColor: obj_egreso.backgroundColor
	        }]
	    },
	    options: {
	        scales: {
	            y: {
	                beginAtZero: true
	            }
	        }
	    }
	});
});

document.getElementById("balance").addEventListener("change",e=>{
	let select = document.getElementById("select_balance");
	if (select.getAttribute("disabled") == null) {
		if(select.value == "Ingresos"){
			document.getElementById("select_tipo_gasto").parentElement.removeAttribute("hidden");
			document.getElementById("tipo_gasto").parentElement.removeAttribute("hidden");
			document.getElementById("select_tipo_gasto").value = "Todos";
			document.getElementById("select_tipo_gasto").setAttribute("disabled","true");
		}

		select.value = "Todos";
		select.setAttribute("disabled","true");
	}else{
		select.removeAttribute("disabled");
	}	
});

document.getElementById("select_balance").addEventListener("change",e=>{
	if (e.target.value == "Ingresos"){
		document.getElementById("select_tipo_gasto").parentElement.setAttribute("hidden","");
		document.getElementById("select_tipo_gasto").value = "Todos";

		document.getElementById("tipo_gasto").parentElement.setAttribute("hidden","");
		document.getElementById("tipo_gasto").checked = false;
	}
	else{
		document.getElementById("select_tipo_gasto").parentElement.removeAttribute("hidden");
		document.getElementById("tipo_gasto").parentElement.removeAttribute("hidden");
	}
});

document.getElementById("metodo_pago").addEventListener("change",e=>{
	let select = document.getElementById("select_metodo_pago");
	if (select.getAttribute("disabled") == null) {
		document.getElementById("select_metodo_pago").value = "Todos";
		select.setAttribute("disabled","");
	}
	else{
		select.removeAttribute("disabled");
	}
});

document.getElementById("tipo_gasto").addEventListener("change",e=>{
	let select = document.getElementById("select_tipo_gasto");
	if (select.getAttribute("disabled") == null) {
		select.value = "Todos";
		select.setAttribute("disabled","true");
	}else{
		select.removeAttribute("disabled");
	}
});

document.getElementById("boton_generar").addEventListener("click",e=>{
	e.preventDefault();
	let img_barra = document.getElementById("canva").toDataURL("image/png");
	document.getElementById("barra").value = img_barra;

	document.getElementById('total_pagos_input').value = document.getElementById('total_pagos').textContent;
	document.getElementById('total_gastos_input').value = document.getElementById('total_gastos').textContent;
	document.getElementById('gastos_efectivo_input').value = document.getElementById('gastos_efectivo').textContent;
	document.getElementById('gastos_transferencia_input').value = document.getElementById('gastos_transferencia').textContent;
	document.getElementById('gastos_pago_movil_input').value = document.getElementById('gastos_pago_movil').textContent;
	document.getElementById('pagos_efectivo_input').value = document.getElementById('pagos_efectivo').textContent;
	document.getElementById('pagos_transferencia_input').value = document.getElementById('pagos_transferencia').textContent;
	document.getElementById('pagos_pago_movil_input').value = document.getElementById('pagos_pago_movil').textContent;
	document.getElementById('fecha_pagos_input').value = document.getElementById('fecha_pagos').textContent;
	document.getElementById('fecha_gastos_input').value = document.getElementById('fecha_gastos').textContent;
	document.getElementById('mostrar_datos_input').value = document.getElementById('select_mostrar_datos').value;

	modal.hide();
	e.target.closest("form").submit();
});

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

function crearContanierEstadistica(arreglo_estadisticas) {
	console.log(arreglo_estadisticas);
	limpiarContainerEstadistica();
	arreglo_estadisticas.map(registro=>{
		let estadistica = Object.values(registro);		

		if (estadistica[0] != 0) {

			let elemento = document.getElementById(estadistica[1]);
			if (elemento == null) return;
			if (estadistica[0] == null) {
				elemento.textContent += 0 + "Bs.";
				elemento.removeAttribute("no-asignado");
				return;
			}
			elemento.textContent += estadistica[0] + "Bs. / " + (estadistica[0] / tasa_dolar).toFixed(2) + "$";
			elemento.removeAttribute("no-asignado");
		}
	});
	let fragment_fecha_pagos = document.createDocumentFragment();
	for(let tiempo in fechas_asignadas_ingresos){
		let p = document.createElement("p");
		p.textContent = tiempo + ": " + fechas_asignadas_ingresos[tiempo] + "Bs. / " + (fechas_asignadas_ingresos[tiempo] / tasa_dolar).toFixed(2) + "$";
		fragment_fecha_pagos.appendChild(p);
	}
	let fragment_fecha_gastos = document.createDocumentFragment();
	for(let tiempo in fechas_asignadas_egresos){
		let p = document.createElement("p");
		p.textContent = tiempo + ": " + fechas_asignadas_egresos[tiempo] + "Bs. / " + (fechas_asignadas_egresos[tiempo] / tasa_dolar).toFixed(2) + "$";
		fragment_fecha_gastos.appendChild(p);
	}

	if(fragment_fecha_pagos.children.length == 0){
		let p = document.createElement("p");
		p.textContent = "No hay marcas de tiempo";
		fragment_fecha_pagos.appendChild(p);
	}
	if(fragment_fecha_gastos.children.length == 0){
		let p = document.createElement("p");
		p.textContent = "No hay marcas de tiempo";
		fragment_fecha_gastos.appendChild(p);
	}

	document.getElementById('fecha_pagos').appendChild(fragment_fecha_pagos);
	document.getElementById('fecha_gastos').appendChild(fragment_fecha_gastos);

	document.querySelectorAll("[no-asignado]").forEach(elemento=>{
		elemento.textContent += 0 + "Bs.";
	});
}

function limpiarContainerEstadistica() {
    document.getElementById('total_pagos').textContent = "Total de Pagos realizados: ";
    document.getElementById('total_pagos').setAttribute("no-asignado","");

    document.getElementById('total_gastos').textContent = "Total de Gastos Realizados: ";
    document.getElementById('total_gastos').setAttribute("no-asignado","");

    document.getElementById('gastos_efectivo').textContent = "Gastos por Efectivo: ";
    document.getElementById('gastos_efectivo').setAttribute("no-asignado","");

    document.getElementById('gastos_transferencia').textContent = "Gastos por Transferencia: ";
    document.getElementById('gastos_transferencia').setAttribute("no-asignado","");

    document.getElementById('gastos_pago_movil').textContent = "Gastos por Pago Movil: ";
    document.getElementById('gastos_pago_movil').setAttribute("no-asignado","");

    document.getElementById('pagos_efectivo').textContent = "Pagos por Efectivo: ";
    document.getElementById('pagos_efectivo').setAttribute("no-asignado","");

    document.getElementById('pagos_transferencia').textContent = "Pagos por Transferencia: ";
    document.getElementById('pagos_transferencia').setAttribute("no-asignado","");

    document.getElementById('pagos_pago_movil').textContent = "Pagos por Pago Movil: ";
    document.getElementById('pagos_pago_movil').setAttribute("no-asignado","");

    document.getElementById('fecha_pagos').textContent = null;
	document.getElementById('fecha_gastos').textContent = null;	
}