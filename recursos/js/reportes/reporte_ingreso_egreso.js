let datos_reporte = {
	fecha_inicio:'',
	fecha_fin:'',
	balance:'',
	metodo_pago:'',
	tipo_gasto:''
};

let obj_ingreso = {
	label:false,
	data:[],
	backgroundColor: ['#0079C2']
};
let obj_egreso = {
	label:false,
	data:[],
	backgroundColor: ['#e01d22']
};
let obj_labels = [];
let meses_anios=  [];

let boton_vista_previa = document.getElementById("boton_vista_previa");
let boton_balance = document.getElementById("balance");
let boton_metodo_pago = document.getElementById("metodo_pago");
let boton_tipo_gasto = document.getElementById("tipo_gasto");
let boton_generar = document.getElementById("boton_generar");

let modal = new bootstrap.Modal(document.getElementById("modal_reporte"));

document.addEventListener("DOMContentLoaded",()=>{
	console.log("cargado");
});
let resultado;
boton_vista_previa.addEventListener("click",async e=>{
	let fecha_inicio = document.getElementById('fecha_inicio');
	let fecha_fin = document.getElementById('fecha_fin');
	if (fecha_inicio.value == "" && fecha_fin.value == "") {
		mensajes('error',4000,'Atencion', "Debe colocar una fecha para la consulta");
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	datos_reporte.fecha_inicio = document.getElementById('fecha_inicio').value;
	datos_reporte.fecha_fin = document.getElementById('fecha_fin').value;
	datos_reporte.balance = document.getElementById('select_balance').value;
	datos_reporte.metodo_pago = document.getElementById('select_metodo_pago').value;	
	datos_reporte.tipo_gasto = document.getElementById('select_tipo_gasto').value;

	let datos_consulta = new FormData();

	datos_consulta.append("fecha_inicio",datos_reporte.fecha_inicio);
	datos_consulta.append("fecha_fin",datos_reporte.fecha_fin);
	datos_consulta.append("balance",datos_reporte.balance);
	datos_consulta.append("metodo_pago",datos_reporte.metodo_pago);
	datos_consulta.append("tipo_gasto",datos_reporte.tipo_gasto);

	datos_consulta.append("operacion","consultar_ingresos_egresos");

	resultado = await query(datos_consulta);

	console.log(resultado);
	// Resvisamos el resultado
	if(!(resultado.estatus)){
		mensajes('error',4000,'Atencion', resultado.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}
	let ultimo_id;
	const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
	resultado.mensaje.map(registro=>{
		if(registro.balance == "Ingreso"){
			obj_ingreso.label = 'Ingreso (BS.)';
			let [anio,mes_d,dia] = registro.fecha.split("-")
			let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
			let mes = fecha_buscar.getMonth();
			let nombreMes = meses[mes];
			let anio_fecha = fecha_buscar.getFullYear();
			// let mes_fecha = new Date(registro.fecha).toLocaleDateString('es-ES', { month: 'long' });
			// let anio_fecha = new Date(registro.fecha).getFullYear();
			if (!meses_anios.includes(`${nombreMes} del ${anio_fecha}`)) meses_anios.push(`${nombreMes} del ${anio_fecha}`);
			if (!obj_labels.includes(`Ingreso ${nombreMes} del ${anio_fecha}`)) {
				obj_labels.push(` Ingreso ${nombreMes} del ${anio_fecha}`);
				obj_ingreso.data.push(parseInt(registro.monto));
				ultimo_id = obj_ingreso.data.length - 1;
			}
			else{
				obj_ingreso.data[ultimo_id] += parseInt(registro.monto);
			}
		}
		else{
			obj_egreso.label = 'Egreso (BS.)';
			let [anio,mes_d,dia] = registro.fecha.split("-")
			let fecha_buscar = new Date(`${anio}/${mes_d}/${dia}`);
			let mes = fecha_buscar.getMonth();
			let nombreMes = meses[mes];
			let anio_fecha = fecha_buscar.getFullYear();

			if (!meses_anios.includes(`${nombreMes} del ${anio_fecha}`)) meses_anios.push(`${nombreMes} del ${anio_fecha}`);
			if (!obj_labels.includes(`Egreso ${nombreMes} del ${anio_fecha}`)) {
				obj_labels.push(`Egreso ${nombreMes} del ${anio_fecha}`);
				obj_egreso.data.push(parseInt(registro.monto));
				ultimo_id = obj_egreso.data.length - 1;
			}
			else{
				obj_egreso.data[ultimo_id] += parseInt(registro.monto);
			}
		}
	});
	const empleadosChart = new Chart(ctx, {
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
boton_balance.addEventListener("change",e=>{
	let select = document.getElementById("select_balance");
	if (select.getAttribute("disabled") == null) {
		select.setAttribute("disabled","true");
	}else{
		select.removeAttribute("disabled");
	}
});
boton_metodo_pago.addEventListener("change",e=>{
	let select = document.getElementById("select_metodo_pago");
	if (select.getAttribute("disabled") == null) {
		select.setAttribute("disabled","true");
	}else{
		select.removeAttribute("disabled");
	}
});
boton_tipo_gasto.addEventListener("change",e=>{
	let select = document.getElementById("select_tipo_gasto");
	if (select.getAttribute("disabled") == null) {
		select.setAttribute("disabled","true");
	}else{
		select.removeAttribute("disabled");
	}
});
boton_generar.addEventListener("click",e=>{
	e.preventDefault();
	let fecha_inicio = document.getElementById('fecha_inicio');
	let fecha_fin = document.getElementById('fecha_fin');
	if (fecha_inicio.value == "" && fecha_fin.value == "") {
		mensajes('error',4000,'Atencion', "Debe colocar una fecha para la consulta");
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}
	let img_barra = document.getElementById("canva").toDataURL("image/png");
	document.getElementById("barra").value = img_barra;

	boton_generar.closest("form").submit();
});


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

// esto solo es para decir que se completo o fallo una operacion
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

const ctx = document.getElementById('canva').getContext('2d');
