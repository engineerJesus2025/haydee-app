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

	datos_consulta.append('operacion',"consultar_personas_solvencia");

	fetch("",{method:"POST", body:datos_consulta})
	.then(res=>res.json())
	.then(data=>{
		if (data.length === 0) {			
			boton_solvencia.parentElement.setAttribute('title','No hay habitantes ni propietarios solventes');
		}
		else{
			boton_solvencia.removeAttribute('disabled');
			array_propietarios_solventes = data;
		}
		boton_solvencia.querySelector(".spinner-grow").parentElement.innerHTML = `<i class="bi bi-house-check-fill" style="font-size: 5rem !important;"></i>`;
	});	
}

consultar_propietarios();

/*
Por si las borran
public function consultar(){
    $sql = "SELECT * FROM personas INNER JOIN personas_apartamentos ON personas.id_persona = personas_apartamentos.persona_id INNER JOIN apartamentos ON apartamentos.id_apartamento = personas_apartamentos.apartamento_id WHERE personas_apartamentos.tipo_vinculo = 'Propietario'";

    $conexion = $this->get_conex()->prepare($sql);
    $result = $conexion->execute();
    //$this->registrar_bitacora(CONSULTAR, GESTIONAR_PROPIETARIOS, "TODOS LOS USUARIOS");//registra cuando se entra al modulo de propietarios

    $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

    if($result == true){
        return $datos;
    }else{
        return ["estatus"=>false, "mensaje"=>"Error al consultar los propietarios"];
    }
}
public function consultar_propietario(){
    $sql = "SELECT * FROM personas INNER JOIN personas_apartamentos ON personas.id_persona = personas_apartamentos.persona_id INNER JOIN apartamentos ON apartamentos.id_apartamento = personas_apartamentos.apartamento_id WHERE id_persona = :id_propietario";
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id_propietario", $this->id_propietario);
    $result = $conexion->execute();
    $datos = $conexion->fetch(PDO::FETCH_ASSOC);

    if($result == true){
        return $datos;
    }else{
        return ["estatus"=>false, "mensaje"=>"Error al consultar el propietario"];
    }
}
*/