let boton_residencia = document.getElementById('boton_residencia');
let array_propietarios_residentes = [];

boton_residencia.addEventListener("click",e=>{
	document.getElementById("titulo_modal_persona").textContent = 'Generar constancia de residencia';
	document.getElementById('label_reporte').textContent = "Seleccione la persona para la Constancia";	

	let boton_generar = document.getElementById('boton_generar');
	boton_generar.setAttribute("reporte","residencia");

	let select_reporte = document.getElementById('select_reporte');
	select_reporte.selectedOptions[0].textContent = "Seleccione el Propietario";
	//Llenar el select
	let fragment = document.createDocumentFragment();
	array_propietarios_residentes.map(propietario=>{
		let option = document.createElement("option");
		option.textContent = `Aptartamento Nº ${propietario.nro_apartamento}, ${propietario.nombre} ${propietario.apellido}`;
		option.value = propietario.id_habitante;

		fragment.appendChild(option);
	});
	select_reporte.appendChild(fragment);

	regex = /^[0-9]{1,11}$/;
	mensajes_err.invalido = 'El valor del habitante no es válido';
	mensajes_err.inexistente = 'El habitante no existe';
	verificar.tabla = 'habitantes';
	verificar.id = 'id_habitante';
});

function consultar_propietarios() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion',"consultar_personas_residencia");

	fetch("",{method:"POST", body:datos_consulta})
	.then(res=>res.json())
	.then(data=>{
		if (data.length === 0) {
			boton_residencia.parentElement.setAttribute('title','No hay habitantes ni propietarios registrados');			
		}
		else{
			boton_residencia.removeAttribute('disabled');
			array_propietarios_residentes = data;
		}
		boton_residencia.querySelector(".spinner-grow").parentElement.innerHTML = `<i class="bi bi-house-fill" style="font-size: 5rem !important;"></i>`;
	});
}

consultar_propietarios();