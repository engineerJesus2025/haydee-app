/**
 * Script de validaciones para Perfil de Usuario
 * Dependencias: validaciones.js (Objeto Validaciones), utilidades.js (Objeto Utilidades)
 */

$(document).ready(function() {
    // ============================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================
    $('#nombre, #apellido').on('keypress', function(e) {
        Validaciones.keyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, e);
    });

    $('#nombre, #apellido').on('keyup', function() {
        Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, this, this.nextElementSibling, 'Solo texto, no más de 20 caracteres');
    });

    $('#correo').on('keypress', function(e) {
        Validaciones.keyPress(/^[A-Za-z0-9_+.@\b]*$/, e);
    });

    $('#correo').on('keyup', function() {
        Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, this, this.nextElementSibling, 'El formato debe ser: ejemplo@gmail.com');
    });

    // Validación de duplicidad de correo en tiempo real
    $('#correo').on('blur', async function() {
        if ($(this).val() === correo_an) return; // No ha cambiado

        if (Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, this, this.nextElementSibling, '')) {
            const datos = new FormData();
            datos.append('validar', 'correo');
            datos.append('correo', $(this).val());
            await Validaciones.verificarDuplicado(datos,'Este correo ya está en uso, ingrese uno diferente.');
            // await verificarDuplicados(datos);
        }
    });

    // Validaciones de contraseña
    $('#contra, #confir_contra, #contra_actual').on('keyup', function() {
        Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, this, this.nextElementSibling.nextElementSibling, 'Mínimo 5 caracteres');
    });

    // ============================================
    // ENVÍO DE FORMULARIO DE PERFIL
    // ============================================
    $('#boton_guardar').on('click', async function(e) {
        e.preventDefault();
        if (await validarEnvioPerfil() === true) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Desea guardar los cambios en su perfil?',
                showCancelButton: true,
                confirmButtonText: 'Sí, Guardar',
                confirmButtonColor: '#1b8a40',
                cancelButtonText: 'Cancelar',
                icon: 'warning'
            }).then(result => {
                if (result.isConfirmed) {
                    modificar();
                    correo_an = null;
                }
            });
        }
    });

    // ============================================
    // ENVÍO DE FORMULARIO DE CAMBIO DE CONTRASEÑA
    // ============================================
    $('#boton_guardar_contra').on('click', async function(e) {
        e.preventDefault();
        if (await validarEnvioContra() === true) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Desea cambiar su contraseña?',
                showCancelButton: true,
                confirmButtonText: 'Sí, Cambiar',
                confirmButtonColor: '#1b8a40',
                cancelButtonText: 'Cancelar',
                icon: 'warning'
            }).then(result => {
                if (result.isConfirmed) {
                    modificarContra();
                }
            });
        }
    });
});

// ============================================
// FUNCIONES DE VALIDACIÓN
// ============================================

async function validarEnvioPerfil() {
    // Validar campos individuales
    if (!Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, $('#nombre')[0], $('#nombre')[0].nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'El nombre no es válido.');
        return false;
    }
    if (!Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/, $('#apellido')[0], $('#apellido')[0].nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'El apellido no es válido.');
        return false;
    }
    if (!Validaciones.keyUp(/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/, $('#correo')[0], $('#correo')[0].nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'El correo no es válido.');
        return false;
    }

    // Verificar duplicado de correo si cambió
    if (correo_an !== $('#correo').val()) {
        const datos = new FormData();
        datos.append('validar', 'correo');
        datos.append('correo', $('#correo').val());
        // const duplicado = await verificarDuplicados(datos);
        const duplicado = await Validaciones.verificarDuplicado(datos,'Este correo ya está en uso, ingrese uno diferente.');
        if (duplicado) return false;
    }

    return true;
}

async function validarEnvioContra() {
    // Validar contraseña actual
    if (!Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, $('#contra_actual')[0], $('#contra_actual')[0].nextElementSibling.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'La contraseña actual no es válida.');
        return false;
    }

    // Validar nueva contraseña
    if (!Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/, $('#contra')[0], $('#contra')[0].nextElementSibling.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'La nueva contraseña no es válida.');
        return false;
    }

    // Confirmar que coinciden
    if ($('#contra').val() !== $('#confir_contra').val()) {
        $('#contra, #confir_contra').addClass('is-invalid').removeClass('is-valid');
        Utilidades.mensaje('error', 'Error', 'Las contraseñas no coinciden.');
        return false;
    }

    // Verificar que la contraseña actual sea correcta contra el servidor
    const datos = new FormData();
    datos.append('validar', 'contra_perfil');
    datos.append('contra', $('#contra_actual').val());

    const respuesta = await Utilidades.query(datos);
    if (!respuesta) {
        $('#contra_actual').addClass('is-invalid').removeClass('is-valid');
        Utilidades.mensaje('error', 'Error', 'La contraseña actual es incorrecta.');
        return false;
    }

    return true;
}