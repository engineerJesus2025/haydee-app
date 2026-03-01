/**
 * cambio_contrasenia_validar.js
 * Validaciones para el formulario de cambio de contraseña
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    const contra = $('#contra');
    const confir = $('#confir_contra');
    const btn = $('#btn-cambiar');
    const form = $('#form-cambiar-contrasenia');

    // Validación en tiempo real para contraseña
    contra.on('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9_.+*$#%&@-]$/, e));
    contra.on('keyup', function() {
        Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, this, this.nextElementSibling.nextElementSibling, 
            'Mínimo 5 caracteres, se permiten: letras, números y ._+*$#%&@-');
    });

    // Confirmar contraseña
    confir.on('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9_.+*$#%&@-]$/, e));
    confir.on('keyup', function() {
        Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, this, this.nextElementSibling.nextElementSibling, 
            'Mínimo 5 caracteres');
    });
    
	// Mostrar/ocultar contraseñas
    document.querySelectorAll('.contra-btn').forEach(boton => {
        boton.addEventListener('click', e => {
            e.preventDefault();
            let input = boton.previousElementSibling; // el input está antes del botón
            let icono = boton.querySelector('i');
            if (icono.classList.contains('bi-eye')) {
                input.setAttribute('type', 'text');
                icono.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.setAttribute('type', 'password');
                icono.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // Al enviar el formulario
    btn.on('click', async function(e) {
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
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
});

async function validarEnvio() {
    const contra = document.getElementById('contra');
    const confir = document.getElementById('confir_contra');

    // Validar formato de la contraseña
    if (!Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, contra, contra.nextElementSibling.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'La contraseña debe tener al menos 5 caracteres y solo caracteres permitidos.');
        return false;
    }

    // Validar que coincidan
    if (contra.value !== confir.value) {
        Validaciones.mostrarError(confir, 'Las contraseñas no coinciden');
        Utilidades.mensaje('error', 'Error', 'Las contraseñas no coinciden.');
        return false;
    }

    return true;
}

