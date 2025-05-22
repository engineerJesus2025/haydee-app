/*
PELIGRO: no terminado

Ni se molesten en leerlo, yo apenas lo entiendo
*/
let data_table, referencia_an
// id_eliminado, id_registrado, id_modificar;
// VAriables que usaremos mas tarde
//guardamos los permisosdel usuario
let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
/*
TO DO
conciliacion:
* al inicio del mes crear una conciliacion del mes que paso sin procesar :D
* cuando entre buscar los mes no conciliados :D
* dar la opcion de cambiar de meses no conciliados :D
* mostrar pagos y gastos del mes seleccionado :D
* Agregar Validaciones :D
* en la tabla dar la opcion para registrar movimiento bancario (form) :D
* funcionalidad para registrar :d
* posteriormente opciones para editar y eliminar:D
* consultar y llenar formulario al editar :D
* funcionalidad a eliminar :D
* dar la opcion para registrar un pago no correspondido por le sistema :D
* dar la opcion para marcar un registro no correspondido por el banco :D
* Calcular conciliacion y mostrarla
* al final un boton para guardar conciliacion bancaria
* ver conciliaciones (no se si cambiar la tabla o mostrarlo en un modal)
* Agregar bitacora
* confirmaciones con boostap
* Actualizar data_table
* debatir si usar data table :/
*/

buscarMesesNoConciliados();

// Asignacion de variables
let fecha_seleccionada = null;
let tabla_registros_sistema = document.querySelector("#tabla_registros_sistema"); //La tabla
let tabla_movimientos = document.querySelector("#tabla_movimientos_sistema");
let modal_movimientos = new bootstrap.Modal("#modal_movimientos"); // el modal
let formulario_usar = document.querySelector(`#form_movimientos`); // el form
let boton_formulario = document.getElementById("boton_formulario");

//Eventos:
//Asignamos el evento select para que cada que cambie llene la tabla de registros del sistema
document.getElementById("mes_select").addEventListener("change",e=>{
	fecha_seleccionada = e.target.value;
	llenarTablaRegistrosSistema();
});

// resetear los datos del form al quiatrlo (evento)
document.getElementById('modal_movimientos').addEventListener("hide.bs.modal",e=>{
	formulario_usar.reset();

	let monto_s  = document.getElementById("monto_sistema"),
	pago_gasto_s  = document.getElementById("pago_gasto_sistema"),
	fecha_s  = document.getElementById("fecha_sistema"),
	referencia_s  = document.getElementById("referencia_sistema"),
	tipo_m = document.getElementById("tipo_movimiento");
	//Seleccionamos los input de resumen
	let diferencia_m = document.getElementById("diferencia_monto"),
	diferencia_t = document.getElementById("diferencia_tipo"),
	estado = document.getElementById("estado");
	//Seleccionamos los titulos
	let titulo2 = document.getElementById("registro_sistema"),
	titulo3 = document.getElementById("resumen");

	monto_s.parentElement.parentElement.removeAttribute("hidden");
	pago_gasto_s.parentElement.parentElement.removeAttribute("hidden");
	fecha_s.parentElement.parentElement.removeAttribute("hidden");
	referencia_s.parentElement.parentElement.removeAttribute("hidden");
	diferencia_m.parentElement.parentElement.removeAttribute("hidden");
	diferencia_t.parentElement.parentElement.removeAttribute("hidden");
	estado.parentElement.parentElement.removeAttribute("hidden");
	titulo2.removeAttribute("hidden");
	titulo3.removeAttribute("hidden");

	pago_gasto_s.parentElement.previousElementSibling.textContent = "Pago realizado por:";
	fecha_s.parentElement.previousElementSibling.textContent = "Fecha de Registro de Pago:";
	document.getElementById('titulo_modal_movimientos').textContent = "Registrar Movimiento Bancario";
	boton_formulario.textContent = "Registrar Movimiento";

	for (opcion of tipo_m){
		if (opcion.value == "Ingreso" || opcion.value == "Egreso") {
			opcion.removeAttribute("selected");
		}
	}

	tipo_m.setAttribute("disabled","");
	tipo_m.setAttribute("readonly","");
});

