/**
 * caja_chica_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, Peticiones.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const inputFecha = document.getElementById("fecha");
    const inputsMonto = document.querySelectorAll("#monto, #monto_reponer");
    const inputConcepto = document.getElementById("concepto");
    const selectCaja = document.getElementById("mes_select");
    const inputDesc = document.getElementById("descripcion_input");

    // Fecha
    if (inputFecha) {
        inputFecha.addEventListener("change", function() { Validador.evaluarFecha(this); });
        inputFecha.addEventListener("keyup", function() { Validador.evaluarFecha(this); });
    }

    // Montos
    inputsMonto.forEach(input => {
        input.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto));
        input.addEventListener("keyup", function() {
            if (Validador.evaluarInput(this, Patrones.monto, "Máximo 12 enteros y 2 decimales")) {
                let esBs = this.getAttribute("monto") === "bs";
                let inputConvertir = document.getElementById(this.id === "monto" ? "monto_cambio" : "monto_cambio_reponer");

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
        inputDesc.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasAlfanumerico));
        inputDesc.addEventListener("keyup", function() {
            Validador.evaluarInput(this, Patrones.descripcionCaja, "Máximo 100 caracteres");
        });
    }

    // Select Caja Chica
    if (selectCaja) {
        selectCaja.addEventListener("change", async function() {
            if (!Validador.evaluarSelect(this.id)) return;
            await Validador.verificarExistenciaEnServidor(
                'validar_clave_foranea', 
                { tabla: 'caja_chica', nombre_clave: 'id_caja_chica', valor: this.value }, 
                this, 
                'La caja seleccionada no existe'
            );
        });
    }

    const btnGastoCaja = document.getElementById("boton_gasto_caja");
    if (btnGastoCaja) {
        btnGastoCaja.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarEnvio(accion)) {
                Swal.fire({
                    title: "¿Estás seguro?",
                    text: `¿Está seguro que desea ${accion} este gasto de caja?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: `Sí, ${accion}`,
                    confirmButtonColor: "#1b8a40",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Llama a tu función AJAX
                        if(typeof envio === 'function') envio(accion); 
                    }
                });
            }
        });
    }
});

function actualizarFondoRestante() {
    let elFondo = document.getElementById("fondos_caja");
    if(!elFondo) return;

    let fondo = parseFloat(elFondo.textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let etiqueta = document.getElementById("fondos_restante");

    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        etiqueta.textContent = elFondo.textContent;
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

async function validarEnvio(accion) {
    let formularioValido = true; // Acumulador para evaluar TODO antes de salir

    const inputFecha = document.getElementById("fecha");
    const inputMonto = document.getElementById("monto");
    const inputConcepto = document.getElementById("concepto");
    const selectCaja = document.getElementById("mes_select");

    // 1. Validar Fecha
    if (!Validador.evaluarFecha(inputFecha, 'La fecha es inválida')) {
        formularioValido = false;
    }

    // 2. Validar Monto (Que no esté vacío, ni sea 0, y cumpla formato)
    if (!inputMonto.value || parseFloat(inputMonto.value) === 0) {
        EstadoInputs.marcarError(inputMonto, 'El monto es obligatorio y mayor a 0');
        formularioValido = false;
    } else if (!Validador.evaluarInput(inputMonto, Patrones.monto, 'Formato inválido')) {
        formularioValido = false;
    }

    // 3. Validar Concepto
    if (!Validador.evaluarInput(inputConcepto, Patrones.conceptoCaja, 'El concepto debe tener entre 3 y 100 caracteres')) {
        formularioValido = false;
    }

    // 4. Validar Select de Caja
    if (!Validador.evaluarSelect(selectCaja.id)) {
        formularioValido = false;
    } else {
        // Validar en el backend que la caja existe realmente
        const cajaValida = await Validador.verificarExistenciaEnServidor(
            'validar_clave_foranea', 
            { tabla: 'caja_chica', nombre_clave: 'id_caja_chica', valor: selectCaja.value }, 
            selectCaja, 
            'La caja seleccionada no existe'
        );
        if (!cajaValida) formularioValido = false;
    }

    // Si algún campo falló visualmente, detenemos aquí y mostramos el resumen
    if (!formularioValido) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    // 5. Validar fondos (Lógica de negocio)
    // Solo llegamos aquí si el formato es perfecto
    if (typeof verificarMontoExcedido === 'function' && verificarMontoExcedido()) {
        EstadoInputs.marcarError(inputMonto, 'El monto supera el fondo disponible');
        Alertas.mostrar('error', 'Fondos insuficientes', 'El monto supera el fondo disponible en la caja.');
        return false;
    }

    return true;
}

// ... Mantén las demás funciones (validarEnvioReponerCaja, verificarMontoExcedido, etc.) adaptando Utilidades.mensaje a Alertas.mostrar.

async function validarEnvioReponerCaja() {
    let montoInput = document.getElementById("monto_reponer");
    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        Validaciones.mostrarError(montoInput, 'El monto es obligatorio');
        Utilidades.mensaje('error', 'Error', 'Debe ingresar un monto');
        return false;
    }

    if (!Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
        montoInput, montoInput.nextElementSibling.nextElementSibling,
        'Formato inválido')) {
        Utilidades.mensaje('error', 'Error', 'El monto tiene formato incorrecto');
        return false;
    }

    let select = document.getElementById("mes_select");
    if (!Validaciones.select(select.id)) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar una caja');
        return false;
    }

    let valido = await verificarClaveForanea(select.value);
    if (!valido) {
        Utilidades.mensaje('error', 'Error', 'La caja seleccionada no existe');
        return false;
    }

    if (verificarReposicionInsuficiente()) {
        Utilidades.mensaje('error', 'Error', 'El monto no cubre todos los gastos pendientes');
        return false;
    }

    return true;
}

function verificarMontoExcedido() {
    let fondo = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio").value.replace(',', '.'));
    return monto > fondo;
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
    let respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: 'caja_chica',
        nombre_clave: 'id_caja_chica',
        valor: valor
    });
    return respuesta.estatus === true;
}
