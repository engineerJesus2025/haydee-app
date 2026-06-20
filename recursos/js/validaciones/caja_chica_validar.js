/**
 * caja_chica_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, Peticiones.js
 */

document.addEventListener("DOMContentLoaded", function() {
    const inputFecha = document.getElementById("fecha");
    const inputsMonto = document.querySelectorAll("#monto, #monto_reponer, #fondo_fijo_inicial");
    const inputConcepto = document.getElementById("concepto");
    const selectCaja = document.getElementById("mes_select");
    const inputDesc = document.getElementById("descripcion_input_inline");

    // Fecha (Permitimos años antiguos, pero bloqueamos futuros con maxHoy: true)
    if (inputFecha) {
        inputFecha.addEventListener("change", function() { Validador.evaluarFecha(this, 'Fecha inválida', { maxHoy: true }); });
        inputFecha.addEventListener("keyup", function() { Validador.evaluarFecha(this, 'Fecha inválida', { maxHoy: true }); });
    }

    // Montos
    inputsMonto.forEach(input => {
        input.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto));
        input.addEventListener("keyup", function() {
            if (Validador.evaluarInput(this, Patrones.monto, "Máximo 12 enteros y 2 decimales")) {
                let esBs = this.getAttribute("monto") === "bs";
                let inputConvertir;

                if (this.id === "monto") {
                    inputConvertir = document.getElementById("monto_cambio");
                } else if (this.id === "monto_reponer") {
                    inputConvertir = document.getElementById("monto_cambio_reponer");
                } else if (this.id === "fondo_fijo_inicial") {
                    inputConvertir = document.getElementById("fondo_fijo_inicial_cambio"); // El nuevo espejo
                }

                if (!this.value || parseFloat(this.value) === 0) {
                    if(inputConvertir) inputConvertir.value = '';
                    return;
                }

                if (inputConvertir && typeof tasa_dolar !== 'undefined') {
                    let valorNum = parseFloat(this.value.replace(',', '.'));
                    inputConvertir.value = esBs ? (valorNum / tasa_dolar).toFixed(2) : (valorNum * tasa_dolar).toFixed(2);
                }

                if (this.id === "monto" && typeof actualizarFondoRestante === 'function') {
                    actualizarFondoRestante();
                }
            }
        });
    });

    // Concepto
    if (inputConcepto) {
        inputConcepto.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasAlfanumerico));
        inputConcepto.addEventListener("keyup", function() {
            Validador.evaluarInput(this, Patrones.conceptoCaja, "Mínimo 3 y máximo 100 caracteres");
        });
    }

    // Descripción modal
    if (inputDesc) {
        inputDesc.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.descripcionCaja));
        inputDesc.addEventListener("keyup", function() {
            if(!Validador.evaluarInput(this, Patrones.descripcionCaja, "Máximo 100 caracteres")){
                document.getElementById("btn_guardar_edicion").disabled = true;
            }else{
                document.getElementById("btn_guardar_edicion").disabled = false;
            }
        });
    }

    // Select Caja Chica
    if (selectCaja) {
        selectCaja.addEventListener("change", async function() {
            if (!Validador.evaluarSelect(this.id)) return;
            await verificarClaveForanea(this.value);
        });
    }

    // Botón Gasto
    const btnGastoCaja = document.getElementById("boton_gasto_caja");
    if (btnGastoCaja) {
        btnGastoCaja.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarEnvio(accion)) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este movimiento?`,
                    "question", 
                    () => {
                        if(typeof envio === 'function') envio(accion); 
                    }
                );
            }
        });
    }

    // Botón Reponer
    const btnReponer = document.getElementById("boton_guardar_reposicion");
    if (btnReponer) {
        btnReponer.addEventListener("click", async function(e) {
            e.preventDefault();
            if (await validarEnvioReponerCaja()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea reponer la caja?`,
                    "question", 
                    () => {
                        if(typeof reponerCaja === 'function') reponerCaja(); 
                    }
                );
            }
        });
    }

    // Botón Editar Descripción (Edición Inline)
    const btnGuardarNota = document.getElementById("btn_guardar_edicion");
    if (btnGuardarNota) {
        btnGuardarNota.addEventListener("click", function(e) {
            e.preventDefault();
            let desc = document.getElementById("descripcion_input_inline");
            
            // Validamos que cumpla el patrón antes de enviar
            if (Validador.evaluarInput(desc, Patrones.descripcionCaja, "Inválido")) {
                // Llamamos a la función AJAX directamente, sin SweetAlert para mayor fluidez
                if(typeof modificarObservacionInline === 'function') {
                    modificarObservacionInline(); 
                }
            }
        });
    }
});

