/**
 * anio_fiscal_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, FormatoFechas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const inputInicio = document.getElementById('fecha_inicio');
    const inputCierre = document.getElementById('fecha_cierre');
    const inputDesc = document.getElementById('descripcion');
    const selectEstado = document.getElementById('estado');

    // Fechas
    [inputInicio, inputCierre].forEach(input => {
        if (!input) return;
        input.addEventListener('keyup', function() { Validador.evaluarFecha(this); });
    });

    // Auto-calcular cierre
    if (inputInicio) {
        inputInicio.addEventListener('change', function() {
            if (Validador.evaluarFecha(this, '')) {
                const [anio, mes, dia] = this.value.split('-');
                const nuevoAnio = parseInt(anio) + 1;
                inputCierre.value = `${nuevoAnio}-${mes}-${dia}`;
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

    if (selectEstado) {
        selectEstado.addEventListener('change', function() {
            Validador.evaluarInput(this, Patrones.estadoAnio, 'El estado debe tener entre 3 y 15 letras');
        });
    }

    // Envío
    const btnForm = document.getElementById('boton_formulario');
    if (btnForm) {
        btnForm.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.id ? 'modificar' : 'Registrar';
            
            if (await validarEnvio(accion)) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Desea ${accion} este año fiscal?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1b8a40',
                    confirmButtonText: 'Sí, ' + accion
                }).then(result => {
                    if (result.isConfirmed) {
                        accion === 'modificar' ? modificar() : registrar();
                    }
                });
            }
        });
    }
});

async function validarEnvio(accion) {
    const inicio = document.getElementById('fecha_inicio');
    const cierre = document.getElementById('fecha_cierre');
    const estado = document.getElementById('estado');
    const desc = document.getElementById('descripcion');

    if (!Validador.evaluarFecha(inicio) || !Validador.evaluarFecha(cierre) || !Validador.evaluarInput(estado, Patrones.estadoAnio, 'Estado inválido')) {
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