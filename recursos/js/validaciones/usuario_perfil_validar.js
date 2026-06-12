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

    // ============================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================
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
        input.addEventListener('keypress', (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCorreo));
        input.addEventListener('keyup', (e) => Validador.evaluarInput(e.target, Patrones.contrasena, 'Mínimo 5 caracteres'));
    });

    // === MEDIDOR DE FORTALEZA DE CONTRASEÑA ===
    if (inputContra) {
        inputContra.addEventListener('input', function() {
            const pass = this.value;
            let fortaleza = 0;
            
            // Reglas de puntaje
            if (pass.length >= 5) fortaleza += 25; // Longitud mínima
            if (pass.match(/[A-Z]/)) fortaleza += 25; // Contiene mayúscula
            if (pass.match(/[0-9]/)) fortaleza += 25; // Contiene número
            if (pass.match(/[^a-zA-Z\d]/)) fortaleza += 25; // Contiene carácter especial

            const barra = document.getElementById('barra_seguridad');
            const texto = document.getElementById('texto_seguridad');

            if (!barra || !texto) return;

            barra.style.width = fortaleza + '%';

            if (pass.length === 0) {
                barra.className = 'progress-bar bg-danger';
                texto.textContent = 'Nivel de seguridad: Vacío';
                texto.className = 'fw-medium text-danger d-block mb-3';
            } else if (fortaleza <= 25) {
                barra.className = 'progress-bar bg-danger';
                texto.textContent = 'Nivel de seguridad: Muy Débil';
                texto.className = 'fw-medium text-danger d-block mb-3';
            } else if (fortaleza === 50) {
                barra.className = 'progress-bar bg-warning';
                texto.textContent = 'Nivel de seguridad: Débil';
                texto.className = 'fw-medium text-warning d-block mb-3';
            } else if (fortaleza === 75) {
                barra.className = 'progress-bar bg-info';
                texto.textContent = 'Nivel de seguridad: Buena';
                texto.className = 'fw-medium text-info d-block mb-3';
            } else {
                barra.className = 'progress-bar bg-success';
                texto.textContent = 'Nivel de seguridad: Muy Fuerte';
                texto.className = 'fw-medium text-success d-block mb-3';
            }
        });
    }

    // ============================================
    // ENVÍO DE FORMULARIOS
    // ============================================
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

    const vContra = Validador.evaluarInput(elContra, Patrones.contrasena, 'La nueva contraseña no es válida.');
    
    if (!vContra) {
        Alertas.mostrar('error', 'Error', 'La nueva contraseña no es válida.');
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