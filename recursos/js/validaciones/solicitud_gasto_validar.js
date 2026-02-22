$(document).ready(function() {
    const campos = [
        { 
            selector: '#monto_estimado', 
            regex: /^\d+([.,]\d{1,2})?$/, 
            mensaje: 'Monto inválido',
            keyPressRegex: /^[0-9.,]$/ 
        },
        { 
            selector: '#descripcion', 
            // Permite letras (con acentos), números y espacios
            regex: /^[A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s]{3,60}$/, 
            mensaje: 'Descripción inválida',
            keyPressRegex: /^[A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s]$/i 
        },
        { 
            selector: '#nombre', 
            // Solo letras (con acentos) y espacios
            regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,40}$/, 
            mensaje: 'Nombre inválido',
            keyPressRegex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]$/i 
        }
    ];

    // Validaciones en tiempo real con keyPress específico
    campos.forEach(c => {
        $(c.selector).on('keypress', e => Validaciones.keyPress(c.keyPressRegex, e));
        $(c.selector).on('keyup', function() {
            Validaciones.campo(this, c.regex, c.mensaje);
        });
    });

    $('#fecha').on('change', function() {
        Validaciones.limpiar(this);
        this.classList.add('is-valid');
    });

    $('#prioridad').on('change', function() {
        Validaciones.campo(this, /^[1-3]$/, 'Seleccione una prioridad');
    });

    // Validación de mes y año con existencia en BD
    $('#selector_mes').on('change', async function() {
        if (!this.value) return Validaciones.mostrarError(this, 'Seleccione un mes');
        const valido = await Validaciones.verificarExistencia(
            'validar_mes',
            { fecha: this.value },
            this,
            'El mes no tiene presupuesto'
        );
        if (valido) buscarPresupuesto();
    });

    $('#selector_anio').on('change', async function() {
        if (!this.value) return Validaciones.mostrarError(this, 'Seleccione un año');
        const valido = await Validaciones.verificarExistencia(
            'validar_anio',
            { fecha: this.value },
            this,
            'El año no tiene presupuesto'
        );
        if (valido) buscarPresupuesto();
    });

    // Validación de envío
    $('#boton_formulario').on('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'Editar' : 'Registrar';
        if (await validarEnvio(accion)) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Desea ${accion} esta solicitud?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1b8a40',
                confirmButtonText: 'Sí, ' + accion
            }).then(result => {
                if (result.isConfirmed) {
                    accion === 'Editar' ? modificar() : registrar();
                }
            });
        }
    });
});

async function validarEnvio(accion) {
    // Validar campos individuales
    const campos = [
        { input: '#selector_mes', regex: /^\d+$/, msg: 'Seleccione mes' },
        { input: '#selector_anio', regex: /^\d+$/, msg: 'Seleccione año' },
        { input: '#fecha', msg: 'Seleccione fecha' },
        { input: '#nombre', regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,40}$/, msg: 'Nombre inválido' },
        { input: '#descripcion', regex: /^[A-Za-z0-9\s]{3,60}$/, msg: 'Descripción inválida' },
        { input: '#monto_estimado', regex: /^\d+([.,]\d{1,2})?$/, msg: 'Monto inválido' },
        { input: '#prioridad', regex: /^[1-3]$/, msg: 'Seleccione prioridad' }
    ];

    for (const c of campos) {
        const el = document.querySelector(c.input);
        if (!el.value || (c.regex && !c.regex.test(el.value))) {
            Validaciones.mostrarError(el, c.msg);
            Utilidades.mensaje('error', 'Error', c.msg);
            return false;
        }
        Validaciones.limpiar(el);
    }

    // Verificar existencia de mes y año en BD (nuevamente por si acaso)
    const mesValido = await Validaciones.verificarExistencia(
        'validar_mes',
        { fecha: $('#selector_mes').val() },
        $('#selector_mes')[0],
        'El mes no tiene presupuesto'
    );
    if (!mesValido) return false;

    const anioValido = await Validaciones.verificarExistencia(
        'validar_anio',
        { fecha: $('#selector_anio').val() },
        $('#selector_anio')[0],
        'El año no tiene presupuesto'
    );
    if (!anioValido) return false;

    // Validar presupuesto disponible
    const monto = parseFloat($('#monto_estimado').val().replace(',', '.'));
    const disponible = parseFloat($('#presupuesto_disponible').text().replace('Bs. ', '').replace(',', ''));
    const montoOriginal = parseFloat($('#monto_estimado').data('original') || 0);
    const disponibleReal = accion === 'Editar' ? disponible + montoOriginal : disponible;

    if (isNaN(disponibleReal) || isNaN(monto)) {
        Utilidades.mensaje('error', 'Error', 'No se pudo verificar el presupuesto.');
        return false;
    }
    if (monto > disponibleReal) {
        Utilidades.mensaje('error', 'Monto excedido', `El monto supera el disponible (Bs. ${disponibleReal.toFixed(2)}).`);
        return false;
    }

    return true;
}