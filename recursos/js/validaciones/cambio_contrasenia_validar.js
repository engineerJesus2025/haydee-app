/**
 * cambio_contrasenia_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    const inputContra = document.getElementById('contra');
    const inputConfir = document.getElementById('confir_contra');
    const btnCambiar = document.getElementById('btn-cambiar');
    const formCambiar = document.getElementById('form-cambiar-contrasenia');
    
    // Elementos del Indicador de Seguridad
    const contenedorFuerza = document.getElementById('contenedor-fuerza');
    const barraFuerza = document.getElementById('barra-fuerza');
    const textoFuerza = document.getElementById('texto-fuerza');
    const iconoConfirmacion = document.getElementById('icono-confirmacion');

    // Función para calcular la seguridad de la contraseña
    function evaluarSeguridad(pass) {
        let puntaje = 0;
        if (pass.length >= 5) puntaje += 1; 
        if (pass.length >= 8) puntaje += 1; 
        if (/[A-Z]/.test(pass)) puntaje += 1; 
        if (/[0-9]/.test(pass)) puntaje += 1; 
        if (/[^A-Za-z0-9]/.test(pass)) puntaje += 1; 

        if (pass.length === 0) return { nivel: 'vacio' };
        if (puntaje <= 2) return { nivel: 'debil', pct: '33%', bg: 'bg-danger', txt: 'Débil', color: 'text-danger' };
        if (puntaje === 3 || puntaje === 4) return { nivel: 'media', pct: '66%', bg: 'bg-warning', txt: 'Aceptable', color: 'text-warning' };
        return { nivel: 'fuerte', pct: '100%', bg: 'bg-success', txt: 'Fuerte', color: 'text-success' };
    }

    // Función principal que se ejecuta CADA VEZ que el usuario escribe o borra algo
    function verificarFormulario() {
        const pass = inputContra.value;
        const confir = inputConfir.value;
        const seguridad = evaluarSeguridad(pass);

        // 1. LÓGICA DEL INPUT SUPERIOR (Nueva Contraseña)
        if (pass.length > 0) {
            if (pass.length >= 5) {
                EstadoInputs.marcarExito(inputContra); // Se pinta de verde
            } else {
                EstadoInputs.marcarError(inputContra, 'Debe tener al menos 5 caracteres'); // Se pinta de rojo
            }
        } else {
            EstadoInputs.limpiar(inputContra); // Si está vacío, vuelve a su color neutro
        }

        // 2. LÓGICA DE LA BARRA DE SEGURIDAD
        if (seguridad.nivel === 'vacio') {
            contenedorFuerza.classList.add('d-none');
        } else {
            contenedorFuerza.classList.remove('d-none');
            barraFuerza.style.width = seguridad.pct;
            barraFuerza.className = 'progress-bar transition-all ' + seguridad.bg;
            textoFuerza.textContent = seguridad.txt;
            textoFuerza.className = 'fw-bold ' + seguridad.color;
        }

        // 3. LÓGICA DEL INPUT INFERIOR (Confirmar) Y SU ÍCONO
        let coinciden = false;
        if (confir.length > 0) {
            if (pass === confir && pass.length >= 5) {
                coinciden = true;
                iconoConfirmacion.className = 'bi bi-check-circle-fill text-success mi-icono fs-5'; 
                EstadoInputs.marcarExito(inputConfir); // Lo pintamos de verde si coinciden
            } else {
                iconoConfirmacion.className = 'bi bi-x-circle text-danger mi-icono fs-5'; 
                EstadoInputs.marcarError(inputConfir, 'Las contraseñas no coinciden'); // Vuelve a rojo si borran algo
            }
        } else {
            iconoConfirmacion.className = 'bi bi-check2-circle text-muted mi-icono fs-5'; 
            EstadoInputs.limpiar(inputConfir); // Limpia los colores si borran todo
        }

        // 4. HABILITAR / DESHABILITAR EL BOTÓN
        if (pass.length >= 5 && coinciden) {
            btnCambiar.disabled = false;
        } else {
            btnCambiar.disabled = true;
        }
    }

    // Escuchar eventos: 'input' detecta inmediatamente cada letra que se escribe o se borra
    if (inputContra && inputConfir) {
        inputContra.addEventListener('input', verificarFormulario);
        inputConfir.addEventListener('input', verificarFormulario);

        // Bloqueamos caracteres inválidos
        inputContra.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
        inputConfir.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
    }

    // Mostrar/ocultar contraseñas
    document.querySelectorAll('.contra-btn').forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            let inputTarget = this.parentElement.querySelector('input'); 
            let icono = this.querySelector('i');

            if (!inputTarget || !icono) return;

            if (icono.classList.contains('bi-eye') || icono.classList.contains('bi-eye-fill')) {
                inputTarget.setAttribute('type', 'text');
                icono.classList.remove('bi-eye', 'bi-eye-fill');
                icono.classList.add('bi-eye-slash-fill');
            } else {
                inputTarget.setAttribute('type', 'password');
                icono.classList.remove('bi-eye-slash', 'bi-eye-slash-fill');
                icono.classList.add('bi-eye-fill');
            }
        });
    });

    // Envío del formulario
    if (btnCambiar) {
        btnCambiar.addEventListener('click', async function(e) {
            e.preventDefault();
            if (await validarEnvio()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea cambiar su contraseña?`,
                    "question", 
                    () => {
                        formCambiar.submit();
                    }
                );
            }
        });
    }
});

async function validarEnvio() {
    const contra = document.getElementById('contra');
    const confir = document.getElementById('confir_contra');

    if (!Validador.evaluarInput(contra, Patrones.contrasena, '')) {
        Alertas.mostrar('error', 'Error', 'La contraseña debe tener al menos 5 caracteres.');
        return false;
    }

    if (contra.value !== confir.value) {
        EstadoInputs.marcarError(confir, 'Las contraseñas no coinciden');
        Alertas.mostrar('error', 'Error', 'Las contraseñas no coinciden.');
        return false;
    }

    return true;
}