// FUNCIONES AUXILIARES LÓGICAS 
function actualizarFondoRestante() {
    let elFondo = document.getElementById("fondos_caja");
    if(!elFondo) return;

    let fondo = parseFloat(elFondo.textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let etiqueta = document.getElementById("fondos_restante");

    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        etiqueta.textContent = elFondo.textContent;
        etiqueta.classList.remove("text-danger");
        return;
    }

    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio").value.replace(',', '.'));

    if (isNaN(monto)) return;

    let dif = typeof diferencia !== 'undefined' ? diferencia : 0;
    let nuevoFondo = fondo - monto + dif;
    
    if (nuevoFondo < 0) {
        etiqueta.textContent = "Excedido";
        etiqueta.classList.add("text-danger");
    } else {
        etiqueta.textContent = `${nuevoFondo.toFixed(2)} Bs. / ${(nuevoFondo / tasa_dolar).toFixed(2)} $`;
        etiqueta.classList.remove("text-danger");
    }
}

function verificarMontoExcedido() {
    let fondo = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio").value.replace(',', '.'));
    
    let dif = typeof diferencia !== 'undefined' ? diferencia : 0;
    return monto > (fondo + dif);
}

function verificarReposicionInsuficiente() {
    let gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio_reponer").value.replace(',', '.'));
    return monto < gastado;
}

async function verificarReposicionExcedente() {
    let gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio_reponer").value.replace(',', '.'));
    return monto > gastado;
}

async function verificarClaveForanea(valor) {
    const selectCaja = document.getElementById("mes_select");
    return await Validador.verificarExistenciaEnServidor(
        'validar_clave_foranea', 
        { tabla: 'caja_chica', nombre_clave: 'id_caja_chica', valor: valor }, 
        selectCaja, 
        'La caja seleccionada no existe en la base de datos'
    );
}

// FUNCIONES DE VALIDACIÓN COMPLETA

async function validarEnvio(accion) {
    let formularioValido = true;
    
    const inputFecha = document.getElementById("fecha");
    const inputMonto = document.getElementById("monto");
    const inputConcepto = document.getElementById("concepto");
    const selectCaja = document.getElementById("mes_select");

    // Validamos Fecha
    if (!Validador.evaluarFecha(inputFecha, 'Fecha inválida', {maxHoy: true})) {
        formularioValido = false;
    }

    // Validamos Monto
    if (!inputMonto.value || parseFloat(inputMonto.value) <= 0) {
        EstadoInputs.marcarError(inputMonto, 'El monto es obligatorio y mayor a 0');
        formularioValido = false;
    } else if (!Validador.evaluarInput(inputMonto, Patrones.monto, 'Formato inválido')) {
        formularioValido = false;
    }

    // Validamos Concepto
    if (!Validador.evaluarInput(inputConcepto, Patrones.conceptoCaja, 'Entre 3 y 100 caracteres')) {
        formularioValido = false;
    }

    // Validamos Select de Caja
    if (!Validador.evaluarSelect(selectCaja.id)) {
        formularioValido = false;
    } else {
        const cajaValida = await verificarClaveForanea(selectCaja.value);
        if (!cajaValida) formularioValido = false;
    }

    if (!formularioValido) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    if (!Validador.evaluarInput(inputMonto, Patrones.monto, 'Formato inválido')) {
        Alertas.mostrar('error', 'Campos Incorrectos', 'Por favor, corrija el formato del monto del gasto antes de continuar.');
        return false;
    }

    if (verificarMontoExcedido()) {
        EstadoInputs.marcarError(inputMonto, 'El monto supera el fondo disponible');
        Alertas.mostrar('error', 'Fondos insuficientes', 'El monto supera el fondo disponible en la caja.');
        return false;
    }

    return true;
}

async function validarEnvioReponerCaja() {
    let formularioValido = true;
    const inputMonto = document.getElementById("monto_reponer");

    if (!inputMonto.value || parseFloat(inputMonto.value) <= 0) {
        EstadoInputs.marcarError(inputMonto, 'El monto es obligatorio y mayor a 0');
        formularioValido = false;
    } else if (!Validador.evaluarInput(inputMonto, Patrones.monto, 'Formato inválido')) {
        formularioValido = false;
    }

    if (!formularioValido) {
        Alertas.mostrar('error', 'Error', 'Revise el monto de reposición.');
        return false;
    }

    if (!Validador.evaluarInput(inputMonto, Patrones.monto, 'Formato inválido')) {
        Alertas.mostrar('error', 'Campos Incorrectos', 'Por favor, corrija el formato del monto del gasto antes de continuar.');
        return false;
    }

    if (verificarReposicionInsuficiente()) {
        EstadoInputs.marcarError(inputMonto, 'La reposición es menor al gasto');
        Alertas.mostrar('error', 'Error', 'El monto a reponer es menor a lo gastado.');
        return false;
    }

    if (await verificarReposicionExcedente()) {
        EstadoInputs.marcarError(inputMonto, 'La reposición es mayor al gasto');
        Alertas.mostrar('error', 'Error', 'El monto a reponer supera lo gastado. Verifique.');
        return false;
    }

    return true;
}