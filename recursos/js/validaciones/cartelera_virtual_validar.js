$(document).ready(function () {
   $("#titulo").on("keypress", function (e) {
      validarKeyPress(/^[A-Za-z \b]*$/, e);
   });
    $("#titulo").on("keyup", function () {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/, $(this), this.nextElementSibling, "Debe ingresar el titulo de la publicación");
    });
    $("#descripcion").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });
    $("#descripcion").on("keyup", function () {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/, $(this), this.nextElementSibling, "Debe ingresar la descripción de la publicación");
    });
    $("#fecha").on("keypress", function (e) {
        validarKeyPress(/^[0-9\b]*$/, e);
    });
    $("#fecha").on("keyup", function () {
        validarKeyUp(/^[0-9]{7,10}$/, $(this), this.nextElementSibling, "Debe ingresar la fecha de la publicación");
    });

$("#boton_formulario").on("click", async function (e) {
    let accion = (e.target.hasAttribute("modificar")) ? "Editar" : "Registrar";
    e.preventDefault();
    if (await validarEnvio(accion) == true) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: `¿Está seguro que desea ${accion} esta publicación?`,
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

function mensajes(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        timer: tiempo,
        showConfirmButton: true,
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#e01d22",
    });
} // Fin de mensajes

async function validarEnvio(accion = "Registrar") {
    if (validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#titulo"), document.querySelector("#titulo").nextElementSibling, 
    )== 0){
        mensajes("error", 2000, "Error", "Debe ingresar el título de la publicación");
        return false;
    }
    if (validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#descripcion"), document.querySelector("#descripcion").nextElementSibling, 
    )== 0){
        mensajes("error", 2000, "Error", "Debe ingresar la descripción de la publicación");
        return false;
    }
    if (!validarFecha($("#fecha"))) {
    mensajes("error", 2000, "Error", "Debe ingresar la fecha de la publicación");
    return false;
}
    if (validar_select("prioridad") == false) {
        mensajes("error", 2000, "Error", "Debe seleccionar la prioridad de la publicación");
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

function validarKeyUp(er, etiqueta, etiquetamensaje, mensaje){
    let valido = er.test(etiqueta.val());

    if (etiquetamensaje) {
        etiquetamensaje.textContent = valido ? "" : mensaje;
    }

    return valido ? 1 : 0;
}

function validar_select(id) {
	let selec = document.querySelector("#"+id);
	if (selec.value == '') {
		return false;
	}else{
		return true;
	}
}
function validarFecha(fechaInput) {
    return fechaInput.val() !== "";
}