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
		option.value = propietario.id_persona;

		fragment.appendChild(option);
	});
	select_reporte.appendChild(fragment);
});

function consultar_propietarios() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion',"consultar_propietarios_reporte");

	fetch("?pagina=propietario_controlador.php&accion=inicio",{method:"POST", body:datos_consulta})
	.then(res=>res.json())
	.then(data=>array_propietarios_residentes = data);
}

consultar_propietarios();