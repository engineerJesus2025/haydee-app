let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#modal_presupuesto");
let formulario_usar = document.querySelector(`#form_presupuesto`);
let select_mes = document.querySelector(`#fecha`);
let detalles_presupuestos_base;

let tabla_presupuesto;

let modal_carga = new bootstrap.Modal("#modal_carga");
let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let tiempoInicio;

let total_monto = 0;
let fecha_seleccionada;

let tasa_dolar = parseFloat(isNaN(localStorage.getItem("tasa_dolar"))?1:localStorage.getItem("tasa_dolar")).toFixed(2);
tasa_dolar = isNaN(tasa_dolar)?1:tasa_dolar;


consultar();

document.querySelector(`#modal_presupuesto`).addEventListener("hide.bs.modal",()=>{
	formulario_usar.reset();
	boton_formulario.removeAttribute("modificar");
	boton_formulario.removeAttribute("id_modificar");	
	boton_formulario.textContent = "Guardar";
	document.getElementById('titulo_modal').textContent = "Registrar presupuesto";
	
	document.getElementById('fecha_editada')?.parentElement.removeChild(document.getElementById('fecha_editada'));
	document.getElementById('contenedor_presupuestos').innerHTML = detalles_presupuestos_base;

	document.querySelectorAll("[accion='agregar']").forEach(boton=>{
		boton.addEventListener('click',agregar_fila_presupuesto);
	});
	document.querySelectorAll("[type='number']").forEach(input=>{
		if (input.id == "cuota_reserva") return;
		input.addEventListener("click",e=>{if (e.target.value == 0) e.target.value = ''});
	});
	
	asignarEventosCambioMoneda();

	document.getElementById('spam_icono_moneda_cuota').textContent = "Bs.";
	document.getElementById('spam_icono_moneda_cuota_cambio').textContent = "$";

	document.querySelectorAll("[type='number']").forEach(input=>{
		input.value = 0;
	});
	document.querySelectorAll("[type='checkbox']").forEach(checkbox=>checkbox.checked = false);
	document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
	document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
});

document.querySelector(`.boton_intercambio_cuota`).addEventListener('click',e=>{
	if (typeof e.preventDefault === 'function') {
  		e.preventDefault();
	}
	let input_monto = e.target.closest(".row").querySelector("[monto]"), 
	input_cambio = e.target.closest(".row").querySelector("[convertido]"),
	valor_temporal = 0;

	if (input_monto.getAttribute("monto") == "bs") {
		input_monto.setAttribute("monto",'$');

		valor_temporal = input_monto.value;
		input_monto.value = input_cambio.value;
		input_cambio.value = valor_temporal;

		input_monto.parentElement.querySelector(".icono_moneda").textContent = "$";
		input_cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
	}
	else{
		input_monto.setAttribute("monto",'bs');

		valor_temporal = input_monto.value;
		input_monto.value = input_cambio.value;
		input_cambio.value = valor_temporal;

		input_monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
		input_cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
	}
});

document.getElementById("cuota_reserva").addEventListener("click",e=>{if (e.target.value == 0) e.target.value = ''});

document.getElementById('header-toggle').addEventListener("click",e=>{
    setTimeout(function(){
        tabla_presupuesto.columns.adjust().draw();
    },450);
});

function envio(operacion) {	
	if (operacion == "Editar") {
		let id_modificar = boton_formulario.getAttribute("id_modificar");//obtenemos el id del registro
		modificar(id_modificar);
	}
	else if(operacion == "Registrar"){
		registrar();
	}else{		
		mensajes('error',4000,'Atencion',
		'Ha ocurrido un error durante la operacion, intentelo nuevamente')
	}
}

