let recuperacionContrasenia = { enviada: false, tiempo: null };
let recaptchaToken = null;
let recaptchaWidgetId = null;
const recaptchaDesactivado = window.RECAPTCHA_DESACTIVADO === true; 
const modal_carga = new bootstrap.Modal("#modal_carga", { focus: false });

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

document.addEventListener('DOMContentLoaded', () => {
    // Alertas por URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('err') === '1') Alertas.mostrar('success', 'Atención', 'La contraseña se ha cambiado exitosamente');
    else if (['2', '4'].includes(urlParams.get('err'))) Alertas.mostrar('error', 'Error', 'El Token Recibido no es válido');
    else if (urlParams.get('err') === '3') Alertas.mostrar('error', 'Error', 'No se pudo cambiar la contraseña');

    const btnEnviar = document.getElementById('enviar');

    // reCAPTCHA inicialización
    if (recaptchaDesactivado) {
        if (btnEnviar) btnEnviar.disabled = false;
    } else {
        if (typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha') && btnEnviar) {
            btnEnviar.disabled = true;
        }
    }

    // Validaciones en tiempo real
    const correoLogin = document.getElementById('correo_login');
    if (correoLogin) {
        correoLogin.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCorreo));
        correoLogin.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.correo, 'Ejemplo: alguien@servidor.com'));
    }

    const correoRecuperar = document.getElementById('correo_recuperar');
    if (correoRecuperar) {
        correoRecuperar.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCorreo));
        correoRecuperar.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.correo, 'Ejemplo: alguien@servidor.com'));
    }

    const contra = document.getElementById('contra');
    if (contra) {
        contra.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.contrasena, 'Mínimo 5 caracteres'));
    }

    // Eventos de botones
    if (btnEnviar) {
        btnEnviar.addEventListener('click', async e => {
            e.preventDefault();
            if (await validarLogin()) await realizarLogin();
        });
    }

    const btnRecuperar = document.getElementById('boton_recuperar');
    if (btnRecuperar) {
        btnRecuperar.addEventListener('click', async e => {
            e.preventDefault();
            await realizarRecuperacion();
        });
    }

    // Limpieza de modal
    const modalCambiarContra = document.getElementById('modal_recuperar_contrasenia');
    if (modalCambiarContra) {
        modalCambiarContra.addEventListener('hide.bs.modal', () => {
            const form = document.getElementById('form_recuperar_contra');
            form.reset();
            form.querySelectorAll('input').forEach(input => EstadoInputs.limpiar(input));
        });
    }
});

async function validarLogin() {
    const vCorreo = Validador.evaluarInput(document.getElementById('correo_login'), Patrones.correo, 'Correo inválido');
    const vContra = Validador.evaluarInput(document.getElementById('contra'), Patrones.contrasena, 'Contraseña inválida');

    if (!vCorreo || !vContra) {
        Alertas.mostrar('error', 'Error', 'Verifique los campos ingresados');
        return false;
    }

    if (!recaptchaDesactivado && typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha')) {
        if (!recaptchaToken) {
            Alertas.mostrar('error', 'Validación requerida', 'Debe completar el reCAPTCHA');
            return false;
        }
    }
    return true;
}

async function realizarLogin() {
    // ---- ACTIVAR ESTADO DE CARGA ----
    const btnEnviar = document.getElementById('enviar');
    const textoBoton = document.getElementById('texto-boton');
    const iconoBoton = document.getElementById('icono-boton');
    const spinnerBoton = document.getElementById('spinner-boton');

    if (btnEnviar) {
        btnEnviar.disabled = true; 
        textoBoton.textContent = 'Cargando...'; 
        iconoBoton.classList.add('d-none');     
        spinnerBoton.classList.remove('d-none');
    }
    // ----------------------------------------

    const formData = new FormData();
    formData.append('usuario', document.getElementById('correo_login').value);
    formData.append('contra', document.getElementById('contra').value);
    formData.append('mantener_sesion', document.getElementById('checkbox_mantener_sesion')?.checked || false);
    formData.append('operacion', 'entrar');

    if (!recaptchaDesactivado && recaptchaToken) {
        formData.append('g-recaptcha-response', recaptchaToken);
    }

    const resultado = await Peticiones.enviar(formData, "", true);

    if (resultado.estatus) {
        await obtenerTasaDolar();
        window.location = "?pagina=inicio&accion=inicio";
        // Nota: No quitamos el loading aquí porque la página ya va a recargar y redireccionar
    } else {
        if (!recaptchaDesactivado && typeof grecaptcha !== 'undefined' && recaptchaWidgetId !== null) {
            grecaptcha.reset(recaptchaWidgetId);
            recaptchaToken = null;
        }

        Alertas.mostrar('error', 'Error', resultado.mensaje || 'Datos incorrectos');
        
        // ---- DESACTIVAR ESTADO DE CARGA SI HUBO ERROR ----
        if (btnEnviar) {
            btnEnviar.disabled = false; 
            textoBoton.textContent = 'Ingresar';  
            iconoBoton.classList.remove('d-none');
            spinnerBoton.classList.add('d-none'); 
        }
    }
}

