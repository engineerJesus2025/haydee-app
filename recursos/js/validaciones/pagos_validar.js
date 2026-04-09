/**
 * pagos_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, Peticiones.js
 */
document.addEventListener("DOMContentLoaded", function() {

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - CABECERA
    // ============================================================
    const selectsCabecera = ['apartamento_id', 'mensualidad_id', 'estado'];
    selectsCabecera.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener("change", function() { Validador.evaluarSelect(this.id); });
        }
    });

    const inputObservacion = document.getElementById("observacion");
    if (inputObservacion) {
        // En keypress podríamos usar Patrones.teclasDireccion que es bastante permisivo
        inputObservacion.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasDireccion));
        inputObservacion.addEventListener("keyup", function() {
            if (this.value.trim() !== "") {
                Validador.evaluarInput(this, Patrones.observacionExtendida, "Debe ingresar una observación de 3 a 60 caracteres");
            } else {
                EstadoInputs.limpiar(this); // Opcional, si está vacío se limpia
            }
        });
    }

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - DETALLES (Delegación de eventos)
    // ============================================================
    const contenedor = document.getElementById("detalles_container");

    if (contenedor) {
        // Delegación para el evento CHANGE
        contenedor.addEventListener("change", async function(e) {
            const target = e.target;

            if (target.classList.contains("fecha_pago")) {
                Validador.evaluarFecha(target, "La fecha no es válida");
            } 
            else if (target.classList.contains("tipo_pago")) {
                Validador.evaluarInput(target, Patrones.textoCorto, "Seleccione un método válido");
            }
            else if (target.classList.contains("banco_id")) {
                Validador.evaluarInput(target, Patrones.digitos, "Debe seleccionar un banco");
            }
            else if (target.classList.contains("imagen")) {
                validarImagen(target, 5 * 1024 * 1024);
            }
            else if (target.classList.contains("referencia")) {
                // Primero validamos formato. Si es correcto, consultamos la BD.
                if (Validador.evaluarInput(target, Patrones.referenciaBancaria, "De 4 a 20 caracteres alfanuméricos")) {
                    const idPagoActual = document.getElementById("boton_formulario").dataset.id || ""; 
                    const refValida = await Validador.verificarDatoUnico(
                        'referencia', 
                        { 
                            referencia: target.value, 
                            id_pago: idPagoActual
                        }, 
                        target, 
                        'Referencia en uso'
                    );
                }
            }
        });

        // Delegación para el evento KEYPRESS (Solo Bloqueo de teclas)
        contenedor.addEventListener("keypress", function(e) {
            const target = e.target;

            if (target.matches(".monto, .monto_dolar, .tasa_dolar")) {
                Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto);
            }
            else if (target.classList.contains("referencia")) {
                // Solo permitimos Letras y números sin espacios
                Validador.bloquearTeclasInvalidas(e, /^[0-9A-Za-z]$/i);
                // ¡Se eliminó la llamada AJAX de aquí!
            }
        });

        // Delegación para el evento KEYUP (Validación de formato completo)
        contenedor.addEventListener("keyup", function(e) {
            const target = e.target;

            if (target.matches(".monto, .monto_dolar, .tasa_dolar")) {
                Validador.evaluarInput(target, Patrones.monto, "Formato inválido (Ej: 150.50)");
            }
            else if (target.classList.contains("referencia")) {
                Validador.evaluarInput(target, Patrones.referenciaBancaria, "De 4 a 20 caracteres alfanuméricos");
            }
        });
    }

    // ============================================================
    // VALIDACIÓN GENERAL AL ENVIAR FORMULARIO
    // ============================================================
    const btnFormulario = document.getElementById("boton_formulario");
    if (btnFormulario) {
        btnFormulario.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

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
                            registrar(); 
                        } else {
                            const id = this.getAttribute("id_modificar");
                            modificar(id); 
                        }
                    }
                });
            }
        });
    }
});

// ============================================================
// FUNCIONES AUXILIARES DE VALIDACIÓN
// ============================================================

