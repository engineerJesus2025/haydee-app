/**
 * usuario_perfil_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */
document.addEventListener("DOMContentLoaded", function() {
    const inputNombre = document.querySelector('#nombre');
    const inputApellido = document.querySelector('#apellido');
    const inputCorreo = document.querySelector('#correo');
    const inputContra = document.querySelector('#contra');
    const inputConfirContra = document.querySelector('#confir_contra');

    // VALIDACIONES EN TIEMPO REAL
    [inputNombre, inputApellido].forEach(input => {
        if (!input) return;
        input.addEventListener('keypress', (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        input.addEventListener('keyup', (e) => Validador.evaluarInput(e.target, Patrones.nombrePersona, 'Solo texto, no más de 20 caracteres'));
    });

    if (inputCorreo) {
        inputCorreo.addEventListener('keypress', (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCorreo));
        inputCorreo.addEventListener('keyup', (e) => Validador.evaluarInput(e.target, Patrones.correo, 'El formato debe ser: ejemplo@gmail.com'));
        
        // Duplicidad de correo en blur
        inputCorreo.addEventListener('blur', async function() {
            if (this.value === correo_an) return; 
            if (Patrones.correo.test(this.value)) {
                await Validador.verificarDuplicadoEnServidor('correo', { correo: this.value }, this, 'Este correo ya está en uso, ingrese uno diferente.');
            }
        });
    }

    // Contraseñas
    [inputContra, inputConfirContra].forEach(input => {
        if (!input) return;
        input.addEventListener('keypress', (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
        input.addEventListener('keyup', (e) => Validador.evaluarContrasenaFuerte(e.target));
    });

    // MEDIDOR DE FORTALEZA DE CONTRASEÑA
    if (inputContra) {
        ComponentesUI.inicializarMedidorContrasena(
            inputContra,
            document.getElementById('barra_seguridad'),
            document.getElementById('texto_seguridad')
        );
    }

    // ENVÍO DE FORMULARIOS
    const btnGuardarPerfil = document.querySelector('#boton_guardar');
    if (btnGuardarPerfil) {
        btnGuardarPerfil.addEventListener('click', async function(e) {
            e.preventDefault();
            if (await validarEnvioPerfil()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Desea guardar los cambios en su perfil?`,
                    "question", 
                    () => {
                        modificar();
                        correo_an = null;
                    }
                );
            }
        });
    }

    const btnGuardarContra = document.querySelector('#boton_guardar_contra');
    if (btnGuardarContra) {
        btnGuardarContra.addEventListener('click', async function(e) {
            e.preventDefault();
            if (await validarEnvioContra()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Desea cambiar su contraseña?`,
                    "question", 
                    () => {
                        modificarContra();
                    }
                );
            }
        });
    }
});

async function validarEnvioPerfil() {
    const elNombre = document.querySelector('#nombre');
    const elApellido = document.querySelector('#apellido');
    const elCorreo = document.querySelector('#correo');

    const vNombre = Validador.evaluarInput(elNombre, Patrones.nombrePersona, 'El nombre no es válido.');
    const vApellido = Validador.evaluarInput(elApellido, Patrones.nombrePersona, 'El apellido no es válido.');
    const vCorreo = Validador.evaluarInput(elCorreo, Patrones.correo, 'El correo no es válido.');

    if (!vNombre || !vApellido || !vCorreo) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    if (correo_an !== elCorreo.value) {
        const duplicado = await Validador.verificarDuplicadoEnServidor('correo', { correo: elCorreo.value }, elCorreo, 'Este correo ya está en uso');
        if (!duplicado) return false;
    }

    return true;
}

async function validarEnvioContra() {
    const elContra = document.querySelector('#contra');
    const elConfirContra = document.querySelector('#confir_contra');

    const vContra = Validador.evaluarContrasenaFuerte(elContra);
    
    if (!vContra) {
        Alertas.mostrar('error', 'Error',
            'La nueva contraseña debe tener mínimo 8 caracteres, con mayúscula, minúscula, número y un carácter especial.');
        return false;
    }

    if (elContra.value !== elConfirContra.value) {
        EstadoInputs.marcarError(elContra, "Las contraseñas no coinciden");
        EstadoInputs.marcarError(elConfirContra, "Las contraseñas no coinciden");
        Alertas.mostrar('error', 'Error', 'Las contraseñas no coinciden.');
        return false;
    }

    return true;
}