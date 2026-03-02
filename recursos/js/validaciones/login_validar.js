/**
 * login_validar.js
 * Validaciones y peticiones para el login y recuperación de contraseña
 * Dependencias: utilidades.js, validaciones.js
 */

// ============================================================
// VARIABLES GLOBALES
// ============================================================
let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
const modalCarga = new bootstrap.Modal("#modal_carga");

let recuperacionContrasenia = {
    enviada: false,
    tiempo: null
};

// Estado de reCAPTCHA
let recaptchaToken = null;
let recaptchaWidgetId = null;
const recaptchaDesactivado = window.RECAPTCHA_DESACTIVADO === true; // true si está desactivado

// ============================================================
// FUNCIONES DE CALLBACK PARA reCAPTCHA (deben ser globales)
// ============================================================
window.onRecaptchaSuccess = function(token) {
    recaptchaToken = token;
    document.getElementById('enviar').disabled = false;
};

window.onRecaptchaExpired = function() {
    recaptchaToken = null;
    document.getElementById('enviar').disabled = true;
};

window.onRecaptchaError = function() {
    recaptchaToken = null;
    document.getElementById('enviar').disabled = true;
};

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // Mensaje de resultado de cambio de contraseña (si viene por URL)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('err') === '1') {
        Utilidades.mensaje('success', 'Atención', 'La contraseña se ha cambiado exitosamente');
    } else if (urlParams.get('err') === '2' || urlParams.get('err') === '4') {
        Utilidades.mensaje('error', 'Error', 'El Token Recibido no es válido');
    } else if (urlParams.get('err') === '3') {
        Utilidades.mensaje('error', 'Error', 'No se pudo cambiar la contraseña');
    }


    // Si reCAPTCHA está desactivado, habilitar el botón directamente
    if (recaptchaDesactivado) {
        document.getElementById('enviar').disabled = false;
    } else {
        // Si hay reCAPTCHA en la página, deshabilitar el botón hasta que se complete
        if (typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha')) {
            document.getElementById('enviar').disabled = true;
        }
    }

    // Validaciones en tiempo real
    const correoLogin = document.getElementById('correo_login');
    if (correoLogin) {
        correoLogin.addEventListener('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9_ .@]$/, e));
        correoLogin.addEventListener('keyup', () => 
            Validaciones.keyUp(/^[A-Za-z0-9_.]{3,20}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, 
                correoLogin, correoLogin.nextElementSibling, 
                'Ejemplo: alguien@servidor.com')
        );
    }

    const correoRecuperar = document.getElementById('correo_recuperar');
    if (correoRecuperar) {
        correoRecuperar.addEventListener('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9_ .@]$/, e));
        correoRecuperar.addEventListener('keyup', () =>
            Validaciones.keyUp(/^[A-Za-z0-9_.]{3,20}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, 
                correoRecuperar, correoRecuperar.nextElementSibling, 
                'Ejemplo: alguien@servidor.com')
        );
    }

    const contra = document.getElementById('contra');
    if (contra) {
        contra.addEventListener('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9_.+*$#%&/]$/, e));
        contra.addEventListener('keyup', () =>
            Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&/]{5,50}$/, 
                contra, contra.nextElementSibling, 
                'Mínimo 5 caracteres, se permiten especiales')
        );
    }

    // Evento del botón enviar (login)
    const btnEnviar = document.getElementById('enviar');
    if (btnEnviar) {
        btnEnviar.addEventListener('click', async e => {
            e.preventDefault();
            if (await validarLogin()) {
                await realizarLogin();
            }
        });
    }

    // Evento del botón recuperar
    const btnRecuperar = document.getElementById('boton_recuperar');
    if (btnRecuperar) {
        btnRecuperar.addEventListener('click', async e => {
            e.preventDefault();
            await realizarRecuperacion();
        });
    }

    // Evento de limpieza de formulario
    const modalCambiarContra = document.getElementById('modal_recuperar_contrasenia');
    if (modalCambiarContra) {
        modalCambiarContra.addEventListener('hide.bs.modal', e => {
            document.getElementById('form_recuperar_contra').reset()
            document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
            document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
        });
    }

    // Verificar si reCAPTCHA está presente en la página
    if (typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha')) {
        // El botón de enviar comienza deshabilitado hasta que se complete el reCAPTCHA
        btnEnviar.disabled = true;
    }
});

