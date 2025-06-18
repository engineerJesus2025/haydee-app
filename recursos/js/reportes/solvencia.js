let boton_solvencia = document.getElementById('boton_solvencia');
let array_propietarios_solventes = [];

boton_solvencia.addEventListener("click",e=>{
	document.getElementById("titulo_modal_persona").textContent = 'Generar Solvencia';
	document.getElementById('label_reporte').textContent = "Seleccione la persona para la Solvencia";	

	let boton_generar = document.getElementById('boton_generar');
	boton_generar.setAttribute("reporte","solvencia");

	let select_reporte = document.getElementById('select_reporte');
	select_reporte.selectedOptions[0].textContent = "Seleccione el Propietario";
	//Llenar el select
	let fragment = document.createDocumentFragment();
	array_propietarios_solventes.map(propietario=>{
		let option = document.createElement("option");
		option.textContent = `${propietario.nombre} ${propietario.apellido}`;
		option.id = propietario.id_persona;

		fragment.appendChild(option);
	});
	select_reporte.appendChild(fragment);
});

function consultar_propietarios() {
	let datos_consulta = new FormData();

	datos_consulta.append('operacion',"consultar_propietarios_reporte");

	fetch("?pagina=propietario_controlador.php&accion=inicio",{method:"POST", body:datos_consulta})
	.then(res=>res.json())
	.then(data=>array_propietarios_solventes = data);
}

consultar_propietarios();