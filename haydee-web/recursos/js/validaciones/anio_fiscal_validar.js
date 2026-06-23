/**
 * anio_fiscal_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, FormatoFechas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const inputInicio = document.getElementById('fecha_inicio');
    const inputCierre = document.getElementById('fecha_cierre');
    const inputDesc = document.getElementById('descripcion');
    const inputEstado = document.getElementById('estado');

    // Fechas
    [inputInicio, inputCierre].forEach(input => {
        if (!input) return;
        input.addEventListener('keyup', function() { Validador.evaluarFecha(this); });
    });

    // Auto-calcular cierre
    if (inputInicio) {
        inputInicio.addEventListener('change', function() {
            if (Validador.evaluarFecha(this, '')) {
                const fechaBase = new Date(this.value + 'T00:00:00');
                fechaBase.setFullYear(fechaBase.getFullYear() + 1);
                
                // Formatear a YYYY-MM-DD para el input type="date"
                const nuevoAnio = fechaBase.getFullYear();
                const nuevoMes = String(fechaBase.getMonth() + 1).padStart(2, '0');
                const nuevoDia = String(fechaBase.getDate()).padStart(2, '0');
                
                inputCierre.value = `${nuevoAnio}-${nuevoMes}-${nuevoDia}`;
                EstadoInputs.limpiar(inputCierre);
            }
        });
    }

    // Descripción y Estado
    if (inputDesc) {
        inputDesc.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasObservacion));
        inputDesc.addEventListener('keyup', function() {
            Validador.evaluarInput(this, Patrones.observacion, 'Máximo 50 caracteres');
        });
    }

    // if (selectEstado) {
    //     selectEstado.addEventListener('change', function() {
    //         Validador.evaluarInput(this, Patrones.estadoAnio, 'El estado debe tener entre 3 y 15 letras');
    //     });
    // }

    // Envío
    const btnForm = document.getElementById('boton_formulario');
    if (btnForm) {
        btnForm.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.id ? 'modificar' : 'Registrar';
            
            if (await validarEnvio(accion)) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este año fiscal?`,
                    "question", 
                    () => {
                        accion === 'modificar' ? modificar() : registrar();
                    }
                );
            }
        });
    }
});

async function validarEnvio(accion) {
    const inicio = document.getElementById('fecha_inicio');
    const cierre = document.getElementById('fecha_cierre');
    const estado = document.getElementById('estado');
    const desc = document.getElementById('descripcion');

    if (!Validador.evaluarFecha(inicio) || !Validador.evaluarFecha(cierre)) {
        Alertas.mostrar('error', 'Error', 'Verifique los campos obligatorios');
        return false;
    }

    if (!await validarRangoFechas()) return false;

    if (desc.value && !Validador.evaluarInput(desc, Patrones.observacion, 'Inválido')) {
        Alertas.mostrar('error', 'Error', 'La descripción contiene caracteres no permitidos.');
        return false;
    }

    return true;
}

async function validarRangoFechas() {
    const inicio = document.getElementById('fecha_inicio');
    const cierre = document.getElementById('fecha_cierre');

    const fechaInicio = new Date(inicio.value);
    const fechaCierre = new Date(cierre.value);

    if (fechaInicio >= fechaCierre) {
        EstadoInputs.marcarError(inicio, 'Debe ser anterior a la de cierre');
        EstadoInputs.marcarError(cierre, 'Debe ser posterior a la de inicio');
        Alertas.mostrar('error', 'Error', 'La fecha de inicio debe ser anterior a la de cierre.');
        return false;
    }
    
    // Usamos tu excelente helper FormatoFechas
    const diferencia = FormatoFechas.diferenciaEnDias(inicio.value, cierre.value);
    if (diferencia < 364 || diferencia > 366) {
        EstadoInputs.marcarError(inicio, `Período inválido. Días: ${diferencia}`);
        EstadoInputs.marcarError(cierre, `Período inválido. Días: ${diferencia}`);
        Alertas.mostrar('error', 'Error', `El período debe ser de un año (364-366 días). Días calculados: ${diferencia}.`);
        return false;
    }

    EstadoInputs.limpiar(inicio);
    EstadoInputs.limpiar(cierre);
    return true;
}