// LLenar fila resumen al escribir en monto (evento)
document.getElementById("monto_movimiento").addEventListener("keyup",e=>{
	let monto_escrito = parseInt(e.target.value);
	let monto_sistema = parseInt(document.getElementById("monto_sistema").value.split("B")[0]);
	let diferencia;
	let monto_diferencia = monto_escrito - monto_sistema;
	let estado;

	if (monto_escrito > monto_sistema) {
		diferencia = "Adicional";
		estado = "El saldo bancario no coincide con el contable";
	}else if(monto_escrito < monto_sistema){
		diferencia = "Faltante";
		estado = "El saldo bancario no coincide con el contable";
	}else{
		diferencia = "Neutro";
		estado = "Todo en Orden";
	}

	// Ponemos los valores en los inputs:
	document.getElementById("diferencia_tipo").value = diferencia;
	document.getElementById("diferencia_monto").value = Math.abs(monto_diferencia);
	document.getElementById("estado").value = estado;	
});

//Funciones:
async function crearConciliacionBancaria() {
	//Revisar si no hay del mes pasado
	let datos_consulta = new FormData();
	datos_consulta.append("operacion","verificar_conciliacion");
	let exite = await query(datos_consulta);
	if (exite != '') {return;}

	datos_consulta = new FormData();
	datos_consulta.append("operacion","crear_conciliacion");
	let creada = await query(datos_consulta);	
}

async function buscarMesesNoConciliados() {
	//Revisar si no hay del mes pasado
	let datos_consulta = new FormData();
	datos_consulta.append("operacion","verificar_meses_conciliados");
	let meses_no_conciliados = await query(datos_consulta);

	let span_select = document.querySelector("#span_select");

	if (meses_no_conciliados.length == 0) {
		span_select.textContent = `No hay meses por conciliar`;
		return;
	}
	
	span_select.textContent = `Hay ${meses_no_conciliados.length} mes${(meses_no_conciliados.length > 1)?'es':''} que falta${(meses_no_conciliados.length > 1)?'n':''} por conciliación bancaria`

	let select = document.querySelector("#mes_select");

	meses_no_conciliados.map(mes=>{
		let option = document.createElement("option");
		fecha = new Date(mes.fecha_inicio);
		mes_buscar = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
		anio_buscar = fecha.getFullYear();

		option.textContent = `${mes_buscar} del ${anio_buscar}`;
		option.value = `${mes.fecha_inicio}`;
		option.id = mes.id_conciliacion;

		select.appendChild(option);
	});
}

async function llenarTablaRegistrosSistema(){
	let datos_consulta = new FormData();

	datos_consulta.append("operacion","buscar_mes");
	datos_consulta.append("fecha",fecha_seleccionada);

	let resultados = await query(datos_consulta);

	let tabla = document.querySelector("#tabla_registros_sistema tbody");

	// console.log(tabla, resultados);

	let fragment = document.createDocumentFragment();

	resultados.map(resultado=>{
		
		let fila = document.createElement("tr");

		let acciones_td = document.createElement("td");
		let ingre_egre_td = document.createElement("td");
		let fecha_td = document.createElement("td");
		let monto_td = document.createElement("td");
		let referencia_td = document.createElement("td");
		let apar_prove_td = document.createElement("td");

		acciones_td.setAttribute("class","text-center");
		ingre_egre_td.setAttribute("class","text-center");
		fecha_td.setAttribute("class","text-center");
		monto_td.setAttribute("class","text-center");
		referencia_td.setAttribute("class","text-center");
		apar_prove_td.setAttribute("class","text-center");

		let accion = (resultado.estado == "fijo" || resultado.estado == "variable")?"Egreso":"Ingreso";

		fila.setAttribute("id",`${accion}-${resultado.id}`);

		let boton_conciliado = crearBoton("Conciliado");
		acciones_td.appendChild(boton_conciliado);
		ingre_egre_td.textContent = accion;
		fecha_td.textContent = resultado.fecha;
		monto_td.textContent = resultado.monto + "Bs.";
		referencia_td.textContent = resultado.referencia;
		apar_prove_td.textContent = `${(accion == "Ingreso")?"Ap. Nº ":''}${resultado.remitente}`;

		fila.appendChild(acciones_td);
		fila.appendChild(ingre_egre_td);
		fila.appendChild(fecha_td);
		fila.appendChild(monto_td);
		fila.appendChild(referencia_td);
		fila.appendChild(apar_prove_td);

		fragment.appendChild(fila);
	})

	tabla.textContent = null;
	tabla.appendChild(fragment);

	data_table = await init_data_table();
	llenarTablaMovimientos();
}