async function validarFormularioCompleto() {
    // 1. Validar Cabecera
    if (!Validador.evaluarSelect("apartamento_id")) {
        Alertas.mostrar("error", "Error", "Debe seleccionar un apartamento");
        return false;
    }
    if (!Validador.evaluarSelect("mensualidad_id")) {
        Alertas.mostrar("error", "Error", "Debe seleccionar una mensualidad asociada");
        return false;
    }
    if (document.getElementById("estado") && !Validador.evaluarSelect("estado")) {
        Alertas.mostrar("error", "Error", "Debe seleccionar el estado del pago");
        return false;
    }

    // 2. Validar que exista al menos un detalle
    const bloques = document.querySelectorAll("#detalles_container .detalle-pago");
    if (bloques.length === 0) {
        Alertas.mostrar("error", "Error", "Debe agregar al menos un detalle de pago");
        return false;
    }

    // 3. Validar Detalles iterativamente
    for (let i = 0; i < bloques.length; i++) {
        const bloque = bloques[i];
        const num = i + 1;

        const fechaInput = bloque.querySelector(".fecha_pago");
        if (!Validador.evaluarFecha(fechaInput)) {
            Alertas.mostrar("error", `Detalle #${num}`, "La fecha no es válida");
            return false;
        }

        const metodoSelect = bloque.querySelector(".tipo_pago");
        if (!Validador.evaluarInput(metodoSelect, Patrones.textoCorto, "Seleccione una opción")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Seleccione un método de pago");
            return false;
        }

        const montoInput = bloque.querySelector(".monto");
        if (!Validador.evaluarInput(montoInput, Patrones.monto, "Monto inválido o en cero")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Monto inválido o en cero");
            return false;
        }

        const metodoValor = metodoSelect.value;
        if (metodoValor === "Transferencia" || metodoValor === "Pago Movil") {
            
            const refInput = bloque.querySelector(".referencia");
            if (!Validador.evaluarInput(refInput, Patrones.referenciaBancaria, "Referencia bancaria inválida")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Referencia bancaria inválida");
                return false;
            }

            const idPagoActual = document.getElementById("boton_formulario").dataset.id || ""; 

            const refValida = await Validador.verificarDatoUnico(
                'referencia', 
                { 
                    referencia: refInput.value, 
                    id_pago: idPagoActual
                }, 
                refInput, 
                'Referencia en uso'
            );
            // SI LA REFERENCIA ESTÁ OCUPADA, DETENEMOS EL FORMULARIO
            if (!refValida) {
                Alertas.mostrar("error", `Detalle #${num}`, "La referencia bancaria ya está registrada en otro pago");
                return false;
            }

            const bancoSelect = bloque.querySelector(".banco_id");
            if (!Validador.evaluarInput(bancoSelect, Patrones.digitos, "Seleccione una opción")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Seleccione el banco destino");
                return false;
            }

            const inputImagen = bloque.querySelector(".imagen");
            const hayImagenPrevia = bloque.querySelector("input[name='imagen_existente[]']");
            
            if (inputImagen && inputImagen.files.length === 0 && !hayImagenPrevia) {
                Alertas.mostrar("error", `Detalle #${num}`, "Debe adjuntar el comprobante (Capture) del pago");
                return false;
            }

            if (inputImagen && inputImagen.files.length > 0 && !validarImagen(inputImagen, 5 * 1024 * 1024)) {
                Alertas.mostrar("error", `Detalle #${num}`, "El comprobante subido no es válido (solo JPG/PNG, máx 5MB)");
                return false;
            }
        }
    }

    return true; 
}

function validarImagen(input, limiteBytes) {
    if (!input || !input.files || input.files.length === 0) return true; 
    
    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!allowedTypes.includes(file.type)) {
        EstadoInputs.marcarError(input, "Solo JPG/PNG");
        Alertas.mostrar("error", "Archivo inválido", "Solo se permiten imágenes JPG o PNG.");
        return false;
    }

    if (file.size > limiteBytes) {
        EstadoInputs.marcarError(input, "Máximo 5MB");
        Alertas.mostrar("error", "Archivo muy pesado", "La imagen no debe superar los 5 MB.");
        return false;
    }

    EstadoInputs.marcarExito(input);
    return true;
}