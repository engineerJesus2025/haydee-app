document.addEventListener("DOMContentLoaded", function() {
    const selectBanco = document.getElementById("banco_id");
    const inputCuenta = document.getElementById("numero_cuenta");
    const selecTipoCuenta = document.getElementById("tipo_cuenta");
    const inputTlf = document.getElementById("telefono_afiliado");
    const inputRif = document.getElementById("rif");
    const selectDoc = document.getElementById("tipo_documento");

    // TIpo de Cuenta
    if (selectBanco) {
        selectBanco.addEventListener("change", function() { Validador.evaluarSelect(this.id); });
    }

    // Cuenta
    if (inputCuenta) {
        inputCuenta.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputCuenta.addEventListener("keyup", function() { Validador.evaluarInput(this, Patrones.numeroCuenta, "Entre 18 y 30 dígitos"); });
        inputCuenta.addEventListener("blur", function() {
            if (Validador.evaluarInput(this, Patrones.numeroCuenta, "Entre 18 y 30 dígitos")) {
                const idActual = boton_formulario.getAttribute("id_modificar") || "";
                // Mandar a validar al controlador de cuentas
                Validador.verificarDatoUnico('numero_cuenta', { numero_cuenta: this.value, id_cuenta: idActual }, this, 'Esta cuenta ya está registrada');
            }
        });
    }

    // TIpo de Cuenta
    if (selecTipoCuenta) {
        selecTipoCuenta.addEventListener("change", function() { Validador.evaluarSelect(this.id); });
    }


    // Teléfono
    if (inputTlf) {
        inputTlf.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputTlf.addEventListener("keyup", function() { Validador.evaluarInput(this, Patrones.telefono, "Debe tener 11 dígitos"); });
    }

    // RIF
    if (inputRif) {
        inputRif.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputRif.addEventListener("keyup", function() { Validador.evaluarInput(this, Patrones.rif, "Entre 7 y 9 dígitos"); });
    }

    // Select Documento
    if (selectDoc) {
        selectDoc.addEventListener("change", function() {
            if (this.value === "") {
                EstadoInputs.marcarError(this, "Seleccione un tipo");
                inputRif.disabled = true;
            } else {
                EstadoInputs.marcarExito(this);
                inputRif.disabled = false;
                // Disparamos la validación manual del RIF
                Validador.evaluarInput(inputRif, Patrones.rif, "Entre 7 y 9 dígitos");
            }
        });
    }

    // Envío
    const btnForm = document.getElementById("boton_formulario");
    if (btnForm) {
        btnForm.addEventListener("click", async function(e) {
            e.preventDefault();
            let accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarEnvio(accion)) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este banco?`,
                    "question", 
                    () => {
                        if(typeof numero_cuenta_an !== 'undefined') numero_cuenta_an = null;
                        accion === 'modificar' ? modificar() : registrar();
                    }
                );
            }
        });
    }
});

async function validarEnvio(accion) {
    const selectBanco = document.getElementById("banco_id");
    const inputCuenta = document.getElementById("numero_cuenta");
    const selecTipoCuenta = document.getElementById("tipo_cuenta");
    const inputTlf = document.getElementById("telefono_afiliado");
    const inputRif = document.getElementById("rif");

    if (!Validador.evaluarSelect("banco_id") ||
    !Validador.evaluarInput(inputCuenta, Patrones.numeroCuenta, 'Entre 18 y 30 dígitos') || 
    !Validador.evaluarSelect("tipo_cuenta") ||
    !Validador.evaluarInput(inputTlf, Patrones.telefono, 'Debe tener 11 dígitos') ||
    !Validador.evaluarSelect("tipo_documento") ||
    !Validador.evaluarInput(inputRif, Patrones.rif, 'Entre 7 y 9 dígitos')) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }
    
    const idActual = document.getElementById("boton_formulario").getAttribute("id_modificar") || "";
    
    const numeroCuentaValida = await Validador.verificarDatoUnico(
        'numero_cuenta',
        { 
            numero_cuenta: inputCuenta.value, 
            id_cuenta: idActual 
        }, 
        inputCuenta, 
        'Esta cuenta ya está registrada'
    );

    if (!numeroCuentaValida) {
        Alertas.mostrar('error', 'Numero de Cuenta Duplicada', 'El Numero de Cuenta ingresado ya pertenece a otra cuenta.');
        return false;
    }


    return true;
}