async function llenarTablaMovimientos() {
	let tabla_registros_filas = tabla_registros_sistema.querySelectorAll("tbody tr");
	let tabla_movimientos_tbody = tabla_movimientos.querySelector("tbody");

	let datos_consulta = new FormData();

	datos_consulta.append("operacion","consultar_movimientos");
	datos_consulta.append("fecha",fecha_seleccionada);

	let movimientos_bancarios = await query(datos_consulta);

	let fragment = document.createDocumentFragment();
	tabla_registros_filas.forEach((fila,index)=>{

		let accion = (fila.id.includes("Ingreso"))?"Ingreso":"Egreso";
		let id_accion = fila.id.split("-")[1];
		
		let fila_tr = document.createElement("tr");
		let td_monto = document.createElement("td");
		let td_accion = document.createElement("td");

		let ultima = false;
		let tr_ultima;

		let relacion = false;

		movimientos_bancarios.map((movimiento,index_m)=>{
			let boton_editar;
			let boton_eliminar;
			// console.log(tabla_registros_filas.length == index + 1 && movimientos_bancarios.length == index_m + 1, index_m+1, index+1);
			if (movimiento.pago_id == null && movimiento.gasto_id == null) {
				// Revisamos si ya la pusimos 0.0
				let existe = fila.parentElement.querySelector(`#Movimiento-${movimiento.id_movimiento}`);
				if (existe) {return;}
				if (!(fila.children[2].textContent > movimiento.fecha && 
					movimiento.fecha.split("-")[2] < new Date(movimiento.fecha.split("-")[0],movimiento.fecha.split("-")[1] + 1,0).getDate()
					) && !(tabla_registros_filas.length == index + 1 && movimientos_bancarios.length == index_m + 1)) {return;}
				//Insertando en registro-sistema
				let fila_sistema = document.createElement("tr");
				fila_sistema.setAttribute("id",`Movimiento-${movimiento.id_movimiento}`);

				let td_estatus = document.createElement("td");
				let td_sistema = document.createElement("td");
				td_sistema.setAttribute("colspan",5);
				td_sistema.setAttribute("class","text-center");
				td_sistema.textContent = `No hay Registro en el sistema para este Movimiento`;

				let boton_estatus = crearBoton("Sin Correspondencia",true);
				td_estatus.appendChild(boton_estatus);
				td_estatus.setAttribute("class","text-center");
				// Recursividad Papa

				fila_sistema.appendChild(td_estatus);
				fila_sistema.appendChild(td_sistema);
				// El padre le dice al hijo que se lo meta despues <..>

				let fila_tr_otro = document.createElement("tr");
				let td_monto_otro = document.createElement("td");
				let td_accion_otro = document.createElement("td");

				// Poner en la tabla movimientos el valor
				fila_tr_otro.setAttribute("id",`Movimiento-${movimiento.id_movimiento}`);

				td_monto_otro.textContent = `${movimiento.monto}bs`;
				
				boton_editar = crearBoton("Editar",fila.id,`Ninguna-${movimiento.tipo_movimiento}`,movimiento.id_movimiento);
				boton_eliminar = crearBoton("Eliminar",movimiento.id_movimiento);
				// boton_agregar_no_correspondido = crearBoton("No Correspondido",fila.id,'',movimiento.id_movimiento);

				td_accion_otro.appendChild(boton_editar);
				td_accion_otro.appendChild(boton_eliminar);
				// td_accion_otro.appendChild(boton_agregar_no_correspondido);

				fila_tr_otro.appendChild(td_monto_otro);
				fila_tr_otro.appendChild(td_accion_otro);
				fragment.appendChild(fila_tr_otro);

				ultima = tabla_registros_filas.length == index + 1 && movimientos_bancarios.length == index_m + 1;
				if (ultima) {
					tr_ultima = fila_tr_otro;
					fila.parentElement.insertBefore(fila_sistema,fila.nextSibling);
				}else{
					fila.parentElement.insertBefore(fila_sistema,fila);
				}		
			}
			if (accion == "Ingreso") {
				if (movimiento.pago_id == id_accion) {
					// Poner en la tabla movimientos el valor
					fila_tr.setAttribute("id",fila.id);

					td_monto.textContent = `${movimiento.monto}bs`;

					boton_editar = crearBoton("Editar",fila.id,accion,movimiento.id_movimiento);
					boton_eliminar = crearBoton("Eliminar",movimiento.id_movimiento);
					boton_agregar_no_correspondido = crearBoton("No Correspondido",fila.id,'',movimiento.id_movimiento);

					td_accion.appendChild(boton_editar);
					td_accion.appendChild(boton_eliminar);
					td_accion.appendChild(boton_agregar_no_correspondido);

					fila_tr.appendChild(td_monto);
					fila_tr.appendChild(td_accion);

					relacion = true;
					// si la diferencia no es 0 modificar el bton de estado
					if (parseInt(movimiento.monto_diferencia) != 0) {
						let estado_registro = document.getElementById(fila.id).children[0];
						let boton_diferencia = crearBoton("No Conciliado");
						estado_registro.textContent = null;
						estado_registro.appendChild(boton_diferencia);
					}
				}
			}
			else if (accion == "Egreso") {
				if (movimiento.gasto_id == id_accion) {
					// Poner en la tabla movimientos el valor
					fila_tr.setAttribute("id",fila.id);

					td_monto.textContent = `${movimiento.monto}bs`;
					
					boton_editar = crearBoton("Editar",fila.id,accion,movimiento.id_movimiento);
					boton_eliminar = crearBoton("Eliminar",movimiento.id_movimiento);
					boton_agregar_no_correspondido = crearBoton("No Correspondido",fila.id,'',movimiento.id_movimiento);

					td_accion.appendChild(boton_editar);
					td_accion.appendChild(boton_eliminar);
					td_accion.appendChild(boton_agregar_no_correspondido);

					fila_tr.appendChild(td_monto);
					fila_tr.appendChild(td_accion);

					relacion = true;
					// si la diferencia no es 0 modificar el bton de estado
					if (parseInt(movimiento.monto_diferencia) != 0) {
						let estado_registro = document.getElementById(fila.id).children[0];
						let boton_diferencia = crearBoton("No Conciliado");
						estado_registro.textContent = null;
						estado_registro.appendChild(boton_diferencia);
					}
				}
			}
		});

		if (!relacion) {
			// Poner en la tabla que esta vacio
			let boton_agregar;

			td_monto.textContent = `No registrado`;

			boton_agregar = crearBoton("Agregar",fila.id,accion);
			boton_agregar_no_correspondido = crearBoton("No Correspondido",fila.id);

			td_accion.appendChild(boton_agregar);
			td_accion.appendChild(boton_agregar_no_correspondido);

			fila_tr.appendChild(td_monto);
			fila_tr.appendChild(td_accion);

			let estado_registro = document.getElementById(fila.id).children[0];
			let boton_diferencia = crearBoton("Sin Movimiento");
			estado_registro.textContent = null;
			estado_registro.appendChild(boton_diferencia);

			if (ultima) {
				fragment.insertBefore(fila_tr,tr_ultima);
				return;
			}
		}

		fragment.appendChild(fila_tr);
	});

	tabla_movimientos_tbody.textContent = null;
	tabla_movimientos_tbody.appendChild(fragment);
}

