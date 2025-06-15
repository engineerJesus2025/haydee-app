$(document).ready(function () {
    $("#monto").on("keypress", function (e) {
        validarKeyPress(/^[0-9.,]$/, e);
    });

    $("#monto").on("keyup", function () {
        validarKeyUp(/^\d+([.,]\d{1,2})?$/, $(this), this.nextElementSibling, "Debe ingresar el monto del gasto");
    });

    $("#descripcion").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#descripcion").on("keyup", function () {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/,
            $(this), this.nextElementSibling, "Debe ingresar la descripción del gasto");
    });

    $("#boton_formulario").on("click", async function (e) {
        let accion = (e.target.getAttribute("modificar")) ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion) == true) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este gasto?`,
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
    });

}); // Fin de AJAX

function mensajes(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: mensaje,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: "#e01d22",
    });
}

async function validarEnvio(accion = "Registrar") {
    if (validarKeyUp(
        /^[A-Za-z \b]{3,30}$/,
        $("#descripcion"), document.querySelector("#descripcion").nextElementSibling, 'Debe ingresar la descripción del gasto'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar la descripción del gasto',
            'El formato debe ser solo en letras');
        return false;
    }
    else if (validarKeyUp(
        /^\d+([.,]\d{1,2})?$/,
        $("#monto"), document.querySelector("#monto").nextElementSibling, 'Debe ingresar el monto del gasto'
    ) == 0) {
        mensajes('error', 4000, 'Debe ingresar el monto del gasto',
            'El formato debe ser solo en números');
        return false;
    }

    return true;
}

function validarKeyPress(er, e) {
    key = e.keyCode;
    tecla = String.fromCharCode(key);
    a = er.test(tecla);
    if (!a) {
        e.preventDefault();
    }
}

function validarKeyUp(er, etiqueta, etiquetamensaje, mensaje) {
    let a = er.test(etiqueta.val());

    if (a) {
        if (etiquetamensaje) etiquetamensaje.textContent = "";
        return 1;
    } else {
        if (etiquetamensaje) etiquetamensaje.textContent = mensaje;
        return 0;
    }
}