function agregar_fila_presupuesto(e) {
	if (typeof e.preventDefault === 'function') {
  		e.preventDefault();
	}

	let div_global = e.target.closest(".accordion-collapse");

	let div_padre = document.createElement("div");
	div_padre.setAttribute("class","accordion-body row");

	let div_nombre = document.createElement("div");
	div_nombre.setAttribute("class","col-sm-5");

	let input_nombre = document.createElement("input");
	input_nombre.setAttribute('class','form-control');
	input_nombre.setAttribute('type','text');
	input_nombre.setAttribute('placeholder','nombre del gasto');

	let spam_nombre = document.createElement("spam");
	spam_nombre.setAttribute('class','w-100 invalid-feedback');

	div_nombre.appendChild(input_nombre);
	div_nombre.appendChild(spam_nombre);

	let div_inputs_montos = document.createElement("div");
	div_inputs_montos.setAttribute("class","col-sm-5 row my-sm-0 my-3");

	let div_monto = document.createElement("div");
	div_monto.setAttribute("class","col-md-6");

	let div_input_group = document.createElement("div");
	div_input_group.setAttribute("class","input-group");

	let input_monto = document.createElement("input");
	input_monto.setAttribute('class','form-control');
	input_monto.setAttribute('type','number');
	input_monto.setAttribute('title','Valor del monto en bolivares');
	input_monto.setAttribute('placeholder','Ingrese un monto');
	input_monto.setAttribute('value',0);
	input_monto.setAttribute('monto','bs');

	let spam_monto = document.createElement("spam");
	spam_monto.setAttribute('class','w-100 invalid-feedback');

	let spam_moneda = document.createElement("spam");
	spam_moneda.setAttribute('class','input-group-text icono_moneda');
	spam_moneda.textContent = "Bs.";

	div_input_group.appendChild(input_monto);
	div_input_group.appendChild(spam_monto);
	div_input_group.appendChild(spam_moneda);
	div_monto.appendChild(div_input_group);

	let div_intercambio = document.createElement("div");
	div_intercambio.setAttribute("class","col-lg-1 col-2 mt-sm-0 mt-2 d-flex justify-content-center align-items-center");

	let boton_intercambio = document.createElement("button");
	boton_intercambio.setAttribute('tabindex','-1');
	boton_intercambio.setAttribute("class","btn btn-outline-info boton_intercambio");

	let spam_intercambio = document.createElement("spam");

	let icono_intercambio = document.createElement("i");
	icono_intercambio.setAttribute('class','bi bi-arrow-left-right');

	spam_intercambio.appendChild(icono_intercambio);
	boton_intercambio.appendChild(spam_intercambio);

	div_intercambio.appendChild(boton_intercambio);	 	

 	let div_monto_2 = document.createElement("div");
	div_monto_2.setAttribute("class","col-md-5 col-10 mt-sm-0 mt-2 text-center");

	let div_input_group_2 = document.createElement("div");
	div_input_group_2.setAttribute("class","input-group");

	let input_monto_2 = document.createElement("input");
	input_monto_2.setAttribute('class','form-control');
	input_monto_2.setAttribute('type','text');
	input_monto_2.setAttribute("disabled","");
	input_monto_2.setAttribute('value',0);
	input_monto_2.setAttribute('title','Valor del monto en dolares');
	input_monto_2.setAttribute('convertido','');

	let spam_moneda_2 = document.createElement("spam");
	spam_moneda_2.setAttribute('class','input-group-text icono_moneda');
	spam_moneda_2.textContent = "$";

	div_input_group_2.appendChild(input_monto_2);
	div_input_group_2.appendChild(spam_moneda_2);
	div_monto_2.appendChild(div_input_group_2);	

	div_inputs_montos.appendChild(div_monto);
	div_inputs_montos.appendChild(div_intercambio);
	div_inputs_montos.appendChild(div_monto_2);

	div_padre.appendChild(div_nombre);
	div_padre.appendChild(div_inputs_montos);

	let div_botones = document.createElement("div");
	div_botones.setAttribute("class","col-sm-2 justify-content-evenly d-flex align-items-baseline");

	let boton_agregar = document.createElement("button");
	boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
	boton_agregar.setAttribute('class','btn btn-success');
	boton_agregar.setAttribute('tabindex','-1');
	boton_agregar.setAttribute('accion',`agregar`);

	let icono_agregar = document.createElement('i');
	icono_agregar.setAttribute("class",'bi bi-plus-lg');

	boton_agregar.appendChild(icono_agregar);

	let boton_eliminar = document.createElement("button");
	boton_eliminar.setAttribute('title','eliminar monto');
	boton_eliminar.setAttribute('tabindex','-1');
	boton_eliminar.setAttribute('class','btn btn-danger');	

	let icono_eliminar = document.createElement('i');
	icono_eliminar.setAttribute("class",'bi bi-x-lg');

	boton_eliminar.appendChild(icono_eliminar);

	div_botones.appendChild(boton_agregar);
	div_botones.appendChild(boton_eliminar);
	
	div_padre.appendChild(div_botones);

	div_global.appendChild(div_padre);

	let boton_a_borrar = e.target;
	if (!(e.target.getAttribute("accion"))) {
		boton_a_borrar = e.target.parentElement;
	}
	boton_a_borrar.parentElement.removeChild(boton_a_borrar);

	//eventos
	boton_eliminar.addEventListener('click',eliminar_fila_presupuesto);
	boton_agregar.addEventListener('click',agregar_fila_presupuesto);

	input_nombre.addEventListener('keypress',e=>{
		let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]*$/;
		let key = e.keyCode;
	    let tecla = String.fromCharCode(key);
	    let a = er.test(tecla);
	    if (!a) {
	        e.preventDefault();
	    }
	});

	input_nombre.addEventListener('keyup',e=>{
		let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]{4,50}$/;
		let a = er.test(input_nombre.value);	
		if(a){
			input_nombre.classList.add('is-valid');
			input_nombre.classList.remove('is-invalid');
			input_nombre.nextElementSibling.textContent = "";
			return 1;
		}
		else{
			input_nombre.classList.add('is-invalid')
			input_nombre.classList.remove('is-valid');
			input_nombre.nextElementSibling.textContent = 'Solo letras, no mas de 50 caracteres';
			return 0;
		}
	});

	input_monto.addEventListener('keypress',e=>{
		let er = /^[0-9,.]*$/;
		let key = e.keyCode;
	    let tecla = String.fromCharCode(key);
	    let a = er.test(tecla);
	    if (!a) {
	        e.preventDefault();
	    }
	});

	input_monto.addEventListener('keyup',e=>{
		let er = /^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/;
		let a = er.test(input_monto.value);	
		if(a){
			input_monto.classList.add('is-valid');
			input_monto.classList.remove('is-invalid');
			input_monto.nextElementSibling.textContent = "";

			//Convertimos al contrario			
			let input_convertir = input_monto.closest(".row").querySelector("[convertido]");
			
			if (input_monto.getAttribute("monto") == "bs") {
				if (input_monto.value <= 0 || input_monto.value == '') {
					input_convertir.value = 0;
					return;
				}
				input_convertir.value = (parseFloat(input_monto.value) / tasa_dolar).toFixed(2) || 0;
			}
			else{
				if (input_monto.value <= 0 || input_monto.value == '') {
					input_convertir.value = 0;
					return;
				}
				input_convertir.value = (parseFloat(input_monto.value) * tasa_dolar).toFixed(2);
			}

			return 1;
		}
		else{
			input_monto.classList.add('is-invalid')
			input_monto.classList.remove('is-valid');
			input_monto.nextElementSibling.textContent = 'Solo numeros, no mas de 15 caracteres';
			return 0;
		}
	});

	boton_intercambio.addEventListener("click",e=>{
		if (typeof e.preventDefault === 'function') {
  			e.preventDefault();
		}
		let valor_temporal = 0;

		if (input_monto.getAttribute("monto") == "bs") {
			input_monto.setAttribute("monto",'$');

			valor_temporal = input_monto.value;
			input_monto.value = input_monto_2.value;
			input_monto_2.value = valor_temporal;

			input_monto.parentElement.querySelector(".icono_moneda").textContent = "$";
			input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
		}
		else{
			input_monto.setAttribute("monto",'bs');

			valor_temporal = input_monto.value;
			input_monto.value = input_monto_2.value;
			input_monto_2.value = valor_temporal;

			input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
			input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "$";
		}
	});
}