function crearBoton(boton_nombre,id = '', accion = '',id_movimiento = '') {
	// Creamos los botones de las acciones
	// es como el crear botones 2.0
	let boton = document.createElement("button");

	if (boton_nombre == "Agregar") {
		let icono_agregar = document.createElement("i");
		icono_agregar.setAttribute("class", "bi bi-plus-lg");
		boton.appendChild(icono_agregar);

		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-primary btn-sm me-1");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");
		boton.setAttribute("data-bs-toggle", "modal");
		boton.setAttribute("data-bs-target", "#modal_movimientos"); // ojo
		boton.setAttribute("value",id);
		boton.setAttribute("title","Agregar Movimiento");

		boton.addEventListener("click",e=>{
			// Le damos al boton la info que necesita
			boton_formulario.setAttribute("id",id);
			boton_formulario.setAttribute("op","Registrar");

			// preparamos el formulario
			prepararFormulario(accion,id);
		});
	}
	if (boton_nombre == "Editar") {
		let icono_editar = document.createElement("i");
		icono_editar.setAttribute("class", "bi bi-pencil-square");
		boton.appendChild(icono_editar);

		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-success btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");
		boton.setAttribute("data-bs-toggle", "modal");
		boton.setAttribute("data-bs-target", "#modal_movimientos"); // ojo
		boton.setAttribute("title","Ver mas/Editar");
		boton.setAttribute("value",id_movimiento);

		boton.addEventListener("click",e=>{
			// Le damos al boton la info que necesita
			boton_formulario.setAttribute("id",id_movimiento);
			boton_formulario.setAttribute("op","Editar");

			document.getElementById('titulo_modal_movimientos').textContent = "Modificar Movimiento Bancario";
			boton_formulario.textContent = "Guardar Cambios";

			// preparamos el formulario
			prepararFormulario(accion,id,id_movimiento);
			llenarFormulario(id_movimiento);
		});
	}
	if (boton_nombre == "Eliminar") {
		let icono_eliminar = document.createElement("i");// le ponemos un icono
		icono_eliminar.setAttribute("class", "bi bi-trash");// y estilos
		boton.appendChild(icono_eliminar);

		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-danger btn-sm mx-1");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");		

		boton.setAttribute("title","Eliminar Movimiento");
		boton.setAttribute("value",id);

		boton.addEventListener("click",e=>{
			Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea eliminar este Movimiento?",
			showCancelButton: true,
			confirmButtonText: "Eliminar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
				if (resultado.isConfirmed) {
					eliminarMovimiento(id);
				}
			});
		});
	}
	if (boton_nombre == "Conciliado") {
		let icono_check = document.createElement("i");
		icono_check.setAttribute("class", "bi bi-check2");
		boton.appendChild(icono_check);

		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-success btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title","Conciliado");
	}
	if (boton_nombre == "No Conciliado") {
		let icono_x = document.createElement("i");
		icono_x.setAttribute("class", "bi bi-x-lg");
		boton.appendChild(icono_x);
		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-danger btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title","No Conciliado");		
	}
	if (boton_nombre == "Sin Movimiento") {
		let icono_question = document.createElement("i");
		icono_question.setAttribute("class", "bi bi-question-lg");
		boton.appendChild(icono_question);
		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-secondary btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title","Marcar como sin Movimiento Bancario Asociado");
		// Evento para marcar como "Sin movimiento asociado"
		boton.addEventListener("click",e=>{

			let id_tabla_sistema = boton.closest("tr").id;

			let fila_movimientos = tabla_movimientos.querySelector(`[value='${id_tabla_sistema}']`).closest("tr");
			fila_movimientos.textContent = null;

			let td = document.createElement("td");
			td.textContent = "Sin movimiento Asociado";
			td.setAttribute("colspan",2);

			fila_movimientos.appendChild(td);		
			boton.outerHTML = crearBoton("No Correspondido por Banco").outerHTML;			
			// Recursividad No joda
		});
	}
	if (boton_nombre == "Sin Correspondencia") {
		let icono_question = document.createElement("i");
		icono_question.setAttribute("class", "bi bi-journal-x");
		boton.appendChild(icono_question);
		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-info btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title",`Movimiento no Correspondido Por El ${(id)?"Banco":"Sistema"}`);
	}
	if (boton_nombre == "No Correspondido") {
		let icono_inferior = document.createElement("i");
		icono_inferior.setAttribute("class", "bi bi-chevron-bar-down");
		boton.appendChild(icono_inferior);
		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-warning btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title","Agregar Movimiento Bancario no Correspondido");

		boton.addEventListener("click",e=>{
			//Crear fila e insertarla debajo
			let fila = tabla_registros_sistema.querySelector("#"+id);
			//Insertando en registro-sistema
			let fila_sistema = document.createElement("tr");
			fila_sistema.setAttribute("id",`Movimiento-${id_movimiento}`);

			let td_estatus = document.createElement("td");
			let td_sistema = document.createElement("td");
			td_sistema.setAttribute("colspan",5);
			td_sistema.setAttribute("class","text-center");
			td_sistema.textContent = `No hay Registro en el sistema para este Movimiento`;

			let boton_estatus = crearBoton("Sin Correspondencia",true);
			td_estatus.appendChild(boton_estatus);
			td_estatus.setAttribute("class","text-center");
			// Recursividad Papa

			fila_sistema.appendChild(td_estatus);
			fila_sistema.appendChild(td_sistema);
			// El padre le dice al hijo que se lo meta despues <..>
			fila.parentElement.insertBefore(fila_sistema,fila.nextSibling);

			// Insertando en movimientos
			let fila_movimientos = document.createElement("tr");

			let td_movimiento_monto = document.createElement("td");
			td_movimiento_monto.textContent = 'No registrado';

			let td_movimiento_accion = document.createElement("td");
			let boton_agregar = crearBoton("Agregar",null,"Ninguna");
			// let boton_eliminar = crearBoton("Eliminar",movimiento.id_movimiento);

			td_movimiento_accion.appendChild(boton_agregar);
			// Mas Recursividad Papa

			fila_movimientos.appendChild(td_movimiento_monto);
			fila_movimientos.appendChild(td_movimiento_accion);

			// Debieron ver la fumada que habia escrito aqui AJAJASJ:
			// console.log();
			e.target.closest("tbody").insertBefore(fila_movimientos,e.target.closest("tr").nextSibling);
			// Basicamente es el papa del papa del papa...*Inserte reggaeton*
			// Despues descrubi como hacerlo mas corto pero ahhhhhhh

			// Llamar al form
		})
	}
	if (boton_nombre == "No Correspondido por Banco") {
		let icono_check = document.createElement("i");
		icono_check.setAttribute("class", "bi bi-check2");
		boton.appendChild(icono_check);

		boton.setAttribute("type", "button");
		boton.setAttribute("class", "btn btn-primary btn-sm");
		boton.setAttribute("tabindex", "-1");
		boton.setAttribute("role", "button");
		boton.setAttribute("aria-disabled", "true");

		boton.setAttribute("title","Movimiento No correspondido por el banco");

	}

	return boton;
}