async function realizarRecuperacion() {
    const correoInput = document.getElementById('correo_recuperar');
    
    // Capturamos los elementos del nuevo botón
    const btnRecuperar = document.getElementById('boton_recuperar');
    const textoBotonRec = document.getElementById('texto-boton-recuperar');
    const iconoBotonRec = document.getElementById('icono-boton-recuperar');
    const spinnerBotonRec = document.getElementById('spinner-boton-recuperar');

    if (!Validador.evaluarInput(correoInput, Patrones.correo, 'Correo inválido')) {
        Alertas.mostrar('error', 'Verifique el correo', 'El formato del correo no es válido');
        return;
    }

    if (recuperacionContrasenia.enviada && (new Date() - recuperacionContrasenia.tiempo) < 60000) {
        Alertas.mostrar('warning', 'Espere', 'Ya se envió un correo recientemente, espere un minuto');
        return;
    }

    // ---- ACTIVAR ESTADO DE CARGA ----
    if (btnRecuperar) {
        btnRecuperar.disabled = true;
        textoBotonRec.textContent = 'Enviando...';
        iconoBotonRec.classList.add('d-none');
        spinnerBotonRec.classList.remove('d-none');
    }

    const formData = new FormData();
    formData.append('correo_recuperar', correoInput.value);
    formData.append('operacion', 'enviar_notificacion');

    await Peticiones.enviar(formData, "", true);

    Alertas.mostrar('warning', 'Atención', 'Revise su bandeja de entrada del correo. Si el correo ingresado está en el sistema, encontrará un enlace para recuperar su contraseña.');

    recuperacionContrasenia.enviada = true;
    recuperacionContrasenia.tiempo = new Date();

    // ---- DESACTIVAR ESTADO DE CARGA ----
    if (btnRecuperar) {
        btnRecuperar.disabled = false;
        textoBotonRec.textContent = 'Enviar Instrucciones';
        iconoBotonRec.classList.remove('d-none');
        spinnerBotonRec.classList.add('d-none');
        
        // Cerrar el modal
        bootstrap.Modal.getInstance(document.getElementById('modal_recuperar_contrasenia')).hide();
    }
}

async function obtenerTasaDolar() {
    const fechaGuardada = localStorage.getItem('fecha_tasa_dolar');
    const tasaGuardada = localStorage.getItem('tasa_dolar');

    if (fechaGuardada && tasaGuardada) {
        const fechaTasa = new Date(fechaGuardada);
        if (fechaTasa.toDateString() === new Date().toDateString()) return;
    }

    try {
        const respuesta = await fetch("https://ve.dolarapi.com/v1/dolares/oficial");
        if (!respuesta.ok) throw new Error('Error al obtener tasa');
        const data = await respuesta.json();
        localStorage.setItem('fecha_tasa_dolar', data.fechaActualizacion);
        localStorage.setItem('tasa_dolar', data.promedio.toString());
    } catch (error) {
        console.error('No se pudo actualizar la tasa de dólar');
    }
}

// ==========================================
// VER/OCULTAR CONTRASEÑA
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const btnVerContra = document.getElementById('btn-ver-contra');
    const inputContra = document.getElementById('contra');

    if (btnVerContra && inputContra) {
        btnVerContra.addEventListener('click', function() {
            // Verificamos el tipo actual del input
            const esPassword = inputContra.getAttribute('type') === 'password';
            
            // Cambiamos el tipo de input (de password a text o viceversa)
            inputContra.setAttribute('type', esPassword ? 'text' : 'password');
            
            // Cambiamos el ícono de Bootstrap (ojo abierto a ojo cerrado)
            const icono = this.querySelector('i');
            icono.classList.remove(esPassword ? 'bi-eye-fill' : 'bi-eye-slash-fill');
            icono.classList.add(esPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill');
        });
    }
});