function eliminar_fila_presupuesto(e) {
	let boton_eliminar = e.target;
	if (e.target.title == '') {
		boton_eliminar = e.target.parentElement;
	}

	if (boton_eliminar.previousElementSibling != null) {
		let boton_agregar = document.createElement("button");
		boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
		boton_agregar.setAttribute('class','btn btn-success');
		boton_agregar.setAttribute('tabindex','-1');
		boton_agregar.setAttribute('accion',`agregar`);

		let icono_agregar = document.createElement('i');
		icono_agregar.setAttribute("class",'bi bi-plus-lg');

		boton_agregar.appendChild(icono_agregar);

		let div_botones_anterior = boton_eliminar.closest(".row").previousElementSibling.querySelector(".col-sm-2");
		
		div_botones_anterior.insertBefore(boton_agregar,div_botones_anterior.querySelector("[title='eliminar monto']"));

		boton_agregar.addEventListener('click',agregar_fila_presupuesto);
	}
	boton_eliminar.parentElement.parentElement.parentElement.removeChild(boton_eliminar.parentElement.parentElement);
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
            console.log("Error en consulta: ",jqXHR,textStatus,errorThrown)
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

function agregarGastoFijo(nombre_gasto,ultimo = false) {
	let div_padre = document.createElement("div");
	div_padre.setAttribute('class','accordion-body row');

	let div_nombre = document.createElement("div");
	div_nombre.setAttribute("class","col-sm-5");

	let input_nombre = document.createElement("input");
	input_nombre.setAttribute('class','form-control');
	input_nombre.setAttribute('type','text');
	input_nombre.setAttribute('placeholder','nombre del gasto');
	input_nombre.setAttribute('value',nombre_gasto);
	if (nombre_gasto !== '') {
		input_nombre.setAttribute('disabled','');
	}

	let spam_nombre = document.createElement("spam");
	spam_nombre.setAttribute('class','w-100 invalid-feedback');

	div_nombre.appendChild(input_nombre);
	div_nombre.appendChild(spam_nombre);	

	let div_inputs_montos = document.createElement("div");
	div_inputs_montos.setAttribute("class","col-sm-5 row my-sm-0 my-3");

	let div_monto = document.createElement("div");
	div_monto.setAttribute("class","col-md-6");

	let div_input_group = document.createElement("div");
	div_input_group.setAttribute("class","input-group");

	let input_monto = document.createElement("input");
	input_monto.setAttribute('class','form-control');
	input_monto.setAttribute('type','number');
	input_monto.setAttribute('value',0);
	input_monto.setAttribute('title','Valor del monto en bolivares');
	input_monto.setAttribute('placeholder','Ingrese un monto');	
	input_monto.setAttribute('monto','bs');

	let spam_monto = document.createElement("spam");
	spam_monto.setAttribute('class','w-100 invalid-feedback');

	let spam_moneda = document.createElement("spam");
	spam_moneda.setAttribute('class','input-group-text icono_moneda');
	spam_moneda.textContent = "Bs.";

	div_input_group.appendChild(input_monto);
	div_input_group.appendChild(spam_monto);
	div_input_group.appendChild(spam_moneda);
	div_monto.appendChild(div_input_group);

	//boton intercambio
	let div_intercambio = document.createElement("div");
	div_intercambio.setAttribute("class","col-lg-1 col-2 mt-sm-0 mt-2 d-flex justify-content-center align-items-center");

	let boton_intercambio = document.createElement("button");
	boton_intercambio.setAttribute('tabindex','-1');
	boton_intercambio.setAttribute("class","btn btn-outline-info boton_intercambio");	

	let spam_intercambio = document.createElement("spam");

	let icono_intercambio = document.createElement("i");
	icono_intercambio.setAttribute('class','bi bi-arrow-left-right');

	spam_intercambio.appendChild(icono_intercambio);
	boton_intercambio.appendChild(spam_intercambio);

	div_intercambio.appendChild(boton_intercambio);	 	

 	let div_monto_2 = document.createElement("div");
	div_monto_2.setAttribute("class","col-md-5 col-10 mt-sm-0 mt-2 text-center");

	let div_input_group_2 = document.createElement("div");
	div_input_group_2.setAttribute("class","input-group");

	let input_monto_2 = document.createElement("input");
	input_monto_2.setAttribute('class','form-control');
	input_monto_2.setAttribute('type','text');
	input_monto_2.setAttribute("disabled","");
	input_monto_2.setAttribute('value',0);
	input_monto_2.setAttribute('title','Valor del monto en dolares');
	input_monto_2.setAttribute('convertido','');

	let spam_moneda_2 = document.createElement("spam");
	spam_moneda_2.setAttribute('class','input-group-text icono_moneda');
	spam_moneda_2.textContent = "$";

	div_input_group_2.appendChild(input_monto_2);
	div_input_group_2.appendChild(spam_moneda_2);
	div_monto_2.appendChild(div_input_group_2);	

	div_inputs_montos.appendChild(div_monto);
	div_inputs_montos.appendChild(div_intercambio);
	div_inputs_montos.appendChild(div_monto_2);

	div_padre.appendChild(div_nombre);
	div_padre.appendChild(div_inputs_montos);

	let div_botones = document.createElement("div");
	div_botones.setAttribute("class","col-sm-2 justify-content-evenly d-flex align-items-baseline");

	if (ultimo) {
		let boton_agregar = document.createElement("button");
		boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
		boton_agregar.setAttribute('class','btn btn-success');
		boton_agregar.setAttribute('tabindex','-1');
		boton_agregar.setAttribute('accion',`agregar`);

		let icono_agregar = document.createElement('i');
		icono_agregar.setAttribute("class",'bi bi-plus-lg');

		boton_agregar.appendChild(icono_agregar);

		div_botones.appendChild(boton_agregar);		
	}
	
	div_padre.appendChild(div_botones);


	//Eventos
	input_nombre.addEventListener('keypress',e=>{
		let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]*$/;
		let key = e.keyCode;
	    let tecla = String.fromCharCode(key);
	    let a = er.test(tecla);
	    if (!a) {
	        e.preventDefault();
	    }
	});

	input_nombre.addEventListener('keyup',e=>{
		let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]{4,50}$/;
		let a = er.test(input_nombre.value);	
		if(a){
			input_nombre.classList.add('is-valid');
			input_nombre.classList.remove('is-invalid');
			input_nombre.nextElementSibling.textContent = "";
			return 1;
		}
		else{
			input_nombre.classList.add('is-invalid')
			input_nombre.classList.remove('is-valid');
			input_nombre.nextElementSibling.textContent = 'Solo letras, no mas de 50 caracteres';
			return 0;
		}
	});

	input_monto.addEventListener('keypress',e=>{
		let er = /^[0-9,.]*$/;
		let key = e.keyCode;
	    let tecla = String.fromCharCode(key);
	    let a = er.test(tecla);
	    if (!a) {
	        e.preventDefault();
	    }
	});

	input_monto.addEventListener('keyup',e=>{
		let er = /^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/;
		let a = er.test(input_monto.value);	
		if(a){
			input_monto.classList.add('is-valid');
			input_monto.classList.remove('is-invalid');
			input_monto.nextElementSibling.textContent = "";

			//Convertimos al contrario
			let input_convertir = input_monto.closest(".row").querySelector("[convertido]");			
			
			if (input_monto.getAttribute("monto") == "bs") {
				if (input_monto.value <= 0 || input_monto.value == '') {
					input_convertir.value = 0;
					return;
				}
				input_convertir.value = (parseFloat(input_monto.value) / tasa_dolar).toFixed(2) || 0;
			}
			else{
				if (input_monto.value <= 0 || input_monto.value == '') {
					input_convertir.value = 0;
					return;
				}
				input_convertir.value = (parseFloat(input_monto.value) * tasa_dolar).toFixed(2);
			}

			return 1;
		}
		else{
			input_monto.classList.add('is-invalid')
			input_monto.classList.remove('is-valid');
			input_monto.nextElementSibling.textContent = 'Solo numeros, no mas de 15 caracteres';
			return 0;
		}
	});

	return div_padre;
}

