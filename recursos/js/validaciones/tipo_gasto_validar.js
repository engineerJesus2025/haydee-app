$(document).ready(function(){

	$("#nombre_tipo_gasto").on("keypress", function(e){
		Validaciones.keyPress(/^[A-Za-z áéíúóñÑ\b]*$/, e);
	});

	$("#nombre_tipo_gasto").on("keyup", function(){
		Validaciones.keyUp(/^[A-Za-z áéíúóñÑ\b]{3,50}$/, this, this.nextElementSibling, "Debe ingresar el nombre del tipo de gasto");
	});
	
	$("#boton_formulario").on("click", async function(e){
		e.preventDefault();
		let accion = (this.getAttribute("modificar")) ? "Editar" : "Registrar";		
		
		if(await validarEnvio(accion) === true){
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este Tipo de Gasto?`,
                showCancelButton: true,
                confirmButtonText: "Sí, " + accion,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    envio(accion);						
                }
            });
		}	
	});

});

async function validarEnvio(accion = "Registrar"){	
	let inputNombre = document.querySelector("#nombre_tipo_gasto");
	let esValido = Validaciones.keyUp(/^[A-Za-z áéíúóñÑ]{3,50}$/, inputNombre, inputNombre.nextElementSibling, 'Debe ingresar el nombre del tipo de gasto');
	
	if(!esValido) {
		Utilidades.mensaje('error', 'Atención', 'El nombre del tipo de gasto debe contener solo letras y formato correcto.');
		return false;
	}
	
	// Si tuvieras que validar duplicados al enviar, aquí llamarías a Validaciones.verificarDuplicado()
	
	return true;
}