function prepararFormulario(accion,id_fila,id_movimiento = '') {
	// Llenar los datos de sistema :D
	// seleccionar fila de registros de sistema:D
	let fila_selec = document.getElementById(id_fila);

	// seleccionar inputs de formulario :D
	let monto_s  = document.getElementById("monto_sistema"),
	pago_gasto_s  = document.getElementById("pago_gasto_sistema"),
	fecha_s  = document.getElementById("fecha_sistema"),
	referencia_s  = document.getElementById("referencia_sistema"),
	tipo_m = document.getElementById("tipo_movimiento");

	// si es un pago no coorespondido por el sistema
	if (accion.includes("Ninguna")) {
		tipo_m.removeAttribute("disabled");
		tipo_m.removeAttribute("readonly");

		//Seleccionamos los input de resumen
		let diferencia_m = document.getElementById("diferencia_monto"),
		diferencia_t = document.getElementById("diferencia_tipo"),
		estado = document.getElementById("estado");

		//Seleccionamos los titulos
		let titulo2 = document.getElementById("registro_sistema"),
		titulo3 = document.getElementById("resumen");

		// Escondemos todo eso
		monto_s.parentElement.parentElement.setAttribute("hidden","");
		pago_gasto_s.parentElement.parentElement.setAttribute("hidden","");
		fecha_s.parentElement.parentElement.setAttribute("hidden","");
		referencia_s.parentElement.parentElement.setAttribute("hidden","");
		diferencia_m.parentElement.parentElement.setAttribute("hidden","");
		diferencia_t.parentElement.parentElement.setAttribute("hidden","");
		estado.parentElement.parentElement.setAttribute("hidden","");
		titulo2.setAttribute("hidden","");
		titulo3.setAttribute("hidden","");

		for (opcion of tipo_m){
			if (opcion.value == accion.split("-")[1]) {
				opcion.selected = true;
			}
		}
		estado.value = "Ninguna";
		return;
	}

	// Llenamos el select de tipo de movimiento de acuerdo a la accion :D
	for (opcion of tipo_m){
		if (opcion.value == accion) {
			opcion.selected = true;
		}
	}

	// Le damos valor a esos inputs :D
	monto_s.value = fila_selec.childNodes[3].textContent;
	pago_gasto_s.value = fila_selec.childNodes[5].textContent;
	fecha_s.value = fila_selec.childNodes[2].textContent;
	referencia_s.value = fila_selec.childNodes[4].textContent;	

	// Cambiar los nombres de acuerdo al tipo de movimiento :D
	if (accion == "Egreso") {
		pago_gasto_s.parentElement.previousElementSibling.textContent = "Servicio Pagado:";
		fecha_s.parentElement.previousElementSibling.textContent = "Fecha de Registro de Gasto:";
	}	
}

