consultar(); // para llenar la tabla al cargar

//Si queremos consultar
async function consultar() {
	//Creamos el formData
	datos_consulta = new FormData();

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consulta');

	//Llamamos a la funcion para hacer la consulta
	data = await query(datos_consulta)
	vaciar_tabla(); //Vaciamos la tabla de lo que tenia antes
	
	// Resvisamos el resultado
	if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	//recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
	await data.map(fila=>{
		llenarTabla(fila);
	})
	
	data_table = init_data_table(); //iniciamos el dataTable de jquery
}

// Esta funcion hace lo que dice
function vaciar_tabla() {
	let cuerpo_tabla = document.querySelector(`#tabla_mensualidad tbody`);
	cuerpo_tabla.textContent = null;
}

// esta tambien, se ve larga, pero no es tan complicada  **********
// esta funcion crea filas para la tabla al momento de consultar
function llenarTabla(fila) {
	// seleccionamos el cuerpo de la tabla que vamos a llenar
	let cuerpo_tabla = document.querySelector(`#tabla_mensualidad tbody`);

	// Creamos etiquetas
	let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

	let id_campo = fila["id_usuario"]; // guardamos el id que nos interese
	
	// creamos un td por cada columna que vamos a llenar de la tabla <td></td>
	let mes_anio_td = document.createElement("td"),
	apartamento_td = document.createElement("td"),	
	propietario_td = document.createElement("td"), 
	tasa_td = document.createElement("td"), 
	monto_td = document.createElement("td");

	fecha = new Date(`${fila["mes"]}/01/${fila["anio"]}`);
	mes = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
	anio = fecha.getFullYear();

	// le damos el contenido de la consulta
	mes_anio_td.textContent = `${mes} del ${anio}`;
	apartamento_td.textContent = `Apatamento Nº ${fila["nro_apartamento"]}`;
	propietario_td.textContent = `${fila["nombre"]} ${fila["apellido"]}`;
	tasa_td.textContent = `${fila["tasa_dolar"]}bs`;
	monto_td.textContent = `${fila["monto"]}bs`;

	
	// le ponemos los td a la fila (tr)
	fila_tabla.appendChild(mes_anio_td);
	fila_tabla.appendChild(apartamento_td);
	fila_tabla.appendChild(propietario_td);
	fila_tabla.appendChild(monto_td);
	fila_tabla.appendChild(tasa_td);

	// y por ultimo, llenamos la tabla con la fila
	cuerpo_tabla.appendChild(fila_tabla);	
}

async function calcular_mensualidad() {
	/*
	todos los gastos del mes
	cantidad total de apartamentos
	tasa de dolar del dia
	tomar en cuenta los que pagan gas y no
	el random que no paga gastos fijos
	el agregado por incumplimiento de pago
	*/	
	let gastos_fijos, gastos_variables, gasto_gas;
	let apartamentos;

	let datos_consulta = new FormData();

	datos_consulta.append("operacion","gastos_fijos");
	gastos_fijos = await query(datos_consulta);

	datos_consulta.append("operacion","gastos_variables");
	gastos_variables = await query(datos_consulta);

	datos_consulta.append("operacion","gasto_gas");
	gasto_gas = await query(datos_consulta);

	datos_consulta.append("operacion","apartamentos"); 
	apartamentos = await query(datos_consulta);

	let total_gastos_fijos = 0, 
	total_gastos_variables = 0, 
	total_gasto_gas = 0,
	numero_apartamento_gas = 0;

	gastos_fijos.map(gasto=>{
		total_gastos_fijos += gasto.monto;
	});

	gastos_variables.map(gasto=>{
		total_gastos_variables += gasto.monto;
	});

	gasto_gas.map(gasto=>{
		total_gasto_gas += gasto.monto;
	});

	apartamentos.map(apartamento=>{
		numero_apartamento_gas += apartamento.gas;
	});

	let monto_gas_apartamento = total_gasto_gas/numero_apartamento_gas;

	//Tasa dolar de API
	let tasa_dolar = 100;

	apartamentos.map(async function(apartamento){
		let total_gastos = total_gastos_fijos + total_gastos_variables;
		let datos_consulta = new FormData();
		let monto_apartamento = (total_gastos * 100) / apartamento.porcentaje_participacion;
		monto_apartamento = parseFloat(monto_apartamento.toFixed(2));

		if (apartamento.gas) {
			monto_apartamento += monto_gas_apartamento;
		}
		datos_consulta.append("operacion","registrar");
		datos_consulta.append("monto",monto_apartamento);
		datos_consulta.append("tasa_dolar",tasa_dolar);
		datos_consulta.append("apartamento_id",apartamento.id_apartamento);

		let resultado = await query(datos_consulta);

		console.log(resultado);
	})
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
	return new DataTable("#tabla_mensualidad",{
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