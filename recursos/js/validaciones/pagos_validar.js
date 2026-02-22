/**
 * pagos_validar.js
 * Validaciones centralizadas para el módulo de Pagos (Admin y Propietarios)
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - CABECERA
    // ============================================================
    $("#apartamento_id").on("change", function() { Validaciones.select(this.id); });
    $("#mensualidad_id").on("change", function() { Validaciones.select(this.id); });
    
    // El estado solo lo valida si el input existe (los propietarios podrían no tenerlo visible)
    if ($("#estado").length) {
        $("#estado").on("change", function() { Validaciones.select(this.id); });
    }

    $("#observacion").on("keyup", function() {
        if (this.value.trim() !== "") {
            Validaciones.keyUp(/^[a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ.,-]{3,60}$/, this, this.nextElementSibling, "Debe ingresar una observación de 3 a 60 caracteres");
        } else {
            // Es opcional, así que si está vacío le quitamos las clases de error
            this.classList.remove('is-invalid');
            this.classList.remove('is-valid');
            this.nextElementSibling.textContent = "";
        }
    });

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - DETALLES (Delegación de eventos)
    // ============================================================
    const contenedor = $("#detalles_container");

    // Fecha
    contenedor.on("change", ".fecha_admin", function() {
        Validaciones.fecha(this, this.nextElementSibling, true);
    });

    // Método de pago
    contenedor.on("change", ".tipo_pago_admin", function() {
        Validaciones.selectCustom(this, /^[a-zA-Z ]{3,20}$/, "Seleccione un método válido");
    });

    // Montos y Tasas
    contenedor.on("keypress", ".monto, .monto_dolar, .tasa_dolar", function(e) {
        Validaciones.keyPress(/^[\d.,]$/, e);
    });

    contenedor.on("keyup", ".monto, .monto_dolar, .tasa_dolar", function() {
        Validaciones.keyUp(/^\d{1,12}([.,]\d{1,2})?$/, this, this.nextElementSibling, "Formato inválido (Ej: 150.50)");
    });

    // Referencia (solo números y letras)
    contenedor.on("keypress", ".referencia", function(e) {
        Validaciones.keyPress(/^[0-9A-Za-z]$/, e);
    });

    contenedor.on("keyup", ".referencia", function() {
        Validaciones.keyUp(/^[0-9A-Za-z]{4,20}$/, this, this.nextElementSibling, "De 4 a 20 caracteres alfanuméricos");
    });

    // Banco
    contenedor.on("change", ".banco_admin", function() {
        Validaciones.selectCustom(this, /^\d+$/, "Debe seleccionar un banco");
    });

    // Imagen
    contenedor.on("change", ".imagen", function() {
        validarImagen(this, 5 * 1024 * 1024); // Límite 5MB
    });


    // ============================================================
    // VALIDACIÓN GENERAL AL ENVIAR FORMULARIO
    // ============================================================
    $("#boton_formulario").on("click", async function(e) {
        e.preventDefault();
        const accion = $(this).attr("modificar") ? "Editar" : "Registrar";

        if (await validarFormularioCompleto()) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Desea ${accion.toLowerCase()} este pago?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1b8a40",
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    if (accion === "Registrar") {
                        registrar(); // Función en pagos_ajax.js
                    } else {
                        const id = document.getElementById("boton_formulario").getAttribute("id_modificar");
                        modificar(id); // Función en pagos_ajax.js
                    }
                }
            });
        }
    });

});

// ============================================================
// FUNCIONES AUXILIARES DE VALIDACIÓN
// ============================================================

async function validarFormularioCompleto() {
    // 1. Validar Cabecera
    if (!Validaciones.select("apartamento_id")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar un apartamento");
        return false;
    }
    if (!Validaciones.select("mensualidad_id")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar una mensualidad asociada");
        return false;
    }

    if ($("#estado").length && !Validaciones.select("estado")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar el estado del pago");
        return false;
    }

    // 2. Validar que exista al menos un detalle
    const bloques = document.querySelectorAll("#detalles_container .detalle-pago");
    if (bloques.length === 0) {
        Utilidades.mensaje("error", "Error", "Debe agregar al menos un detalle de pago");
        return false;
    }

    // 3. Validar Detalles iterativamente
    for (let i = 0; i < bloques.length; i++) {
        const bloque = bloques[i];
        const num = i + 1;

        // Fecha
        const fechaInput = bloque.querySelector(".fecha_admin");
        if (!Validaciones.fecha(fechaInput, fechaInput.nextElementSibling, true)) {
            Utilidades.mensaje("error", `Detalle #${num}`, "La fecha no es válida");
            return false;
        }

        // Método de pago
        const metodoSelect = bloque.querySelector(".tipo_pago_admin");
        if (!Validaciones.selectCustom(metodoSelect, /^[a-zA-Z ]{3,20}$/, "Seleccione una opción")) {
            Utilidades.mensaje("error", `Detalle #${num}`, "Seleccione un método de pago");
            return false;
        }

        // Monto
        const montoInput = bloque.querySelector(".monto");
        if (!Validaciones.keyUp(/^\d{1,12}([.,]\d{1,2})?$/, montoInput, montoInput.nextElementSibling, "")) {
            Utilidades.mensaje("error", `Detalle #${num}`, "Monto inválido o en cero");
            return false;
        }

        // Si es Transferencia o Pago Móvil, requiere campos bancarios
        const metodoValor = metodoSelect.value;
        if (metodoValor === "Transferencia" || metodoValor === "Pago Movil") {
            
            // Referencia
            const refInput = bloque.querySelector(".referencia");
            if (!Validaciones.keyUp(/^[0-9A-Za-z]{4,20}$/, refInput, refInput.nextElementSibling, "")) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Referencia bancaria inválida");
                return false;
            }

            // Banco
            const bancoSelect = bloque.querySelector(".banco_admin");
            if (!Validaciones.selectCustom(bancoSelect, /^\d+$/, "Seleccione una opción")) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Seleccione el banco destino");
                return false;
            }

            // Imagen (Solo es obligatoria si es un registro NUEVO y no tiene imagen previa en edición)
            const inputImagen = bloque.querySelector(".imagen");
            const hayImagenPrevia = bloque.querySelector("input[name='imagen_existente[]']");
            
            if (inputImagen.files.length === 0 && !hayImagenPrevia) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Debe adjuntar el comprobante (Capture) del pago");
                return false;
            }

            // Validar peso y tipo si subió una
            if (inputImagen.files.length > 0 && !validarImagen(inputImagen, 5 * 1024 * 1024)) {
                Utilidades.mensaje("error", `Detalle #${num}`, "El comprobante subido no es válido (solo JPG/PNG, máx 5MB)");
                return false;
            }
        }
    }

    return true; // Todo perfecto
}

function validarImagen(input, limiteBytes) {
    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!file) return true; // Se maneja como válido porque la obligación se evalúa en validarFormularioCompleto

    if (!allowedTypes.includes(file.type)) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        Utilidades.mensaje("error", "Archivo inválido", "Solo se permiten imágenes JPG o PNG.");
        return false;
    }

    if (file.size > limiteBytes) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        Utilidades.mensaje("error", "Archivo muy pesado", "La imagen no debe superar los 5 MB.");
        return false;
    }

    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    return true;
}