// ============================================================
// FUNCIONES DE VALIDACIÓN
// ============================================================
async function validarLogin() {
    const correo = document.getElementById('correo_login');
    const contra = document.getElementById('contra');

    if (!Validaciones.keyUp(/^[A-Za-z0-9_.]{3,20}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, 
        correo, correo.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Verifique el correo', 'El correo ingresado no es válido');
        return false;
    }

    if (!Validaciones.keyUp(/^[A-Za-z0-9_.+*$#%&/]{5,50}$/, 
        contra, contra.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Verifique la contraseña', 'La contraseña debe tener al menos 5 caracteres');
        return false;
    }

    // Validar reCAPTCHA solo si no está desactivado
    if (!recaptchaDesactivado && typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha')) {
        if (!recaptchaToken) {
            Utilidades.mensaje('error', 'Validación requerida', 'Debe completar el reCAPTCHA');
            return false;
        }
    }

    return true;
}

// ============================================================
// FUNCIONES DE PETICIONES AJAX
// ============================================================
async function realizarLogin() {
    const formData = new FormData();
    formData.append('usuario', document.getElementById('correo_login').value);
    formData.append('contra', document.getElementById('contra').value);
    formData.append('mantener_sesion', document.getElementById('checkbox_mantener_sesion')?.checked || false);
    formData.append('operacion', 'entrar');

    // Agregar token de reCAPTCHA solo si está activo
    if (!recaptchaDesactivado && recaptchaToken) {
        formData.append('g-recaptcha-response', recaptchaToken);
    }

    const resultado = await Utilidades.query(formData, true);

    if (resultado.estatus) {
        await obtenerTasaDolar();
        window.location = "?pagina=inicio&accion=inicio";
    } else {
        // Si falla, reiniciar reCAPTCHA (si está presente)
        if (!recaptchaDesactivado && typeof grecaptcha !== 'undefined' && recaptchaWidgetId !== null) {
            grecaptcha.reset(recaptchaWidgetId);
            recaptchaToken = null;
        }
        Utilidades.mensaje('error', resultado.mensaje || 'Error', 'Intente nuevamente');
    }
}

async function realizarRecuperacion() {
    const correoInput = document.getElementById('correo_recuperar');
    const correo = correoInput.value;

    if (!Validaciones.keyUp(/^[A-Za-z0-9_.]{3,20}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, 
        correoInput, correoInput.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Verifique el correo', 'El formato del correo no es válido');
        return;
    }

    // Evitar envíos repetidos en corto tiempo (mejorar)
    if (recuperacionContrasenia.enviada && (new Date() - recuperacionContrasenia.tiempo) < 60000) {
        Utilidades.mensaje('warning', 'Espere', 'Ya se envió un correo recientemente, espere un minuto');
        return;
    }

    const formData = new FormData();
    formData.append('correo_recuperar', correo);
    formData.append('operacion', 'enviar_notificacion');

    const resultado = await Utilidades.query(formData, true);

    // Siempre mostramos el mismo mensaje por seguridad (no revelar si el correo existe)
    Utilidades.mensaje('warning', 'Atención', 
        'Revise su bandeja de entrada del correo. Si el correo ingresado está en el sistema, encontrará un enlace para recuperar su contraseña.');

    recuperacionContrasenia.enviada = true;
    recuperacionContrasenia.tiempo = new Date();
}

// ============================================================
// FUNCIÓN PARA OBTENER TASA DE DÓLAR
// ============================================================
async function obtenerTasaDolar() {
    const fechaGuardada = localStorage.getItem('fecha_tasa_dolar');
    const tasaGuardada = localStorage.getItem('tasa_dolar');

    if (fechaGuardada && tasaGuardada) {
        const fechaTasa = new Date(fechaGuardada);
        const hoy = new Date();
        if (fechaTasa.toDateString() === hoy.toDateString()) {
            return; // Tasa vigente, no hacemos nada
        }
    }

    // Intentar obtener nueva tasa
    try {
        const respuesta = await fetch("https://ve.dolarapi.com/v1/dolares/oficial");
        if (!respuesta.ok) throw new Error('Error al obtener tasa');
        const data = await respuesta.json();
        localStorage.setItem('fecha_tasa_dolar', data.fechaActualizacion);
        localStorage.setItem('tasa_dolar', data.promedio.toString());
    } catch (error) {
        console.error('No se pudo actualizar la tasa de dólar:', error);
        // No mostramos error al usuario, solo dejamos la tasa anterior o 1
    }
}