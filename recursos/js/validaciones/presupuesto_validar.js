/**
 * presupuesto_validar.js
 * Validaciones para el módulo de Presupuesto
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    // ============================================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================================

    // Cuota de reserva (número con hasta 2 decimales)
    $("#cuota_reserva").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9.,]$/, e);
    });
    $("#cuota_reserva").on("keyup", function() {
        if (Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
            this, this.nextElementSibling,
            "Solo números, máximo 2 decimales")) {
            
            let inputConvertir = this.closest(".row").querySelector("[convertido]");
            if (this.getAttribute("monto") === "bs") {
                inputConvertir.value = (parseFloat(this.value) / tasa_dolar).toFixed(2) || 0;
            } else {
                inputConvertir.value = (parseFloat(this.value) * tasa_dolar).toFixed(2);
            }
        }
    });

    // Observación (letras, números, puntos, comas, espacios, máx 50)
    $("#observacion").on("keypress", function(e) {
        Validaciones.keyPress(/^[A-Za-z0-9ñ.,\s]$/, e);
    });
    $("#observacion").on("keyup", function() {
        Validaciones.keyUp(/^[A-Za-z0-9ñ.,\s]{0,50}$/,
            this, this.nextElementSibling,
            "Máximo 50 caracteres");
    });

    // Select de fecha
    $("#fecha").on("change", function() {
        Validaciones.select(this.id);
    });

    // ============================================================
    // ENVÍO DEL FORMULARIO
    // ============================================================
    $("#boton_formulario").on("click", async function(e) {
        e.preventDefault();
        const accion = $(this).attr("modificar") ? "Editar" : "Registrar";

        if (await validarFormularioCompleto()) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Desea ${accion.toLowerCase()} este presupuesto?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1b8a40",
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    if (accion === "Registrar") {
                        registrar(); // función de presupuesto_ajax.js
                    } else {
                        const id = document.getElementById("boton_formulario").getAttribute("id_modificar");
                        modificar(id);
                    }
                }
            });
        }
    });
});

// ============================================================
// FUNCIÓN DE VALIDACIÓN COMPLETA
// ============================================================
async function validarFormularioCompleto() {
    // Validar select de fecha
    if (!Validaciones.select("fecha")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar un mes");
        return false;
    }

    // Validar cuota de reserva (no vacía y formato correcto)
    const cuota = document.getElementById("cuota_reserva");
    if (!cuota.value || parseFloat(cuota.value) <= 0) {
        Validaciones.mostrarError(cuota, "La cuota de reserva debe ser mayor a 0");
        Utilidades.mensaje("error", "Error", "Cuota de reserva inválida");
        return false;
    }
    if (!Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
        cuota, cuota.nextElementSibling, "")) {
        Utilidades.mensaje("error", "Error", "Formato de cuota inválido");
        return false;
    }

    // Validar observación (opcional, pero si tiene contenido debe cumplir formato)
    const obs = document.getElementById("observacion");
    if (obs.value && !Validaciones.keyUp(/^[A-Za-z0-9ñ.,\s]{0,50}$/,
        obs, obs.nextElementSibling, "")) {
        Utilidades.mensaje("error", "Error", "Observación contiene caracteres no permitidos");
        return false;
    }

    // Validar detalles del presupuesto
    return validarDetalles();
}

function validarDetalles() {
    let todosValidos = true;
    let primerError = "";

    // Validar que todos los campos de texto (nombre) tengan al menos 4 caracteres
    document.querySelectorAll("#contenedor_presupuestos [type='text']").forEach(input => {
        if (!Validaciones.keyUp(/^[A-Za-záéíóúñÑ \s]{4,50}$/,
            input, input.nextElementSibling, "")) {
            todosValidos = false;
        console.log(input)
            if (!primerError) primerError = "Nombre de detalle inválido (mínimo 4 letras)";
        }
    });

    // Validar que todos los montos sean > 0 y tengan formato correcto
    let totalMontos = 0;
    document.querySelectorAll("#contenedor_presupuestos [type='number']").forEach(input => {
        if (!Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
            input, input.nextElementSibling, "")) {
            todosValidos = false;
            if (!primerError) primerError = "Formato de monto inválido";
        } else {
            totalMontos += parseFloat(input.value) || 0;
        }
    });

    if (totalMontos === 0) {
        Utilidades.mensaje("error", "Error", "Debe haber al menos un monto > 0 en los detalles");
        return false;
    }

    if (!todosValidos) {
        Utilidades.mensaje("error", "Error", primerError || "Revise los detalles del presupuesto");
        return false;
    }

    return true;
}