function asignarEventosDetalles() {
	detalles_presupuestos_base = document.getElementById('contenedor_presupuestos').innerHTML;
	document.querySelectorAll("[accion='agregar']").forEach(boton=>{		
		boton.addEventListener('click',agregar_fila_presupuesto);
	});
	document.querySelectorAll("[title='eliminar monto']").forEach((boton_eliminar)=>{
		boton_eliminar.addEventListener('click',eliminar_fila_presupuesto);
	});

	document.querySelectorAll("[type='number']").forEach(input=>{
		if (input.id == "cuota_reserva") return;
		input.addEventListener("click",e=>{if (e.target.value == 0) e.target.value = ''});
	});
}

function asignarEventosCambioMoneda(){
	let botones_intercambio = document.querySelectorAll(".boton_intercambio");
	botones_intercambio.forEach(boton=>{
		boton.addEventListener("click",e=>{
			if (typeof e.preventDefault === 'function') {
  				e.preventDefault();
			}
			let input_monto = e.target.closest(".row").querySelector("[monto]"), 
			input_cambio = e.target.closest(".row").querySelector("[convertido]"),
			valor_temporal = 0;

			if (input_monto.getAttribute("monto") == "bs") {
				input_monto.setAttribute("monto",'$');

				valor_temporal = input_monto.value;
				input_monto.value = input_cambio.value;
				input_cambio.value = valor_temporal;

				input_monto.parentElement.querySelector(".icono_moneda").textContent = "$";
				input_cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
			}
			else{
				input_monto.setAttribute("monto",'bs');

				valor_temporal = input_monto.value;
				input_monto.value = input_cambio.value;
				input_cambio.value = valor_temporal;

				input_monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
				input_cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
			}
		});
	});
}