//Si queremos registrar:
async function registrar(id_fila) {
	// Realmente no es registrar, porque se crea una conciliacion a inicio
	// de mes, se edita el registro que ya esta

	if (id_fila != "null") {
		let accion = id_fila.split("-")[0];
		let id_accion = id_fila.split("-")[1];
	}
	
	let select = document.querySelector("#mes_select");
	// para tomar el id de conciliacion a la que le vamos a meter esto

	//Creamos el formData
	datos_consulta = new FormData();

	//Creamos las variables con los datos de los inputs
	let fecha = formulario_usar.querySelector("#fecha_movimiento").value,
	monto = formulario_usar.querySelector("#monto_movimiento").value,	
	referencia = formulario_usar.querySelector("#referencia_movimiento").value, 
	tipo_movimiento = formulario_usar.querySelector("#tipo_movimiento").value,
	conciliacion_id = select[select.options.selectedIndex].id,
	banco_id = formulario_usar.querySelector("#banco_movimiento").value;

	if (id_fila != "null") {		
		let monto_diferencia = formulario_usar.querySelector("#diferencia_monto").value,
		tipo_diferencia = formulario_usar.querySelector("#diferencia_tipo").value,
		gasto_pago = accion,
		gasto_pago_id = id_accion;
	}

	// le pasamos los datos por el formData
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("referencia",referencia);	
	datos_consulta.append("tipo_movimiento",tipo_movimiento);	
	datos_consulta.append("conciliacion_id",conciliacion_id);
	datos_consulta.append("banco_id",banco_id);
	if (id_fila != "null") {
		datos_consulta.append("monto_diferencia",monto_diferencia);
		datos_consulta.append("tipo_diferencia",tipo_diferencia);
		datos_consulta.append("gasto_pago",gasto_pago);
		datos_consulta.append("gasto_pago_id",gasto_pago_id);
	}else{
		datos_consulta.append("monto_diferencia",0);
		datos_consulta.append("tipo_diferencia","No hay");
		datos_consulta.append("gasto_pago","Ninguna");
		datos_consulta.append("gasto_pago_id","Ninguna");
	}

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','registrar_movimiento');
	
	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta); 
	console.log(respuesta);
	modal_movimientos.hide(); //Esconde el modal
	formulario_usar.reset();//Limpia el formulario

	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	//Actualizar las tablas
	llenarTablaRegistrosSistema();

	data_table.draw(); // glu glu *Se refresca*

	// Dar mensaje de exito
	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');//Mensaje de que se completo la operacion
}

