/**
 * presupuesto_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */
document.addEventListener("DOMContentLoaded", function() {
    
    const inputCuota = document.querySelector("#cuota_reserva");
    const inputObs = document.querySelector("#observacion");
    const selectFecha = document.querySelector("#fecha");

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================================
    
    if (inputCuota) {
        inputCuota.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto));
        inputCuota.addEventListener("keyup", function() {
            if (Validador.evaluarInput(this, Patrones.monto, "Solo números, máximo 2 decimales")) {
                // Cálculo de tasa
                let row = this.closest(".row");
                let inputConvertir = row ? row.querySelector("[convertido]") : null;
                
                if (inputConvertir && typeof tasa_dolar !== 'undefined') {
                    if (this.getAttribute("monto") === "bs") {
                        inputConvertir.value = (parseFloat(this.value) / tasa_dolar).toFixed(2) || 0;
                    } else {
                        inputConvertir.value = (parseFloat(this.value) * tasa_dolar).toFixed(2);
                    }
                }
            }
        });
    }

    if (inputObs) {
        inputObs.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasObservacion));
        inputObs.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.observacion, "Máximo 50 caracteres"));
    }

    if (selectFecha) {
        selectFecha.addEventListener("change", function() {
            Validador.evaluarSelect(this.id);
        });
    }

    // ============================================================
    // ENVÍO DEL FORMULARIO
    // ============================================================
    const btnFormulario = document.querySelector("#boton_formulario");
    if (btnFormulario) {
        btnFormulario.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarFormularioCompleto()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este presupuesto?`,
                    "question", 
                    () => {
                        if (accion === "Registrar") {
                            registrar(); 
                        } else {
                            const id = this.getAttribute("id_modificar");
                            modificar(id);
                        }
                    }
                );
            }
        });
    }
});

async function validarFormularioCompleto() {
    if (!Validador.evaluarSelect("fecha")) {
        Alertas.mostrar("error", "Error", "Debe seleccionar un mes");
        return false;
    }

    const cuota = document.querySelector("#cuota_reserva");
    if (!cuota.value || parseFloat(cuota.value) <= 0) {
        EstadoInputs.marcarError(cuota, "La cuota de reserva debe ser mayor a 0");
        Alertas.mostrar("error", "Error", "Cuota de reserva inválida");
        return false;
    }
    
    if (!Validador.evaluarInput(cuota, Patrones.monto, "Formato de cuota inválido")) {
        Alertas.mostrar("error", "Error", "Formato de cuota inválido");
        return false;
    }

    const obs = document.querySelector("#observacion");
    if (obs.value && !Validador.evaluarInput(obs, Patrones.observacion, "Caracteres no permitidos")) {
        Alertas.mostrar("error", "Error", "Observación contiene caracteres no permitidos");
        return false;
    }

    return validarDetalles();
}

function validarDetalles() {
    let todosValidos = true;
    let primerError = "";
    let totalMontos = 0;

    const contenedor = document.querySelector("#contenedor_presupuestos");
    if (!contenedor) return false;
    
    contenedor.querySelectorAll("input[type='text']").forEach(input => {
        if (!Validador.evaluarInput(input, Patrones.nombreDetalle, "Mínimo 4 letras")) {
            todosValidos = false;
            console.log(input)
            if (!primerError) primerError = "Nombre de detalle inválido (mínimo 4 letras)";
        }
    });

    contenedor.querySelectorAll("input[type='number'], input.monto-detalle").forEach(input => {
        if (!Validador.evaluarInput(input, Patrones.monto, "Monto inválido")) {
            todosValidos = false;
            if (!primerError) primerError = "Formato de monto inválido";
        } else {
            totalMontos += parseFloat(input.value) || 0;
        }
    });

    if (totalMontos === 0) {
        Alertas.mostrar("error", "Error", "Debe haber al menos un monto > 0 en los detalles");
        return false;
    }

    if (!todosValidos) {
        Alertas.mostrar("error", "Error", primerError || "Revise los detalles del presupuesto");
        return false;
    }

    return true;
}