function crearBotones(id) {
	let td = document.createElement("td");
	let acciones = document.createElement("div");
	acciones.setAttribute("class","row justify-content-evenly");

	let boton_editar = document.createElement("button");

	let icono_editar = document.createElement("i");
	icono_editar.setAttribute("class", "bi bi-pencil-square");
	boton_editar.appendChild(icono_editar);

	boton_editar.setAttribute("type", "button");
	boton_editar.setAttribute("class", "btn btn-success btn-sm col-lg-3 col-4 editar");
	boton_editar.setAttribute("tabindex", "-1");
	boton_editar.setAttribute("role", "button");
	boton_editar.setAttribute("aria-disabled", "true");
	boton_editar.setAttribute("data-bs-toggle", "modal");
	boton_editar.setAttribute("data-bs-target", "#modal_presupuesto");

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

async function consultar() {
	// eventosCargaDataTable('tabla_presupuesto',modal_carga);
	const paramentros_consulta = (data)=>{data.operacion = 'consulta';}

	const estructura_tabla_presupuetos = [
 		{
 			"data": null,
            "render": function (row) {
            	let fecha = new Date(`${row["mes_fecha"]}/01/${row["anio_fecha"]}`);
				let mes = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
				let anio = fecha.getFullYear();

                return `${mes} del ${anio}`;
            }  
        },
		{ 
			"data": null,
			"render": function (row) {                
                return `${(row["monto_estimado"]).toFixed(2)} Bs. / ${(row["monto_estimado"] / tasa_dolar).toFixed(2)} $.`;
            }
        },
        { 
            "data": null,
            "render": function (row) {
            	return `${parseFloat(row["cuota_reserva"]).toFixed(2)} Bs. / ${(row["cuota_reserva"] / tasa_dolar).toFixed(2)} $.`;
            }
        },
		{ 
            "data": null,
            "render": function (row) {
            	return `${row["observacion"]}`;
            }
        },      
        { 
            "data": null, 
            "render": function (row) {
            	let id_campo = row["id_presupuesto"];
               	let acciones = crearBotones(id_campo);

                return `${acciones.innerHTML}`;
        	}
        } 		
 	]

 	const configuraciones_tabla_presupuetos = (row, data)=>{
 		Array.from(row.children).map(td=>td.setAttribute("class",'align-middle'));
 		 		
		row.setAttribute("id",`fila-${data.id_presupuesto}`); 		
 		row.querySelector(".editar")?.addEventListener('click',modificar_formulario);
 		row.querySelector(".eliminar")?.addEventListener('click',eventoEliminar);
 	}

 	tabla_presupuesto = crearDataTable('tabla_presupuesto',estructura_tabla_presupuetos,paramentros_consulta,configuraciones_tabla_presupuetos);

	consultarInformacionFormulario();	
}

async function consultarInformacionFormulario() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion','consultar_meses_faltantes');

	presupuestos_faltantes = await query(datos_consulta)
	
	if(!(presupuestos_faltantes.estatus == undefined)){
		mensajes('error',4000,'Atencion', presupuestos_faltantes.mensaje);
		return;
	}
	if (presupuestos_faltantes.length == 0) {
		document.getElementById('boton_registrar').nextElementSibling.textContent = "No hay meses para definir presupuesto";
		document.getElementById('boton_registrar').setAttribute('style','display:none');
		return;
	}

	select_mes.textContent = null;
	let fragment = document.createDocumentFragment();
	presupuestos_faltantes.map(fila=>{
		let fecha = new Date(`${fila.mes_faltante}-01-${fila.anio_faltante}`);
		let mes = `${fecha.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;
		let anio = fecha.getFullYear();
		
		let option = document.createElement("option");
		option.textContent = `${mes} de ${anio}`;
		option.value = `${fila.anio_faltante}-${fila.mes_faltante}-01`;

		fragment.appendChild(option);
	});
	
	select_mes.appendChild(fragment);

	llenarDetallesPresupuestos();
}

async function llenarDetallesPresupuestos() {
	datos_consulta = new FormData();

	datos_consulta.append('operacion','consultar_tipo_gastos');
	
	tipos_gastos = await query(datos_consulta);

	if(!(tipos_gastos.estatus == undefined)){
		mensajes('error',4000,'Atencion', tipos_gastos.mensaje);
		return;
	}

	document.getElementById('contenedor_presupuestos').textContent = null;

	let fragment_acordeon = document.createDocumentFragment();
	tipos_gastos.map(tipo_gasto=>{
		let nombre_format = tipo_gasto.nombre_tipo_gasto.replaceAll(" ","-");

		let padre_acordeon = document.createElement("div");
		padre_acordeon.setAttribute('id',nombre_format);
		padre_acordeon.setAttribute('id_tipo_gasto',tipo_gasto.id_tipo_gasto);
		padre_acordeon.setAttribute('class','accordion col-12');

		let item_acordeon = document.createElement("div");
		item_acordeon.setAttribute('class','accordion-item');

		let titulo_acordeon = document.createElement("h2");
		titulo_acordeon.setAttribute('class','accordion-header');

		let boton_acordeon = document.createElement("button");
		boton_acordeon.setAttribute('class','accordion-button');
		boton_acordeon.setAttribute('type','button');
		boton_acordeon.setAttribute('data-bs-toggle','collapse');
		boton_acordeon.setAttribute('data-bs-target',`#${nombre_format}-body`);
		boton_acordeon.setAttribute('aria-expanded','true');
		boton_acordeon.setAttribute('tabindex','-1');
		boton_acordeon.setAttribute('aria-controls',`${nombre_format}-body`);

		boton_acordeon.textContent = tipo_gasto.nombre_tipo_gasto;

		titulo_acordeon.appendChild(boton_acordeon);

		let cuerpo_acordeon = document.createElement("div");
		cuerpo_acordeon.setAttribute('class','align-items-center my-3 accordion-collapse collapse show');
		cuerpo_acordeon.setAttribute('id',`${nombre_format}-body`);
		cuerpo_acordeon.setAttribute('data-bs-parent',nombre_format);

		if (tipo_gasto.nombre_tipo_gasto == "Servicio de Gas") {
			let item_gas_1 = agregarGastoFijo("GAS LARA",true);
			cuerpo_acordeon.appendChild(item_gas_1);
		}
		else if (tipo_gasto.nombre_tipo_gasto == "Servicios Públicos") {
			let item_servicio_1 = agregarGastoFijo("CORPOELEC");
			cuerpo_acordeon.appendChild(item_servicio_1);

			let item_servicio_2 = agregarGastoFijo("HIDROLARA",true);
			cuerpo_acordeon.appendChild(item_servicio_2);
		}
		else if (tipo_gasto.nombre_tipo_gasto == "Personal y Obligaciones Laborales") {
			let item_peronal_1 = agregarGastoFijo("Trabajadora Residencial");
			cuerpo_acordeon.appendChild(item_peronal_1);
			let item_peronal_2 = agregarGastoFijo("Bono de alimentacion");
			cuerpo_acordeon.appendChild(item_peronal_2);
			let item_peronal_3 = agregarGastoFijo("Bono de ayuda");
			cuerpo_acordeon.appendChild(item_peronal_3);
			let item_peronal_4 = agregarGastoFijo("Seguridad Social",true);
			cuerpo_acordeon.appendChild(item_peronal_4);
		}
		else if (tipo_gasto.nombre_tipo_gasto == "Mantenimientos y Reparaciones") {
			let item_peronal_1 = agregarGastoFijo("Mantenimiento ascensor",true);
			cuerpo_acordeon.appendChild(item_peronal_1);			
		}
		else if (tipo_gasto.nombre_tipo_gasto == "Suministros de Limpieza y Operacion") {
			let item_peronal_1 = agregarGastoFijo("Bolsas de Basura");
			cuerpo_acordeon.appendChild(item_peronal_1);
			let item_peronal_2 = agregarGastoFijo("Productos de Limpieza",true);
			cuerpo_acordeon.appendChild(item_peronal_2);
		}
		else if (tipo_gasto.nombre_tipo_gasto == "Gastos Administrativos y Financieros") {
			let item_peronal_1 = agregarGastoFijo("Comisiones Bancaracias");
			cuerpo_acordeon.appendChild(item_peronal_1);
			let item_peronal_2 = agregarGastoFijo("Exencion cuota del administrador",true);
			cuerpo_acordeon.appendChild(item_peronal_2);
		}
		else{
			let item_1 = agregarGastoFijo("",true);
			cuerpo_acordeon.appendChild(item_1);
		}

		item_acordeon.appendChild(titulo_acordeon);
		item_acordeon.appendChild(cuerpo_acordeon);
		padre_acordeon.appendChild(item_acordeon);

		fragment_acordeon.appendChild(padre_acordeon);

		let division = document.createElement("hr");
		division.setAttribute('class','col-12 my-4');

		fragment_acordeon.appendChild(division);
	});
	
	document.getElementById('contenedor_presupuestos').appendChild(fragment_acordeon);

	asignarEventosDetalles();
	asignarEventosCambioMoneda();
}

