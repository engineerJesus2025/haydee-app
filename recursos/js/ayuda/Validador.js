/**
 * Validador.js
 * Propósito: Aplicar reglas de validación a los datos e inputs.
 * Dependencias: EstadoInputs, Peticiones
 */
const Validador = {
    /**
     * Previene que se escriban caracteres no permitidos en tiempo real.
     * Úsalo en el evento onkeypress.
     */
    bloquearTeclasInvalidas(evento, expresionRegular) {
        const teclaPulsada = String.fromCharCode(evento.keyCode || evento.which);
        if (!expresionRegular.test(teclaPulsada)) {
            evento.preventDefault();
        }
    },

    /**
     * Valida el contenido de un input usando una expresión regular.
     * Úsalo en los eventos onkeyup o onchange.
     */
    evaluarInput(input, expresionRegular, mensajeError) {
        if (expresionRegular.test(input.value)) {
            EstadoInputs.marcarExito(input);
            return true;
        } else {
            EstadoInputs.marcarError(input, mensajeError);
            return false;
        }
    },

    /**
     * Valida que un elemento <select> tenga una opción elegida.
     */
    evaluarSelect(idSelect) {
        const select = document.getElementById(idSelect);
        if (!select) return false;

        if (select.value === '') {
            EstadoInputs.marcarError(select, "Debe seleccionar una opción");
            return false;
        } else {
            EstadoInputs.marcarExito(select);
            return true;
        }
    },

    /**
     * Valida una fecha asegurando formato YYYY-MM-DD y lógica de calendario.
     */
    evaluarFecha(input, mensajeError = "Fecha inválida o formato incorrecto") {
        const valor = input.value;
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        
        if (!regex.test(valor)) {
            EstadoInputs.marcarError(input, "El formato debe ser YYYY-MM-DD");
            return false;
        }
        
        const [anio, mes, dia] = valor.split('-').map(Number);
        const fecha = new Date(anio, mes - 1, dia);
        
        if (fecha.getFullYear() !== anio || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia || anio < 2000) {
            EstadoInputs.marcarError(input, mensajeError);
            return false;
        }
        
        EstadoInputs.marcarExito(input);
        return true;
    },

    /**
     * Consulta al servidor si un dato ya existe en la base de datos (Asíncrono).
     */
    async verificarDuplicadoEnServidor(accionBackend, datosExtra, input, mensajeError) {
        const formData = new FormData();
        formData.append('validar', accionBackend);
        
        // Agregar datos extra al FormData
        for (const llave in datosExtra) {
            formData.append(llave, datosExtra[llave]);
        }

        const respuesta = await Peticiones.enviar(formData, "", false);
        
        // Asumiendo que tu backend devuelve { estatus: false, existe: true } cuando falla
        if (respuesta.estatus === false || respuesta.existe === true) {
            EstadoInputs.marcarError(input, mensajeError);
            return false; // Es inválido porque ya existe
        }
        
        EstadoInputs.marcarExito(input);
        return true; // Es válido
    },

    /**
     * Consulta al servidor si un dato EXISTE en la base de datos (Asíncrono).
     * Útil para validar claves foráneas o dependencias (ej: ¿Existe presupuesto para este mes?).
     * Retorna true si EXISTE, false si NO EXISTE.
     */
    async verificarExistenciaEnServidor(accionBackend, datosExtra, input, mensajeError) {
        const formData = new FormData();
        formData.append('validar', accionBackend);
        
        for (const llave in datosExtra) {
            formData.append(llave, datosExtra[llave]);
        }

        // El tercer parámetro 'false' evita que el spinner de carga parpadee en cada tecla/blur
        const respuesta = await Peticiones.enviar(formData, "", false); 
        
        // Ajusta la lógica según lo que responda tu backend (ej: respuesta.existe === true)
        if (respuesta.estatus === false || respuesta.existe === false) {
            EstadoInputs.marcarError(input, mensajeError);
            return false; // Es inválido porque NO existe
        }
        
        EstadoInputs.marcarExito(input);
        return true; // Es válido porque SÍ existe
    }
};