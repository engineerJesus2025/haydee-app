$(document).ready(function() {
    $("#nombre").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#nombre").on("keyup", function() {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/,
            $(this), this.nextElementSibling, "Debe ingresar el nombre del propietario");
    });

    $("#apellido").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#apellido").on("keyup", function() {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/,
            $(this), this.nextElementSibling, "Debe ingresar el apellido del propietario");
    });

    $("#cedula").on("keypress", function(e) {
        validarKeyPress(/^[0-9\b]*$/, e);
    });
    $("#cedula").on("keyup", function() {
        validarKeyUp(/^[0-9]{7,10}$/,
            $(this), this.nextElementSibling, "Debe ingresar la cédula del propietario");
    });
    $("#telefono").on("keypress", function(e) {
        validarKeyPress(/^[0-9\b]*$/, e);
    });
    $("#telefono").on("keyup", function() {
        validarKeyUp(/^[0-9]{7,11}$/,
            $(this), this.nextElementSibling, "Debe ingresar el teléfono del propietario");
    });
    $("#correo").on("keypress", function(e) {
        validarKeyPress(/^[-A-Za-z0-9_.@\b]*$/, e);
    });
    $("#correo").on("keyup", function(e) {
        validarKeyUp(/^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
            $(this), this.nextElementSibling, "El formato debe ser ejemplo@gmail.com");
});

$("#boton_formulario").on("click", async function(e) {
    let accion = (e.target.hasAttribute("modificar")) ? "Editar" : "Registrar";
    e.preventDefault();
    if (await validarEnvio(accion) == true) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: `¿Está seguro que desea ${accion} este propietario?`,
            showCancelButton: true,
            confirmButtonText: accion,
            confirmButtonColor: "#1b8a40",
            cancelButtonText: "Cancelar",
            icon: "warning"
        }).then((result) => {
            if (result.isConfirmed) {
                envio(accion);
            }
        });
    }
})
}); // Fin de AJAX

function mensajes(icono, tiempo, titulo, mensaje){
    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        timer: tiempo,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: "#e01d22",
    });
}

async function validarEnvio(accion = "Registrar"){
    if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#nombre"), document.querySelector("#nombre").nextElementSibling, 'Debe ingresar el nombre del propietario'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar el nombre del propietario',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#apellido"), document.querySelector("#apellido").nextElementSibling, 'Debe ingresar el apellido del propietario'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar el apellido del propietario',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[0-9]{7,10}$/,
        $("#cedula"), document.querySelector("#cedula").nextElementSibling, 'Debe ingresar la cédula del propietario'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar la cédula del propietario',
            'El formato debe ser sólo números');
        return false;
    }
    if(validarKeyUp(
        /^[0-9]{7,11}$/,
        $("#telefono"), document.querySelector("#telefono").nextElementSibling, 'Debe ingresar el teléfono del propietario'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar el teléfono del propietario',
            'El formato debe ser sólo números');
        return false;
    }
    if(validarKeyUp(
        /^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/,
        $("#correo"), document.querySelector("#correo").nextElementSibling, 'El formato debe ser ejemplo@gmail.com'
    ) == 0) {
        mensajes('error', 4000, 'El formato del correo es incorrecto',
            'El formato debe ser ejemplo@gmail.com');
        return false;

}

return true;

}

function validarKeyPress(er, e){
    key = e.keyCode;
    tecla = String.fromCharCode(key);
    a = er.test(tecla);
    if (!a) {
        e.preventDefault();
    }
}

function validarKeyUp(er,etiqueta,etiquetamensaje,
mensaje){
	a = er.test(etiqueta.val());

	if(a){

		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}

async function verificar_duplicados(datos){
	// Solo es un fetching de datos, en body mandamos los datos
	// Estos datos se mandan al controdalor	
	let data = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
		return result;//Convertimos el resultado de json a js y lo mandamos
	})
	// aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje	
	if(data.estatus){
		document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
		return true;
	}
	return false;
}