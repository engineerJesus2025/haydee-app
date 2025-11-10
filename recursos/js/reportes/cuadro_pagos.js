let boton_cuadro_pagos = document.getElementById('boton_cuadro_pagos');
let array_meses = [];

boton_cuadro_pagos.addEventListener("click",e=>{
	document.getElementById("titulo_modal_persona").textContent = 'Generar Cuadro de Pagos';
	document.getElementById('label_reporte').textContent = "Seleccione el mes para generar el cuadro";

	let boton_generar = document.getElementById('boton_generar');
	boton_generar.setAttribute("reporte","cuadro_pagos");

	let select_reporte = document.getElementById('select_reporte');
	select_reporte.selectedOptions[0].textContent = "Seleccione el Mes";
	
	//Llenar el select
	let fragment = document.createDocumentFragment();
	array_meses.map(meses=>{
		let fecha = new Date(`${meses.mes}-01-${meses.anio}`);
		mes_buscar = fecha.toLocaleString("es-ES",{month: 'long'});
		anio_buscar = fecha.getFullYear();
		let option = document.createElement("option");
		option.textContent = `${mes_buscar} del ${anio_buscar}`;
		option.value = `${meses.mes}-${meses.anio}`;

		fragment.appendChild(option);
	});
	select_reporte.appendChild(fragment);

	regex = /^[0-9]{1,2}-[0-9]{4}$/;
	mensajes_err.invalido = 'La fecha seleccionada no es válida';
	mensajes_err.inexistente = '';
	verificar.tabla = '';
	verificar.id = '';
	verificar.basico = true;
});

function consultar_propietarios() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion',"consultar_meses_mensualidad");

	fetch("",{method:"POST", body:datos_consulta})
	.then(res=>res.json())
	.then(data=>{
		if (data.length === 0) {
			boton_cuadro_pagos.parentElement.setAttribute('title','No mensualidades registradas para generar el cuadro');
		}
		else{
			boton_cuadro_pagos.removeAttribute('disabled');
			array_meses = data;
		}
		boton_cuadro_pagos.querySelector(".spinner-grow").parentElement.innerHTML = `<i class="bi bi-ui-checks" style="font-size: 5rem !important;"></i>`;
	});
}

consultar_propietarios();