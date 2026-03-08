/**
 * bancos_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {

    const inputNombre = document.getElementById("nombre_banco");
    const inputCodigo = document.getElementById("codigo");
    const inputCuenta = document.getElementById("numero_cuenta");
    const inputTlf = document.getElementById("telefono_afiliado");
    const inputRif = document.getElementById("rif");
    const selectDoc = document.getElementById("tipo_documento");

    // Nombre
    if (inputNombre) {
        inputNombre.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener("keyup", function() { Validador.evaluarInput(this, Patrones.textoCorto, "Solo letras, mínimo 3 caracteres"); });
    }

    // Código
    if (inputCodigo) {
        inputCodigo.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputCodigo.addEventListener("keyup", function() { Validador.evaluarInput(this, Patrones.codigoBanco, "Debe ser de 4 dígitos"); });
    }

    // Cuenta
    if (inputCuenta) {
        inputCuenta.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputCuenta.addEventListener("keyup", function() {
            if (Validador.evaluarInput(this, Patrones.numeroCuenta, "Entre 18 y 30 dígitos")) {
                if (typeof numero_cuenta_an !== 'undefined' && this.value === numero_cuenta_an) return;
                Validador.verificarDuplicadoEnServidor('numero_cuenta', { numero_cuenta: this.value }, this, 'Esta cuenta ya está registrada');
            }
        });
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
                        if(typeof numero_cuenta_an !== 'undefined') numero_cuenta_an = null;
                    }
                });
            }
        });
    }
});

async function validarEnvio(accion) {
    const inputNombre = document.getElementById("nombre_banco");
    const inputCuenta = document.getElementById("numero_cuenta");
    
    if (!Validador.evaluarInput(inputNombre, Patrones.textoCorto, 'Solo letras, mínimo 3 caracteres') ||
        !Validador.evaluarInput(document.getElementById("codigo"), Patrones.codigoBanco, 'Debe ser de 4 dígitos') ||
        !Validador.evaluarInput(inputCuenta, Patrones.numeroCuenta, 'Entre 18 y 30 dígitos') || 
        !Validador.evaluarInput(document.getElementById("telefono_afiliado"), Patrones.telefono, 'Debe tener 11 dígitos') ||
        !Validador.evaluarSelect("tipo_documento") ||
        !Validador.evaluarInput(document.getElementById("rif"), Patrones.rif, 'Entre 7 y 9 dígitos')) {
        
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    if (typeof numero_cuenta_an !== 'undefined' && numero_cuenta_an !== inputCuenta.value) {
        const duplicado = await Validador.verificarDuplicadoEnServidor('numero_cuenta', { numero_cuenta: inputCuenta.value }, inputCuenta, 'Esta cuenta ya está registrada');
        if (!duplicado) return false;
    }

    return true;
}