async function llenarFormulario(id){
	//Creamos el formData
	datos_consulta = new FormData();

	// le pasamos los datos por el formData
	datos_consulta.append("id_movimiento",id);	

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','consultar_movimiento');

	respuesta = await query(datos_consulta);

	// Resvisamos el resultado
	if(!(respuesta.estatus == undefined)) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	// Llenamos el formulario
	let monto = formulario_usar.querySelector("#monto_movimiento"),
	fecha = formulario_usar.querySelector("#fecha_movimiento"),	
	referencia = formulario_usar.querySelector("#referencia_movimiento"),	
	banco = formulario_usar.querySelector("#banco_movimiento"),
	diferencia_tipo = formulario_usar.querySelector("#diferencia_tipo"),
	diferencia_monto = formulario_usar.querySelector("#diferencia_monto");

	// le damos valor
	monto.value = respuesta.monto;
	fecha.value = respuesta.fecha;	
	referencia.value = respuesta.referencia;
	banco.value = respuesta.banco_id;
	diferencia_tipo.value = respuesta.tipo_diferencia;
	diferencia_monto.value = respuesta.monto_diferencia;

	referencia_an = referencia.value;// Guardamos la ref para futuras validaciones

	// este if revisa si tiene permiso para editar, en caso de que no, quitamos el boton
	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
		//si no los tiene apaga el boton.
	}

	// Le damos al boton la info que necesita
	boton_formulario.setAttribute("id",id);
	boton_formulario.setAttribute("op","Editar");
}

async function editarMovimiento(id){
	let select = document.querySelector("#mes_select");

	//Creamos el formData
	let datos_consulta = new FormData();

	let estado = document.getElementById("estado");

	//Guardamos los datos del formulario
	let id_movimiento = id,
	fecha = formulario_usar.querySelector("#fecha_movimiento").value,
	monto = formulario_usar.querySelector("#monto_movimiento").value, 	
	referencia = formulario_usar.querySelector("#referencia_movimiento").value,
	tipo_movimiento = formulario_usar.querySelector("#tipo_movimiento").value,
	banco_id = formulario_usar.querySelector("#banco_movimiento").value,
	monto_diferencia = formulario_usar.querySelector("#diferencia_monto").value,
	tipo_diferencia = formulario_usar.querySelector("#diferencia_tipo").value,
	conciliacion_id = select[select.options.selectedIndex].id;

	// Le ponemos los datos del formulario
	datos_consulta.append("id_movimiento",id_movimiento);
	datos_consulta.append("fecha",fecha);
	datos_consulta.append("monto",monto);
	datos_consulta.append("referencia",referencia);	
	datos_consulta.append("tipo_movimiento",tipo_movimiento);
	datos_consulta.append("banco_id",banco_id);
	datos_consulta.append("conciliacion_id",conciliacion_id);
	if (estado != "Ninguna") {
		datos_consulta.append("monto_diferencia",0);
		datos_consulta.append("tipo_diferencia","No hay");
	}

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','modificar_movimiento');

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);

	formulario_usar.reset(); //Limpiamos el formulario
 	modal_movimientos.hide(); // escondemos el modal

 	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	// al terminar le damos al boton su valores originales
	
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Registrar";

	document.getElementById('titulo_modal_movimientos').textContent = "Registrar Movimiento Bancario";

	//Actualizar las tablas
	llenarTablaRegistrosSistema();

	data_table.draw(); // glu glu *Se refresca*

	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');//Mensaje de que se completo la operacion	
}

