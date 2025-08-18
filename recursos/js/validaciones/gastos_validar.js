$(document).ready(function () {

    // Evento principal para validar y enviar el formulario
    $("#boton_formulario").on("click", function (e) {
        e.preventDefault();
        let accion = $(this).attr("modificar") ? "Editar" : "Registrar";
        if (validarFormularioCompleto()) {
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

    const formulario = $("#form_gastos");

    // Para el campo MONTO
    formulario.on('keypress', '.monto', function (e) {
        // Permite números y un solo punto o coma
        validarKeyPress(/^[\d.,]*$/, e);
    });
    formulario.on('keyup', '.monto', function () {
        // Valida que el formato sea, por ejemplo, 150.50 o 150,50
        validarKeyUp(/^\d{1,10}([.,]\d{1,2})?$/, $(this), "El monto debe ser un número válido (ej: 150,50).");
    });

    // Para el campo REFERENCIA
    formulario.on('keypress', '.referencia', function (e) {
        validarKeyPress(/^[0-9]*$/, e);
    });
    formulario.on('keyup', '.referencia', function () {
        validarKeyUp(/^[0-9]{4,20}$/, $(this), "La referencia debe tener entre 4 y 20 números.");
    });

    // Para los campos de DESCRIPCIÓN (general y de detalle)
    formulario.on('keyup', '#descripcion_gasto, .descripcion_detalle', function () {
        validarKeyUp(/^.{10,}$/, $(this), "La descripción debe tener al menos 10 caracteres.");
    });

    // Para el campo FECHA
    formulario.on('change', '.fecha_detalle', function () {
        validarKeyUp(/.+/, $(this), "Debe seleccionar una fecha.");
    });

}); // Fin de $(document).ready()


// ✅ --- FUNCIONES DE AYUDA PARA LA VALIDACIÓN

/**
 * Bloquea la escritura de caracteres que no coincidan con la expresión regular.
 * @param {RegExp} regex - La expresión regular para validar la tecla.
 * @param {Event} e - El objeto del evento keypress.
 */
function validarKeyPress(regex, e) {
    const char = String.fromCharCode(e.which);
    if (!regex.test(char)) {
        e.preventDefault();
    }
}

/**
 * Muestra u oculta un mensaje de validación al escribir.
 * @param {RegExp} regex - La expresión regular para validar el valor completo.
 * @param {jQuery} campo - El objeto jQuery del campo (input/textarea).
 * @param {string} mensaje - El mensaje de error a mostrar.
 */
function validarKeyUp(regex, campo, mensaje) {
    let mensajeContenedor = campo.closest('.input-group, .form-group').find('.mensaje-validacion');
    if (mensajeContenedor.length === 0) { // Fallback por si la estructura es diferente
        mensajeContenedor = campo.parent().find('.mensaje-validacion');
    }

    if (!regex.test(campo.val()) && campo.val() !== "") {
        campo.addClass('is-invalid');
        mensajeContenedor.text(mensaje);
    } else {
        campo.removeClass('is-invalid');
        mensajeContenedor.text('');
    }
}

/**
 * Muestra una alerta de SweetAlert2.
 * @param {string} icono - 'success', 'error', 'warning', 'info'
 * @param {number} tiempo - Tiempo en milisegundos para que se cierre sola.
 * @param {string} titulo - El título de la alerta.
 * @param {string} texto - El mensaje de la alerta.
 */
function mensajes(icono, tiempo, titulo, texto) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: texto,
        confirmButtonColor: "#1b8a40",
    });
}

/**
 * Valida el formulario completo, incluyendo todos los detalles de gasto.
 * @returns {boolean} - Devuelve true si todo es válido, de lo contrario, false.
 */
function validarFormularioCompleto() {

    // --- 1. VALIDACIÓN DE CAMPOS PRINCIPALES ---
    if ($("#tipo").val() === null || $("#tipo").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Tipo.');
        return false;
    }
    if ($("#tipo_gasto").val() === null || $("#tipo_gasto").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Tipo de Gasto.');
        return false;
    }
    if ($("#descripcion_gasto").val().trim() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes ingresar la Descripción del Gasto.');
        return false;
    }
    if ($("#proveedor").val() === null || $("#proveedor").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Proveedor.');
        return false;
    }
    if ($("#solicitud").val() === null || $("#solicitud").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar una Solicitud.');
        return false;
    }

    // --- 2. VALIDACIÓN DE TODOS LOS BLOQUES DE DETALLE ---
    let todosLosDetallesValidos = true;
    $(".detalle-gasto:visible").each(function (index) {
        let bloque = $(this);
        let numeroDetalle = index + 1;

        // Validar Fecha
        if (bloque.find(".fecha_detalle").val() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar una fecha.');
            todosLosDetallesValidos = false;
            return false; // Detiene el bucle .each()
        }

        // Validar Método de Pago
        if (bloque.find(".metodo_pago").val() === null || bloque.find(".metodo_pago").val() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar un método de pago.');
            todosLosDetallesValidos = false;
            return false;
        }

        // Validar Monto
        const monto = bloque.find(".monto").val();
        const montoRegex = /^[0-9]+([.,][0-9]{1,2})?$/;
        if (monto.trim() === "" || !montoRegex.test(monto)) {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'El monto no es válido. Usa solo números y hasta dos decimales.');
            todosLosDetallesValidos = false;
            return false;
        }

        // Validar Descripción del Detalle
        if (bloque.find(".descripcion_detalle").val().trim() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes ingresar la descripción del detalle.');
            todosLosDetallesValidos = false;
            return false;
        }

        // --- VALIDACIÓN DE CAMPOS CONDICIONALES (si están visibles) ---
        if (bloque.find(".grupo_referencia").is(":visible")) {

            // Validar Referencia
            const referencia = bloque.find(".referencia").val();
            const referenciaRegex = /^[0-9]{4,20}$/; // Ejemplo: solo números, de 4 a 20 dígitos
            if (referencia.trim() === "" || !referenciaRegex.test(referencia)) {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'La referencia es inválida (solo números, de 4 a 20 dígitos).');
                todosLosDetallesValidos = false;
                return false;
            }

            // Validar Banco
            if (bloque.find(".banco").val() === null || bloque.find(".banco").val() === "") {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar un banco.');
                todosLosDetallesValidos = false;
                return false;
            }

            const hayArchivoNuevo = bloque.find(".imagen").get(0).files.length > 0;
            const hayComprobanteCargado = bloque.find(".nombre_imagen_cargada").text().trim() !== "";

            if (!hayArchivoNuevo && !hayComprobanteCargado) {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debe seleccionar una imagen de comprobante para este método de pago.');
                todosLosDetallesValidos = false;
                return false; // Detiene el bucle
            }
        }
    });

    if (!todosLosDetallesValidos) {
        return false; // Si algún detalle falló, la validación general falla.
    }

    // Si todas las validaciones pasaron
    return true;
}