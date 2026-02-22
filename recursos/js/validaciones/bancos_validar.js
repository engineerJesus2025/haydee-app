/**
 * bancos_validar.js
 * Validaciones en tiempo real para Bancos
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    // ============================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================

    $("#nombre_banco").on("keypress", function(e) {
        Validaciones.keyPress(/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]*$/, e);
    });

    $("#nombre_banco").on("keyup", function() {
        Validaciones.keyUp(/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,30}$/,
            this, this.nextElementSibling,
            "Solo letras, mínimo 3 caracteres");
    });

    $("#codigo").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9\b]*$/, e);
    });

    $("#codigo").on("keyup", function() {
        Validaciones.keyUp(/^\d{4}$/,
            this, this.nextElementSibling,
            "Debe ser un código de 4 dígitos");
    });

    $("#numero_cuenta").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9\b]*$/, e);
    });

    $("#numero_cuenta").on("keyup", function() {
        if (Validaciones.keyUp(/^\d{18,30}$/,
            this, this.nextElementSibling,
            "Debe tener entre 18 y 30 dígitos")) {
            
            if (this.value === numero_cuenta_an) return;
            
            let datos = new FormData();
            datos.append('validar', 'numero_cuenta');
            datos.append('numero_cuenta', this.value);
            Validaciones.verificarDuplicado(datos, 'Este número de cuenta ya está registrado');
        }
    });

    $("#telefono_afiliado").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9\b]*$/, e);
    });

    $("#telefono_afiliado").on("keyup", function() {
        Validaciones.keyUp(/^\d{11}$/,
            this, this.nextElementSibling,
            "Debe tener 11 dígitos (ej: 04141234567)");
    });

    $("#rif").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9\b]*$/, e);
    });

    $("#rif").on("keyup", function() {
        Validaciones.keyUp(/^\d{7,9}$/,
            this, this.nextElementSibling,
            "Debe tener entre 7 y 9 dígitos");
    });

    // Validación del tipo de documento (select)
    $("#tipo_documento").on("change", function() {
        if (this.value === "") {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = "Debe seleccionar un tipo de documento";
            $("#rif").attr("disabled", true);
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            this.nextElementSibling.textContent = "";
            $("#rif").removeAttr("disabled");
            // Validar RIF después de habilitarlo
            $("#rif").trigger('keyup');
        }
    });

    // ============================================
    // ENVÍO DEL FORMULARIO
    // ============================================
    $("#boton_formulario").on("click", async function(e) {
        e.preventDefault();
        let accion = (this.getAttribute("modificar")) ? "Editar" : "Registrar";

        if (await validarEnvio(accion) === true) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este banco?`,
                showCancelButton: true,
                confirmButtonText: `Sí, ${accion}`,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    envio(accion);
                    numero_cuenta_an = null;
                }
            });
        }
    });
});

/**
 * Validación completa del formulario antes del envío
 */
async function validarEnvio(accion = "Registrar") {
    // Validar nombre
    if (!Validaciones.keyUp(/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,30}$/,
        document.querySelector("#nombre_banco"),
        document.querySelector("#nombre_banco").nextElementSibling,
        'Solo letras, mínimo 3 caracteres')) {
        Utilidades.mensaje('error', 'Error', 'El nombre del banco es inválido.');
        return false;
    }

    // Validar código
    if (!Validaciones.keyUp(/^\d{4}$/,
        document.querySelector("#codigo"),
        document.querySelector("#codigo").nextElementSibling,
        'Debe ser un código de 4 dígitos')) {
        Utilidades.mensaje('error', 'Error', 'El código del banco debe tener 4 dígitos.');
        return false;
    }

    // Validar número de cuenta
    if (!Validaciones.keyUp(/^\d{18,30}$/,
        document.querySelector("#numero_cuenta"),
        document.querySelector("#numero_cuenta").nextElementSibling,
        'Debe tener entre 18 y 30 dígitos')) {
        Utilidades.mensaje('error', 'Error', 'El número de cuenta es inválido.');
        return false;
    }

    // Validar teléfono
    if (!Validaciones.keyUp(/^\d{11}$/,
        document.querySelector("#telefono_afiliado"),
        document.querySelector("#telefono_afiliado").nextElementSibling,
        'Debe tener 11 dígitos')) {
        Utilidades.mensaje('error', 'Error', 'El teléfono afiliado debe tener 11 dígitos.');
        return false;
    }

    // Validar tipo de documento
    if (!Validaciones.select('tipo_documento')) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar un tipo de documento.');
        return false;
    }

    // Validar RIF
    if (!Validaciones.keyUp(/^\d{7,9}$/,
        document.querySelector("#rif"),
        document.querySelector("#rif").nextElementSibling,
        'Debe tener entre 7 y 9 dígitos')) {
        Utilidades.mensaje('error', 'Error', 'El número de RIF es inválido.');
        return false;
    }

    // Validar duplicidad del número de cuenta si cambió
    if (numero_cuenta_an !== $("#numero_cuenta").val()) {
        let datos = new FormData();
        datos.append('validar', 'numero_cuenta');
        datos.append('numero_cuenta', $("#numero_cuenta").val());
        let duplicado = await Validaciones.verificarDuplicado(datos, 'Este número de cuenta ya está registrado');
        if (duplicado) return false;
    }

    return true;
}