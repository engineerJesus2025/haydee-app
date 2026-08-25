const Validador = {
    /**
     Previene que se escriban caracteres no permitidos en tiempo real.
     */
    bloquearTeclasInvalidas(evento, expresionRegular) {
        const teclaPulsada = String.fromCharCode(evento.keyCode || evento.which);
        if (!expresionRegular.test(teclaPulsada)) {
            evento.preventDefault();
        }
    },

    /**
     Valida el contenido de un input usando una expresión regular.
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
     Valida que un elemento <select> tenga una opción elegida.
     */
    evaluarSelect(idSelect) {
        const select = document.getElementById(idSelect);
        if (!select) {
            console.log(`Select con id ${idSelect} no encontrado`);
            return false;
        }

        if (select.value === '') {
            EstadoInputs.marcarError(select, "Debe seleccionar una opción");
            return false;
        } else {
            EstadoInputs.marcarExito(select);
            return true;
        }
    },

    /**
     Valida una fecha asegurando formato YYYY-MM-DD y lógica de calendario.
     */
    evaluarFecha(input, mensajeError = "Fecha inválida o formato incorrecto", opciones = {}) {
        const valor = input.value;
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        
        if (!regex.test(valor)) {
            EstadoInputs.marcarError(input, "El formato debe ser YYYY-MM-DD");
            return false;
        }
        
        const [anio, mes, dia] = valor.split('-').map(Number);
        const fechaIngresada = new Date(anio, mes - 1, dia);
        
        // Validar que la fecha exista en el calendario real (ej: que no sea 30 de febrero)
        if (fechaIngresada.getFullYear() !== anio || fechaIngresada.getMonth() !== mes - 1 || fechaIngresada.getDate() !== dia) {
            EstadoInputs.marcarError(input, mensajeError);
            return false;
        }
        
        // Validar año mínimo (Por defecto 1900, evita errores de tipeo como año "0202")
        const minAnio = opciones.minAnio !== undefined ? opciones.minAnio : 1900;
        if (anio < minAnio) {
            EstadoInputs.marcarError(input, `El año no puede ser menor a ${minAnio}`);
            return false;
        }

        // Validar que no sea una fecha en el futuro (Solo si maxHoy es true)
        if (opciones.maxHoy) {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0); // Ignorar la hora, evaluar solo el día
            
            if (fechaIngresada > hoy) {
                EstadoInputs.marcarError(input, "La fecha no puede estar en el futuro");
                return false;
            }
        }
        
        EstadoInputs.marcarExito(input);
        return true;
    },

    /**
     Consulta al servidor si un dato ya existe en la base de datos (Asíncrono).
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
    },
    /**
      Procesa los errores del Backend. Los campos visibles se marcan en rojo.
      Detecta dinámicamente si el error pertenece a un arreglo de detalles (ej. detalle_0_monto).
     */
    mostrarErroresBackend(errores) {
        let erroresGlobales = []; 

        for (const campo in errores) {
            if (errores.hasOwnProperty(campo)) {
                const primerMensaje = errores[campo][0];
                let input = null;
                let esErrorDetalle = false;

                // Verificamos si es un error de los detalles usando Regex
                // Busca el patrón: "detalle_" seguido de un número, "_" y el nombre del campo
                const coincidenciaDetalle = campo.match(/^detalle_(\d+)_(.+)$/);

                if (coincidenciaDetalle) {
                    esErrorDetalle = true;
                    const indiceFila = parseInt(coincidenciaDetalle[1], 10); // Ej: 0, 1, 2...
                    const nombreCampo = coincidenciaDetalle[2]; // Ej: "monto", "fecha_detalle"

                    // Intentamos buscar por el atributo name de PHP (ej: name="monto[]")
                    const inputsPorNombre = document.querySelectorAll(`[name="${nombreCampo}[]"]`);
                    if (inputsPorNombre.length > indiceFila) {
                        input = inputsPorNombre[indiceFila];
                    } else {
                        // Si no lo encuentra por name, lo buscamos por la clase (ej: .monto)
                        const inputsPorClase = document.querySelectorAll(`.${nombreCampo}`);
                        if (inputsPorClase.length > indiceFila) {
                            input = inputsPorClase[indiceFila];
                        }
                    }
                } else {
                    // Si NO es un detalle, lo buscamos normalmente por su ID (Cabecera)
                    input = document.getElementById(campo);
                }
                // Evaluamos si encontramos el input en el HTML y si está visible
                const esInputValido = input && input.nodeName !== 'SELECT' && input.type !== 'hidden';
                const esSelectValido = input && input.nodeName === 'SELECT' && !input.hidden;   
                if (esInputValido || esSelectValido) {
                    EstadoInputs.marcarError(input, primerMensaje);
                } else {
                    // Si el input no existe, está oculto (hidden), o es un ID interno, va a la alerta
                    let textoAlerta = primerMensaje;
                    
                    if (esErrorDetalle) {
                        const filaLogica = parseInt(coincidenciaDetalle[1], 10) + 1;
                        textoAlerta = `Renglón ${filaLogica}: ${primerMensaje}`;
                    }
                    
                    erroresGlobales.push(`• ${textoAlerta}`);
                }
            }
        }

        // Mostramos la alerta dinámica según los errores encontrados
        if (erroresGlobales.length > 0) {
            Alertas.mostrar('error', 'Error de Validación', erroresGlobales.join('<br>'), 6000);
        } else {
            Alertas.mostrar('error', 'Errores en el formulario', 'Por favor, revise los campos marcados en rojo.', 5000);
        }
    },

    /**
      Evalúa genéricamente la respuesta del servidor para no repetir código en AJAX.
     */
    procesarRespuesta(respuesta, accionExito) {
        if (!respuesta.estatus) {
            // Evaluamos de dónde viene el error
            if (respuesta.silencioso) return;
            else if (respuesta.errores) this.mostrarErroresBackend(respuesta.errores);
            else if (respuesta.mensaje) Alertas.mostrar('error', 'Error', respuesta.mensaje);
            else Alertas.mostrar('error', 'Error', 'Ocurrió un error inesperado al procesar la solicitud.');
            return false; // Detenemos la ejecución
        }

        // Si llegó aquí, la operación en el servidor fue un éxito
        if (respuesta.mensaje) {
            Alertas.mostrar('success', '¡Éxito!', respuesta.mensaje);
        }
        
        // Ejecutamos lo que sea que el módulo necesite hacer al tener éxito
        if (typeof accionExito === 'function') {
            accionExito(respuesta);
        }
        return true;
    },
    /**
     Consulta al servidor si un dato es ÚNICO (no debe existir en BD).
     para Cédulas, Correos y Referencias Bancarias.
     Retorna true si es ÚNICO (verde), false si YA EXISTE (rojo).
    */
    async verificarDatoUnico(accionBackend, datosExtra, input, mensajeError) {
        const formData = new FormData();
        formData.append('validar', accionBackend);
        
        for (const llave in datosExtra) {
            formData.append(llave, datosExtra[llave]);
        }

        const respuesta = await Peticiones.enviar(formData, "", false); 
        // Si 'existe' es true, entonces está ocupado y DA ERROR.
        if (respuesta.estatus === false || respuesta.existe === true) {
            EstadoInputs.marcarError(input, mensajeError);
            return false; // Es inválido porque YA EXISTE
        }
        
        EstadoInputs.marcarExito(input);
        return true; // Es válido porque está disponible
    },
};