async function registrar() {
	let fecha = document.getElementById('fecha').value,
	observacion = document.getElementById("observacion").value;
	let cuota_reserva;
	//Con intension de guardar en bolivares
	if (document.getElementById("cuota_reserva").getAttribute("monto") == "bs") {
		cuota_reserva = document.getElementById("cuota_reserva").value;
	}
	else{
		cuota_reserva = document.getElementById("cuota_reserva_cambio").value;
	}

	fecha_seleccionada = fecha;
	total_monto += parseFloat(cuota_reserva);

	let datos_consulta = new FormData();

	datos_consulta.append("fecha",fecha);
	datos_consulta.append("cuota_reserva",cuota_reserva);
	datos_consulta.append("observacion",observacion);	

	datos_consulta.append('operacion','registrar');
	
	let respuesta = await query(datos_consulta,true); 	
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	let id_registrado = await last_id();

	let error = false;

	let listas_acordeones = document.querySelectorAll(".accordion");

	for (acordion in listas_acordeones){		
		if (isNaN(acordion)) {break;}

		let nombres_detalles_presupuestos = [],
			montos_detalles_presupuestos = [],
			tipo_gasto_id;

			tipo_gasto_id = listas_acordeones[acordion].getAttribute("id_tipo_gasto");

		let div_detalles_presupuestos = listas_acordeones[acordion].querySelectorAll(".accordion-body");
		div_detalles_presupuestos.forEach(div=>{
			let nombre = div.querySelector("[type='text']").value;
			let monto;

			if (div.querySelector("[monto]").getAttribute("monto") == "bs") {
				monto = div.querySelector("[type='number']").value;
			}
			else{
				monto = div.querySelector("[convertido]").value;
			}
			
			nombres_detalles_presupuestos.push(nombre);
			montos_detalles_presupuestos.push(monto);
			total_monto += parseFloat(monto);
		});

		datos_consulta = new FormData();

		datos_consulta.append('operacion','registrar_detalles_presupuestos');
		datos_consulta.append('nombres_detalles_presupuestos',nombres_detalles_presupuestos);
		datos_consulta.append('montos_detalles_presupuestos',montos_detalles_presupuestos);
		datos_consulta.append('tipo_gasto_id',tipo_gasto_id);
		datos_consulta.append('presupuesto_id',id_registrado);

		respuesta = await query(datos_consulta,true); 	

		if (!respuesta.estatus) {
			error = true;
			break;
		}
	}	

	if (error) {
		mensajes('error',4000,'Atencion',"Ha ocurrido un error al tratar de registrar los detalles del presupuesto");
		return;
	}

	let registro_mesualidad = await registrarMensualidad(id_registrado);

	if (!registro_mesualidad) {
		mensajes('error',4000,'Atencion',"Ha ocurrido un error al tratar de registrar las mensualidad");
		return;
	}

	modal.hide();

	consultarInformacionFormulario();
	tabla_presupuesto.ajax.reload();

	mensajes('success',4000,'Atencion','El registro se ha realizado exitosamente');
}

