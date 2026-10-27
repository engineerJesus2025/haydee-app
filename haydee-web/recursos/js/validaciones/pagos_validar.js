document.addEventListener("DOMContentLoaded", function() {
    const METODOS_BANCARIOS = ["TRANSFERENCIA", "PAGO MOVIL"];

    const selectsCabecera = ['apartamento_id', 'mensualidad_id', 'estado'];
    selectsCabecera.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener("change", function() { Validador.evaluarSelect(this.id); });
        }
    });

    const inputObservacion = document.getElementById("observacion");
    if (inputObservacion) {
        inputObservacion.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasDireccion));
        inputObservacion.addEventListener("keyup", function() {
            if (this.value.trim() !== "") {
                Validador.evaluarInput(this, Patrones.observacionExtendida, "Debe ingresar una observación de 3 a 60 caracteres");
            } else {
                EstadoInputs.limpiar(this);
            }
        });
    }

    const contenedor = document.getElementById("detalles_container");

    if (contenedor) {
        contenedor.addEventListener("change", async function(e) {
            const target = e.target;

            if (target.classList.contains("fecha_pago")) {
                Validador.evaluarFecha(target, "La fecha no es válida");
            } 
            else if (target.classList.contains("tipo_pago")) {
                // Al cambiar método, revalidamos visibilidad y disparamos validación
                if (typeof actualizarVisibilidadMetodo === "function") {
                    actualizarVisibilidadMetodo(target);
                }
                Validador.evaluarInput(target, Patrones.textoCorto, "Seleccione un método válido");
            }
            else if (target.classList.contains("banco_id")) {
                Validador.evaluarInput(target, Patrones.digitos, "Debe seleccionar un banco");
            }
            else if (target.classList.contains("cuenta_id")) {
                Validador.evaluarInput(target, Patrones.digitos, "Debe seleccionar la cuenta receptora");
            }
            else if (target.classList.contains("imagen")) {
                validarImagen(target, 5 * 1024 * 1024);
            }
            else if (target.classList.contains("referencia")) {
                // Validación de formato + unicidad en BD
                if (Validador.evaluarInput(target, Patrones.referenciaBancaria, "De 4 a 20 caracteres alfanuméricos")) {
                    const idPagoActual = obtenerIdPagoActual();
                    await Validador.verificarDatoUnico(
                        'referencia', 
                        { referencia: target.value, id_pago: idPagoActual }, 
                        target, 
                        'Referencia en uso'
                    );
                }
            }
        });

        // KEYPRESS
        contenedor.addEventListener("keypress", function(e) {
            const target = e.target;

            if (target.matches(".monto, .tasa_dolar")) {
                Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto);
            }
            else if (target.classList.contains("referencia")) {
                Validador.bloquearTeclasInvalidas(e, /^[0-9A-Za-z]$/i);
            }
        });

        // KEYUP
        contenedor.addEventListener("keyup", function(e) {
            const target = e.target;

            if (target.matches(".monto, .tasa_dolar")) {
                Validador.evaluarInput(target, Patrones.monto, "Formato inválido (Ej: 150.50)");
            }
            else if (target.classList.contains("referencia")) {
                Validador.evaluarInput(target, Patrones.referenciaBancaria, "De 4 a 20 caracteres alfanuméricos");
            }
        });
    }

    const btnFormulario = document.getElementById("boton_formulario");
    if (btnFormulario) {
        btnFormulario.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarFormularioCompleto()) {
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

// HELPERS (mover despues a quien sabe donde)
function obtenerIdPagoActual() {
    const btn = document.getElementById("boton_formulario");
    return btn?.getAttribute("id_modificar") || btn?.dataset.id || "";
}

async function validarFormularioCompleto() {
    const METODOS_BANCARIOS = ["TRANSFERENCIA", "PAGO MOVIL"];

    // --- Validar Cabecera ---
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

    // --- Validar que exista al menos un detalle ---
    const bloques = document.querySelectorAll("#detalles_container .detalle-pago");
    if (bloques.length === 0) {
        Alertas.mostrar("error", "Error", "Debe agregar al menos un detalle de pago");
        return false;
    }

    // --- Validar Detalles ---
    for (let i = 0; i < bloques.length; i++) {
        const bloque = bloques[i];
        const num = i + 1;

        // Fecha
        const fechaInput = bloque.querySelector(".fecha_pago");
        if (!Validador.evaluarFecha(fechaInput)) {
            Alertas.mostrar("error", `Detalle #${num}`, "La fecha no es válida");
            return false;
        }

        // Metodo de pago
        const metodoSelect = bloque.querySelector(".tipo_pago");
        if (!Validador.evaluarInput(metodoSelect, Patrones.textoCorto, "Seleccione una opción")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Seleccione un método de pago");
            return false;
        }

        // Monto
        const montoInput = bloque.querySelector(".monto");
        if (!Validador.evaluarInput(montoInput, Patrones.monto, "Monto inválido o en cero")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Monto inválido o en cero");
            return false;
        }
        if (parseFloat(montoInput.value) <= 0) {
            Alertas.mostrar("error", `Detalle #${num}`, "El monto debe ser mayor a cero");
            EstadoInputs.marcarError(montoInput, "Debe ser mayor a 0");
            return false;
        }

        // Validaciones bancarias SOLO si el método es bancario
        const metodoValor = (metodoSelect.value || "").trim().toUpperCase();

        if (METODOS_BANCARIOS.includes(metodoValor)) {

            // Referencia
            const refInput = bloque.querySelector(".referencia");
            if (!Validador.evaluarInput(refInput, Patrones.referenciaBancaria, "Referencia bancaria inválida")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Referencia bancaria inválida");
                return false;
            }
            if (!refInput.value || refInput.value.trim() === "") {
                Alertas.mostrar("error", `Detalle #${num}`, "Debe ingresar el número de referencia");
                EstadoInputs.marcarError(refInput, "Campo obligatorio");
                return false;
            }

            // Unicidad de referencia (consulta AJAX a BD)
            const idPagoActual = obtenerIdPagoActual();
            const refValida = await Validador.verificarDatoUnico(
                'referencia',
                { referencia: refInput.value, id_pago: idPagoActual },
                refInput,
                'Referencia en uso'
            );
            if (!refValida) {
                Alertas.mostrar("error", `Detalle #${num}`, "La referencia bancaria ya está registrada en otro pago");
                return false;
            }

            // Banco Emisor
            const bancoSelect = bloque.querySelector(".banco_id");
            if (!bancoSelect.value || !Validador.evaluarInput(bancoSelect, Patrones.digitos, "Seleccione una opción")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Seleccione el banco emisor");
                EstadoInputs.marcarError(bancoSelect, "Campo obligatorio");
                return false;
            }

            // Cuenta Destino
            const cuentaSelect = bloque.querySelector(".cuenta_id");
            if (!cuentaSelect.value || !Validador.evaluarInput(cuentaSelect, Patrones.digitos, "Seleccione una opción")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Seleccione la cuenta destino del condominio");
                EstadoInputs.marcarError(cuentaSelect, "Campo obligatorio");
                return false;
            }

            // Comprobante (Imagen)
            const inputImagen = bloque.querySelector(".imagen");
            const hayImagenPrevia = bloque.querySelector("input[name='imagen_existente[]']");

            // No hay imagen nueva NI imagen previa 
            if ((!inputImagen || inputImagen.files.length === 0) && !hayImagenPrevia) {
                Alertas.mostrar("error", `Detalle #${num}`, "Debe adjuntar el comprobante (Capture) del pago");
                return false;
            }

            // Si hay imagen nueva, validar formato y peso
            if (inputImagen && inputImagen.files.length > 0) {
                if (!validarImagen(inputImagen, 5 * 1024 * 1024)) {
                    Alertas.mostrar("error", `Detalle #${num}`, "El comprobante no es válido (solo JPG/PNG, máx 5MB)");
                    return false;
                }
            }
        }
    }

    return true;
}

// VALIDACIÓN DE IMAGEN
function validarImagen(input, limiteBytes) {
    if (!input || !input.files || input.files.length === 0) return true;

    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!allowedTypes.includes(file.type)) {
        EstadoInputs.marcarError(input, "Solo JPG/PNG");
        return false;
    }

    if (file.size > limiteBytes) {
        EstadoInputs.marcarError(input, "Máximo 5MB");
        return false;
    }

    EstadoInputs.marcarExito(input);
    return true;
}