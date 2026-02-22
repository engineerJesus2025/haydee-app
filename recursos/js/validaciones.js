/**
 * validaciones.js
 * Helper global para validaciones de formularios en el sistema Haydee.
 */
const Validaciones = {

    /**
     * Verifica duplicados en la base de datos de forma asíncrona.
     */
    async verificarDuplicado(datosAjax, mensajeError) {
        let respuesta = await Utilidades.query(datosAjax);
        
        if (respuesta.estatus && respuesta.busqueda) {
            let input = document.getElementById(respuesta.busqueda);
            if (input) {
                input.nextElementSibling.textContent = mensajeError;
                input.classList.replace('is-valid', 'is-invalid');
            }
            Utilidades.mensaje('error', 'Registro Duplicado', mensajeError);
            return true; // Es duplicado
        }
        return false; // No es duplicado
    },
    
    /**
     * Valida la entrada en tiempo real (evento keypress) bloqueando caracteres no permitidos.
     * @param {RegExp} regex - Expresión regular permitida
     * @param {Event} e - Evento keypress
     */
    keyPress(regex, e) {
        const key = e.keyCode || e.which;
        const tecla = String.fromCharCode(key);
        if (!regex.test(tecla)) {
            e.preventDefault();
        }
    },

    /**
     * Valida el valor completo del input (evento keyup o change).
     * @param {RegExp} regex - Expresión regular a cumplir
     * @param {HTMLElement} input - Elemento HTML input
     * @param {HTMLElement} errorElement - Elemento donde se mostrará el error (ej. span, div)
     * @param {String} mensajeError - Mensaje a mostrar si falla
     * @returns {Boolean}
     */
    keyUp(regex, input, errorElement, mensajeError) {
        const esValido = regex.test(input.value);

        if (esValido) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (errorElement) errorElement.textContent = "";

            // Caso específico para las contraseñas en tu sistema (íconos de ojo)
            if (input.id === "contra" || input.id === "confir_contra") {
                input.nextElementSibling.classList.replace('border-danger', 'border-success');
                input.nextElementSibling.classList.replace('text-danger', 'text-success');
            }
            return true;
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (errorElement) errorElement.textContent = mensajeError;

            // Caso específico contraseñas
            if (input.id === "contra" || input.id === "confir_contra") {
                input.nextElementSibling.classList.replace('border-success', 'border-danger');
                input.nextElementSibling.classList.replace('text-success', 'text-danger');
            }
            return false;
        }
    },

    /**
     * Valida que un select no esté vacío.
     * @param {String} id - ID del elemento select
     * @returns {Boolean}
     */
    select(id) {
        const selec = document.getElementById(id);
        if (!selec) return false;

        if (selec.value === '') {
            selec.classList.remove('is-valid');
            selec.classList.add('is-invalid');
            if (selec.nextElementSibling) selec.nextElementSibling.textContent = "Debe seleccionar una opción";
            return false;
        } else {
            selec.classList.remove('is-invalid');
            selec.classList.add('is-valid');
            if (selec.nextElementSibling) selec.nextElementSibling.textContent = "";
            return true;
        }
    },

    selectCustom(etiqueta,regex,mensaje) {
        const esValido = regex.test(etiqueta.value);

        if (esValido) {
            etiqueta.classList.remove('is-invalid');
            etiqueta.classList.add('is-valid');
            etiqueta.nextElementSibling.textContent = "";

            return true;
        } else {
            etiqueta.classList.remove('is-valid');
            etiqueta.classList.add('is-invalid');
            etiqueta.nextElementSibling.textContent = mensaje;
            
            return false;
        }
    },

    fecha(input, errorElement, mostrarMensaje = false) {
        let valor = input.value;
        let regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(valor)) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (errorElement) errorElement.textContent = 'Formato debe ser YYYY-MM-DD';
            return false;
        }
        let [anio, mes, dia] = valor.split('-').map(Number);
        let fecha = new Date(anio, mes-1, dia);
        if (fecha.getFullYear() !== anio || fecha.getMonth() !== mes-1 || fecha.getDate() !== dia) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (errorElement) errorElement.textContent = 'Fecha inválida';
            return false;
        }
        if (anio < 2000) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (errorElement) errorElement.textContent = 'Año debe ser ≥ 2000';
            return false;
        }
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        if (errorElement) errorElement.textContent = '';
        return true;
    },

    /**
     * Muestra un error de validación en un campo.
     * @param {HTMLElement} input - El elemento input.
     * @param {string} mensaje - Mensaje de error.
     */
    mostrarError(input, mensaje) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = mensaje;
        }
    },

    /**
     * Limpia el estado de validación de un campo.
     * @param {HTMLElement} input 
     */
    limpiar(input) {
        input.classList.remove('is-invalid', 'is-valid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    },

    /**
     * Valida que un campo no esté vacío y tenga formato correcto.
     * @param {HTMLElement} input 
     * @param {RegExp} regex 
     * @param {string} mensaje 
     * @returns {boolean}
     */
    campo(input, regex, mensaje) {
        if (!regex.test(input.value)) {
            this.mostrarError(input, mensaje);
            return false;
        }
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        return true;
    },

    /**
     * Verifica en el servidor si un valor ya existe (duplicado).
     * @param {string} tipo - Tipo de validación (ej. 'correo', 'mes', 'anio').
     * @param {Object} datos - Datos adicionales.
     * @param {HTMLElement} input - Input a marcar en caso de error.
     * @param {string} mensajeError - Mensaje a mostrar si ya existe.
     * @returns {Promise<boolean>} - true si es válido (no existe), false si ya existe.
     */
    async verificarExistencia(tipo, datos, input, mensajeError) {
        const respuesta = await Utilidades.validar(tipo, datos);
        if (respuesta.estatus === false || (respuesta.existe === true)) {
            this.mostrarError(input, mensajeError);
            return false;
        }
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        return true;
    }
};