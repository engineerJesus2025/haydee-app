$(document).ready(function () {
    $("#monto").on("keypress", function (e) {
        validarKeyPress(/^[0-9.,]$/, e);
    });

    $("#monto").on("keyup", function () {
        validarKeyUp(/^\d+([.,]\d{1,2})?$/, $(this), this.nextElementSibling, "Debe ingresar el monto del gasto");
    });

    $("#descripcion").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z 0-9 \b]*$/, e);
    });

    $("#descripcion").on("keyup", function () {
        validarKeyUp(/^[A-Za-z 0-9 \b]{3,30}$/,
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

// Función para validar todo
async function validarEnvio(accion = "Registrar") {
    // Reglas básicas
    const reglas = [
        { campo: "#fecha", mensaje: "Debe seleccionar una fecha válida" },
        { campo: "#metodo_pago", mensaje: "Debe seleccionar un método de pago" },
        { campo: "#tipo", mensaje: "Debe seleccionar el tipo de gasto" },
        { campo: "#tipo_gasto", mensaje: "Debe seleccionar el tipo de gasto específico" },
        { campo: "#proveedor", mensaje: "Debe seleccionar un proveedor" },
        { campo: "#solicitud", mensaje: "Debe seleccionar una solicitud" },
    ];

    for (let regla of reglas) {
        const valor = $(regla.campo).val();
        if (!valor) {
            mensajes("error", 4000, "Campo requerido", regla.mensaje);
            $(regla.campo).focus();
            return false;
        }
    }

    // Validar descripción
    if (
        validarKeyUp(/^[A-Za-z 0-9 \b]{3,30}$/, $("#descripcion"), document.querySelector("#descripcion").nextElementSibling, "Debe ingresar la descripción del gasto") == 0
    ) {
        mensajes("error", 4000, "Descripción inválida", "Debe contener solo letras y números entre 3 y 30 caracteres");
        return false;
    }

    // Validar monto
    if (
        validarKeyUp(/^\d+([.,]\d{1,2})?$/, $("#monto"), document.querySelector("#monto").nextElementSibling, "Debe ingresar el monto del gasto") == 0
    ) {
        mensajes("error", 4000, "Monto inválido", "Debe ingresar un monto numérico válido");
        return false;
    }

    const metodo = $("#metodo_pago").val();

    if (metodo !== "efectivo") {
        const referencia = $("#referencia").val().trim();
        const banco = $("#banco").val();

        if (!referencia || referencia.length < 3) {
            mensajes("error", 4000, "Referencia inválida", "Debe ingresar la referencia o comprobante de pago");
            $("#referencia").focus();
            return false;
        }

        if (!banco) {
            mensajes("error", 4000, "Banco no seleccionado", "Debe seleccionar el banco correspondiente");
            $("#banco").focus();
            return false;
        }

    }

    return true;
}

// Utilidades
function mensajes(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: mensaje,
        showConfirmButton: true,
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#e01d22",
    });
}

function validarKeyPress(er, e) {
    const tecla = String.fromCharCode(e.keyCode);
    if (!er.test(tecla)) {
        e.preventDefault();
    }
}

function validarKeyUp(er, etiqueta, etiquetamensaje, mensaje) {
    const valido = er.test(etiqueta.val());
    if (valido) {
        if (etiquetamensaje) etiquetamensaje.textContent = "";
        return 1;
    } else {
        if (etiquetamensaje) etiquetamensaje.textContent = mensaje;
        return 0;
    }
}