// si queremos eliminar
async function eliminarMovimiento(id) {
	//Creamos el formData
	datos_consulta = new FormData()

	// Le ponemos el id al FormData
	datos_consulta.append("id_movimiento",id);

	//Aqui decimos que vamos a hacer
	datos_consulta.append('operacion','eliminar_movimiento');

	//Llamamos a la funcion para hacer la consulta
	let respuesta = await query(datos_consulta);
	
	// Resvisamos el resultado
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;// en caso de error mandamos un mensaje con el error y nos vamos
	}

	//Actualizar las tablas
	llenarTablaRegistrosSistema();

	data_table.draw(); // glu glu *Se refresca*

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');//Mensaje de que se completo la operacion
}

//En caso de que se envie un formulario, punto de entrada del validar.js
function envio(operacion) {	
	id_utilizar = boton_formulario.getAttribute("id");//obtenemos el id del registro
	if (operacion == "Editar") {		
		editarMovimiento(id_utilizar);
	}
	else if(operacion == "Registrar"){
		//sino a registrar
		registrar(id_utilizar);
	}else{
		// esto es imposible que pase pero aja
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

// esta funcion obtiene el ultimo id registrado en la base de datos
async function last_id() {
	datos_consulta = new FormData()
	datos_consulta.append('operacion','ultimo_id');
	let res = await query(datos_consulta);
	return res;
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






// Esta en veremos lo de abajo

// un observador que detecte cuando cambie la tabla, si detecta cambio ejecuta la esa funcion
// yo la puse porque jquery cuando hace la paginacion en la tabla, borra los elementos, 
// entonces se pierden los eventos asignados, y cuando vuelven a aparecer, no los tienen.
// Esto es para reasinarle estos eventos (para eliminar, modificar, etc)
const observer = new MutationObserver(() => {
	reasignarEventos();
});

//observer.observe(tabla, {childList:true});

// esta funcion pone los eventos de eliminar y modificar
function reasignarEventos() {
	console.log("me ejecuto");
	if (id_eliminado){ //Si hay un eliminado que no se ha quitado de la tabla
		let existe_fila = tabla.querySelector(`#fila-${id_eliminado}`)
		if (existe_fila) {
			data_table.row(`#fila-${id_eliminado}`).remove().draw();
			id_eliminado = null;	
		}
	//Esto es porque si la tabla esta paginada, como que no encuentra cual borrar hasta que esta en la pagina que la contiene
	}

	// Se asigna el evento eliminar para los botones, esta aqui porque pasa algo parecido a lo de arriba
	$(".eliminar").on("click",function(e){
		id = e.target.value;
		if (id == undefined) {	
			id = e.target.parentElement.value;
		}
		Swal.fire({
			title: "¿Estás seguro?",
			text: "¿Está seguro que desea eliminar este usuario?",
			showCancelButton: true,
			confirmButtonText: "Eliminar",
			confirmButtonColor: "#e01d22",
			cancelButtonText: "Cancelar",
			icon: "warning"
			}).then((resultado) => {
				if (resultado.isConfirmed) {
					eliminar(id);
				}
		});
	});

	if (id_registrado) { // en caso de que se haya registrado y no se haya añadido a la tabla
		let boton_modificar = tabla.querySelector(`[value='${id_registrado.mensaje}']`); 
		// captura el boton de editar, sino lo encuentra es que no esta en su pagina, y no tiene caso ponerle evento
		if (boton_modificar) {
			// si lo encuentra le pone el evento de modificar
			boton_modificar.addEventListener("click",modificar_formulario);
			boton_modificar.parentElement.parentElement.parentElement.setAttribute("id",`fila-${id_registrado.mensaje}`);
			id_registrado = null;
		}
	}
}
