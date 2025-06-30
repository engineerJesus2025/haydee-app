$(document).ready(function () {
    $("#monto_estimado").on("keypress", function (e) {
        validarKeyPress(/^[0-9.,]$/, e);
    });

    $("#monto_estimado").on("keyup", function () {
        validarKeyUp(/^\d+([.,]\d{1,2})?$/, $(this), this.nextElementSibling, "Debe ingresar el monto de la solicitud del gasto");
    });

    $("#descripcion").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#descripcion").on("keyup", function () {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/, $(this), this.nextElementSibling, "Debe ingresar la descripción de la solicitud del gasto");
    });

    $("#boton_formulario").on("click", async function (e) {
        let accion = (e.target.getAttribute("modificar")) ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion)) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} esta solicitud de gasto?`,
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
});

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
    const descripcion = $("#descripcion");
    const montoInput = $("#monto_estimado");
    const fechaInput = $("#fecha");
    const nombreInput = $("#nombre");
    const prioridadInput = $("#prioridad");
    const mesInput = $("#selector_mes");
    const anioInput = $("#selector_anio");

    const presupuestoDisponible = parseFloat(document.querySelector("#presupuesto_disponible").textContent.replace(",", "."));
    const montoEstimado = parseFloat(montoInput.val().replace(",", "."));

    const boton = document.querySelector("#boton_formulario");
    const esModificacion = boton.hasAttribute("modificar");
    const monto_original = parseFloat(document.querySelector("#monto_estimado").getAttribute("data-original") || 0);
    const disponible_real = esModificacion ? presupuestoDisponible + monto_original : presupuestoDisponible;

    // Validar mes
    if (mesInput.val() === "") {
        mensajes('error', 3000, 'Mes no seleccionado', 'Debe seleccionar el mes del presupuesto.');
        return false;
    }

    // Validar año
    if (anioInput.val() === "") {
        mensajes('error', 3000, 'Año no seleccionado', 'Debe seleccionar el año del presupuesto.');
        return false;
    }

    // Validar fecha
    if (!fechaInput.val()) {
        mensajes('error', 3000, 'Fecha inválida', 'Debe seleccionar una fecha de solicitud.');
        return false;
    }

    // Validar nombre
    if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,40}$/.test(nombreInput.val())) {
        mensajes('error', 3000, 'Nombre inválido', 'Debe ingresar un nombre válido entre 3 y 40 letras.');
        return false;
    }

    // Validar descripción
    if (validarKeyUp(
        /^[A-Za-z \b]{3,30}$/,
        descripcion,
        descripcion[0].nextElementSibling,
        'Debe ingresar la descripción de la solicitud del gasto'
    ) === 0) {
        mensajes('error', 4000, 'Descripción inválida', 'El formato debe ser solo en letras.');
        return false;
    }

    // Validar monto
    if (validarKeyUp(
        /^\d+([.,]\d{1,2})?$/,
        montoInput,
        montoInput[0].nextElementSibling,
        'Debe ingresar el monto de la solicitud del gasto'
    ) === 0) {
        mensajes('error', 4000, 'Monto inválido', 'Debe ingresar un valor numérico válido.');
        return false;
    }

    // Validar prioridad
    if (!prioridadInput.val()) {
        mensajes('error', 3000, 'Prioridad no seleccionada', 'Debe seleccionar una prioridad.');
        return false;
    }

    // Validar presupuesto
    if (isNaN(disponible_real) || isNaN(montoEstimado)) {
        mensajes('error', 4000, 'Error de presupuesto', 'No se pudo obtener el monto disponible del presupuesto.');
        return false;
    }

    if (montoEstimado > disponible_real) {
        mensajes('error', 4000, 'Monto excedido', `El monto estimado ($ ${montoEstimado.toFixed(2)}) supera el disponible ($ ${disponible_real.toFixed(2)}).`);
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
