/**
 * cambio_contrasenia_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    const inputContra = document.getElementById('contra');
    const inputConfir = document.getElementById('confir_contra');
    const btnCambiar = document.getElementById('btn-cambiar');
    const formCambiar = document.getElementById('form-cambiar-contrasenia');

    // Validación en tiempo real
    if (inputContra) {
        inputContra.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
        inputContra.addEventListener('keyup', function() {
            Validador.evaluarInput(this, Patrones.contrasena, 'Mínimo 5 caracteres permitidos');
        });
    }

    if (inputConfir) {
        inputConfir.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
        inputConfir.addEventListener('keyup', function() {
            Validador.evaluarInput(this, Patrones.contrasena, 'Mínimo 5 caracteres permitidos');
        });
    }
    
    // Mostrar/ocultar contraseñas (Lógica a prueba de fallos)
    document.querySelectorAll('.contra-btn').forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            let inputTarget = this.previousElementSibling; 
            let icono = this.querySelector('i');

            if (icono && icono.classList.contains('bi-eye')) {
                inputTarget.setAttribute('type', 'text');
                icono.classList.replace('bi-eye', 'bi-eye-slash');
            } else if (icono) {
                inputTarget.setAttribute('type', 'password');
                icono.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // Envío del formulario
    if (btnCambiar) {
        btnCambiar.addEventListener('click', async function(e) {
            e.preventDefault();
            if (await validarEnvio()) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Desea cambiar su contraseña?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1b8a40',
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) formCambiar.submit();
                });
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