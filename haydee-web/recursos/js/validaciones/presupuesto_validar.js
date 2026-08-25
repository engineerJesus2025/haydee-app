document.addEventListener("DOMContentLoaded", function() {
    const inputObs = document.querySelector("#observacion");
    const selectFecha = document.querySelector("#fecha");
    const formularioPresupuesto = document.getElementById("form_presupuesto"); 

    if (inputObs) {
        inputObs.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasObservacion));
        inputObs.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.observacionExtendida, "Máximo 50 caracteres"));
    }

    if (selectFecha) {
        selectFecha.addEventListener("change", function() {
            Validador.evaluarSelect(this.id);
        });
    }

    if (formularioPresupuesto) {
        const selectorMontos = "input[type='number'], input.monto-detalle, #cuota_reserva";

        formularioPresupuesto.addEventListener("keypress", function(e) {
            if (e.target.matches(selectorMontos)) {
                Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto);
            }
        });

        formularioPresupuesto.addEventListener("focusin", function(e) {
            if (e.target.matches(selectorMontos)) {
                if (parseFloat(e.target.value) === 0) {
                    e.target.value = '';
                }
            }
        });

        formularioPresupuesto.addEventListener("focusout", function(e) {
            if (e.target.matches(selectorMontos)) {
                if (e.target.value.trim() === '') {
                    e.target.value = '0';
                    let row = e.target.closest(".row"); 
                    let inputConvertir = row ? row.querySelector("[convertido]") : null;
                    if (inputConvertir) inputConvertir.value = '0';
                }
            }
        });

        formularioPresupuesto.addEventListener("keyup", function(e) {
            if (e.target.matches(selectorMontos)) {
                if (Validador.evaluarInput(e.target, Patrones.monto, "Solo números y máximo 2 decimales")) {
                    let row = e.target.closest(".row"); 
                    let inputConvertir = row ? row.querySelector("[convertido]") : null;

                    if (inputConvertir && typeof tasa_dolar !== 'undefined') {
                        let valorDigitado = parseFloat(e.target.value) || 0;
                        if (e.target.getAttribute("monto") === "bs") {
                            inputConvertir.value = (valorDigitado / tasa_dolar).toFixed(2);
                        } else {
                            inputConvertir.value = (valorDigitado * tasa_dolar).toFixed(2);
                        }
                    }
                }
            }
        });

        formularioPresupuesto.addEventListener("click", function(e) {
            let botonIntercambio = e.target.closest(".boton_intercambio, .boton_intercambio_cuota");
            if (botonIntercambio) {
                e.preventDefault();
                
                let row = botonIntercambio.closest(".row");
                let input_monto = row.querySelector("[monto]");
                let input_cambio = row.querySelector("[convertido]");
                let valor_temporal = 0;

                if (!input_monto || !input_cambio) return;

                if (input_monto.getAttribute("monto") === "bs") {
                    input_monto.setAttribute("monto", '$');
                    valor_temporal = input_monto.value;
                    input_monto.value = input_cambio.value;
                    input_cambio.value = valor_temporal;
                    row.querySelector(".icono_moneda").textContent = "$";
                    row.querySelector("[convertido]").parentElement.querySelector(".icono_moneda").textContent = "Bs.";
                } else {
                    input_monto.setAttribute("monto", 'bs');
                    valor_temporal = input_monto.value;
                    input_monto.value = input_cambio.value;
                    input_cambio.value = valor_temporal;
                    row.querySelector(".icono_moneda").textContent = "Bs.";
                    row.querySelector("[convertido]").parentElement.querySelector(".icono_moneda").textContent = "$";
                }
            }
        });
    }

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
    if (obs.value && !Validador.evaluarInput(obs, Patrones.observacionExtendida, "Caracteres no permitidos")) {
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

    contenedor.querySelectorAll("input[type='number'], input.monto-detalle").forEach(input => {
        if (!Validador.evaluarInput(input, Patrones.monto, "Monto inválido")) {
            todosValidos = false;
            if (!primerError) primerError = "Formato de monto inválido";
        } else {
            totalMontos += parseFloat(input.value) || 0;
        }
    });

    if (totalMontos === 0) {
        Alertas.mostrar("error", "Error", "Debe haber al menos un monto que no sea 0 en los detalles");
        return false;
    }

    if (!todosValidos) {
        Alertas.mostrar("error", "Error", primerError || "Revise los detalles del presupuesto");
        return false;
    }

    return true;
}