async function registrarMensualidad(id_presupuesto_registrado){
	let datos_consulta = new FormData();

	datos_consulta.append('operacion','consultar_apartamentos');
	
	let apartamentos = await query(datos_consulta,'text-secondary')	
	
	if(!(apartamentos.estatus == undefined)){
		mensajes('error',4000,'Atencion', apartamentos.mensaje);
		return false;
	}
	
	error = false;

	for (apartamento in apartamentos){
		if (isNaN(apartamento)) {break;}

		let monto_mensualidad_apatamento = ((total_monto * apartamentos[apartamento].porcentaje_participacion) / 100).toFixed(2);
		let [anio,mes] = fecha_seleccionada.split("-");

		datos_consulta = new FormData();

		datos_consulta.append('monto',monto_mensualidad_apatamento);
		datos_consulta.append('tasa_dolar',tasa_dolar);
		datos_consulta.append('mes',mes);
		datos_consulta.append('anio',anio);
		datos_consulta.append('apartamento_id',apartamentos[apartamento].id_apartamento);

		datos_consulta.append('operacion','registrar_mensualidad');

		let respuesta = await query(datos_consulta,true);

		if (!respuesta.estatus) {			
			error = true;
			break;
		}

		datos_consulta = new FormData();

		datos_consulta.append('mensualidad_id',respuesta.lastId);
		datos_consulta.append('presupuesto_id',id_presupuesto_registrado);
	
		datos_consulta.append('operacion','registrar_presupuesto_mensualidad');

		respuesta = await query(datos_consulta,true);

		if (!respuesta.estatus) {			
			error = true;
			break;
		}
	}

	return !error;
}

async function modificar_formulario(e) {
	if (document.getElementById("contenedor_presupuestos").childElementCount == 0) {
		await llenarDetallesPresupuestos();
	}
	
	let datos_consulta = new FormData();
		
	let id = e.target.value;
	if (id === undefined) {
		id = e.target.parentElement.value; 
	}
	datos_consulta.append("id_presupuesto",id);

	datos_consulta.append('operacion','consulta_especifica');

	let presupuesto = await query(datos_consulta,true);	
	
	let fecha = formulario_usar.querySelector("#fecha"),
	cuota_reserva = formulario_usar.querySelector("#cuota_reserva"),
	observacion = formulario_usar.querySelector("#observacion");

	const fecha_js = new Date(`${presupuesto.anio_fecha}/${presupuesto.mes_fecha}/01`);
	const anio = fecha_js.getFullYear();
	let mes = `${fecha_js.toLocaleString("es-ES",{month: 'long'})[0].toUpperCase()}${fecha_js.toLocaleString("es-ES",{month: 'long'}).slice(1)}`;

	//Me cree esta opcion para mostrarla en el select de los meses
	let option = document.createElement("option");
	option.textContent = `${mes} de ${anio}`;
	option.value = `${presupuesto.anio_fecha}-${presupuesto.mes_fecha}-01`;
	option.setAttribute("id",'fecha_editada');

	fecha.appendChild(option);

	fecha.value = `${presupuesto.anio_fecha}-${presupuesto.mes_fecha}-01`;

	observacion.value = presupuesto.observacion;

	cuota_reserva.value = presupuesto.cuota_reserva;

	let input_convertir = cuota_reserva.closest(".row").querySelector("[convertido]");
	input_convertir.value = (parseFloat(cuota_reserva.value) / tasa_dolar).toFixed(2) || 0;	

	if(!permiso_editar){
		boton_formulario.setAttribute("hidden",true);
		boton_formulario.setAttribute("disabled",true);
	}

	boton_formulario.setAttribute("modificar",true);
	boton_formulario.setAttribute("id_modificar",presupuesto.id_presupuesto);
	boton_formulario.textContent = "Guardar Cambios";
	document.getElementById('titulo_modal').textContent = "Modificar presupuesto";

	datos_consulta = new FormData();

	datos_consulta.append("id_presupuesto",id);

	datos_consulta.append('operacion','consultar_detalles_presupuestos');

	let detalles_presupuestos = await query(datos_consulta,true);

	detalles_presupuestos.map((detalle)=>{
		let gasto_fijo = false;
		
		document.getElementById('contenedor_presupuestos').querySelectorAll("[type='text']").forEach(input=>{
			if (input.value == detalle.nombre_detalle) {
				gasto_fijo = true;
				input.closest(".row").querySelector("[type=number]").value = detalle.monto_detalle;

				let input_convertir = input.closest(".row").querySelector("[convertido]");
				input_convertir.value = (parseFloat(detalle.monto_detalle) / tasa_dolar).toFixed(2) || 0;				
			}
		});
		if (!gasto_fijo) {
			let boton_agregar = {};	
			boton_agregar.target = document.querySelector(`[id_tipo_gasto='${detalle.tipo_gasto_id}']`).querySelector("[accion='agregar']");
			agregar_fila_presupuesto(boton_agregar);

			document.getElementById('contenedor_presupuestos').querySelectorAll("[type='text']").forEach(input=>{
				if (input.value == ''){
					input.value = detalle.nombre_detalle;
					input.closest(".row").querySelector("[type=number]").value = detalle.monto_detalle;

					let input_convertir = input.closest(".row").querySelector("[convertido]");
					input_convertir.value = (parseFloat(detalle.monto_detalle) / tasa_dolar).toFixed(2) || 0;
				}
			});
		}
	});
}

async function modificar(id) {	
	let datos_consulta = new FormData();
	
	let fecha = document.getElementById('fecha').value,
	observacion = document.getElementById("observacion").value;

	let cuota_reserva;
	if (document.getElementById("cuota_reserva").getAttribute("monto") == "bs") {
		cuota_reserva = document.getElementById("cuota_reserva").value;
	}
	else{
		cuota_reserva = document.getElementById("cuota_reserva_cambio").value;
	}

	datos_consulta.append("fecha",fecha);
	datos_consulta.append("cuota_reserva",cuota_reserva);
	datos_consulta.append("observacion",observacion);

	datos_consulta.append("id_presupuesto",id);
	
	datos_consulta.append('operacion','editar_presupuesto');

	let respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}
	
	datos_consulta = new FormData();
	datos_consulta.append("presupuesto_id",id);
	datos_consulta.append('operacion','eliminar_detalles_presupuestos');

	respuesta = await query(datos_consulta,true);

	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	//regsitrar nuevos detalles
	let error = false, monto_total = 0;

	let listas_acordeones = document.querySelectorAll(".accordion");
	listas_acordeones.forEach(async acordion=>{
		let nombres_detalles_presupuestos = [],
			montos_detalles_presupuestos = [],
			tipo_gasto_id;

			tipo_gasto_id = acordion.getAttribute("id_tipo_gasto");

		let div_detalles_presupuestos = acordion.querySelectorAll(".accordion-body");
		div_detalles_presupuestos.forEach(div=>{
			let nombre = div.querySelector("[type='text']").value;
			let monto;

			if (div.querySelector("[monto]").getAttribute("monto") == "bs") {
				monto = div.querySelector("[type='number']").value;
			}
			else{
				monto = div.querySelector("[convertido]").value;
			}			
				
			nombres_detalles_presupuestos.push(nombre);
			montos_detalles_presupuestos.push(monto);
			monto_total += parseFloat(monto);
		});

		datos_consulta = new FormData();

		datos_consulta.append('operacion','registrar_detalles_presupuestos');
		datos_consulta.append('nombres_detalles_presupuestos',nombres_detalles_presupuestos);
		datos_consulta.append('montos_detalles_presupuestos',montos_detalles_presupuestos);
		datos_consulta.append('tipo_gasto_id',tipo_gasto_id);
		datos_consulta.append('presupuesto_id',id);

		respuesta = await query(datos_consulta,true); 	

		if (!respuesta.estatus) {
			error = true;
		}
	});

	if (error) {
		mensajes('error',4000,'Atencion',"Ha ocurrido un error al tratar de editar los detalles del presupuesto");
		return;
	}

	tabla_presupuesto.ajax.reload();
	
 	modal.hide();
 	
	mensajes('success',4000,'Atencion','El registro se ha modificado exitosamente');
}

function eventoEliminar(e){
	let boton_eliminar = e.target;
	if (boton_eliminar.value == undefined) {
		boton_eliminar = boton_eliminar.parentElement;
	}

	Swal.fire({
		title: "¿Estás seguro?",
		text: "¿Está seguro que desea eliminar este presupuesto?",
		showCancelButton: true,
		confirmButtonText: "Si, Eliminar",
		confirmButtonColor: "#e01d22",
		cancelButtonText: "Cancelar",
		icon: "warning"
	}).then((resultado) => {
		if (resultado.isConfirmed) {
			eliminar(boton_eliminar.value);				
		}
	});
}

async function eliminar(id) {
	let datos_consulta = new FormData()

	datos_consulta.append("id_presupuesto",id);
	
	datos_consulta.append('operacion','eliminar');

	let respuesta = await query(datos_consulta);
	
	if (!respuesta.estatus) {
		mensajes('error',4000,'Atencion',respuesta.mensaje);
		return;
	}

	tabla_presupuesto.ajax.reload();

	consultarInformacionFormulario();

	mensajes('success',4000,'Atencion','El registro ha sido eliminado correctamente');
}

async function last_id() {
	datos_consulta = new FormData()
	datos_consulta.append('operacion','ultimo_id');
	let res = await query(datos_consulta);
	return res;
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
					const tiempoEsperaMin = 300;

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