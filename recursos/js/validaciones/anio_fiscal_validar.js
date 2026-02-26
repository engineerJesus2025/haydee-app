$(document).ready(function() {
    // Validaciones en tiempo real
    $('#fecha_inicio, #fecha_cierre').on('keyup', function() {
        Validaciones.campo(this, /^\d{4}-\d{2}-\d{2}$/, 'Debe ingresar una fecha válida (YYYY-MM-DD)');
    });

    $('#descripcion').on('keypress', e => Validaciones.keyPress(/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]$/, e));
    $('#descripcion').on('keyup', function() {
        Validaciones.campo(this, /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/, 'Máximo 50 caracteres, solo letras, números y puntuación básica');
    });

    $('#estado').on('change', function() {
        Validaciones.campo(this, /^[A-Za-z]{3,15}$/, 'El estado debe tener entre 3 y 15 letras');
    });

    // Auto-calcular fecha de cierre al cambiar fecha de inicio
    $('#fecha_inicio').on('change', function() {
        if (Validaciones.campo(this, /^\d{4}-\d{2}-\d{2}$/, '')) {
            const fecha = new Date(this.value);
            fecha.setFullYear(fecha.getFullYear() + 1);
            $('#fecha_cierre').val(fecha.toISOString().split('T')[0]);
            Validaciones.limpiar($('#fecha_cierre')[0]);
        }
    });

    // Validación de envío
    $('#boton_formulario').on('click', async function(e) {
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
});

async function validarEnvio(accion) {
    // Validar campos obligatorios
    const campos = [
        { input: '#fecha_inicio', regex: /^\d{4}-\d{2}-\d{2}$/, msg: 'Fecha de inicio inválida' },
        { input: '#fecha_cierre', regex: /^\d{4}-\d{2}-\d{2}$/, msg: 'Fecha de cierre inválida' },
        { input: '#estado', regex: /^[A-Za-z]{3,15}$/, msg: 'Estado inválido' }
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

    // Validar lógica de fechas (rango de un año)
    if (!await validarRangoFechas()) return false;

    // Descripción es opcional, pero si tiene valor validar formato
    const desc = document.querySelector('#descripcion');
    if (desc.value && !/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/.test(desc.value)) {
        Validaciones.mostrarError(desc, 'Descripción inválida');
        Utilidades.mensaje('error', 'Error', 'La descripción contiene caracteres no permitidos.');
        return false;
    }

    return true;
}

async function validarRangoFechas() {
    const inicio = document.querySelector('#fecha_inicio');
    const cierre = document.querySelector('#fecha_cierre');

    const fechaInicio = new Date(inicio.value);
    const fechaCierre = new Date(cierre.value);

    if (fechaInicio >= fechaCierre) {
        Validaciones.mostrarError(inicio, 'La fecha de inicio debe ser anterior a la de cierre');
        Validaciones.mostrarError(cierre, 'La fecha de cierre debe ser posterior a la de inicio');
        Utilidades.mensaje('error', 'Error', 'La fecha de inicio debe ser anterior a la de cierre.');
        return false;
    }

    const diferencia = Math.round((fechaCierre - fechaInicio) / (1000 * 60 * 60 * 24));
    if (diferencia < 364 || diferencia > 366) {
        Validaciones.mostrarError(inicio, `El período debe ser de aproximadamente un año (364-366 días). Días actuales: ${diferencia}`);
        Validaciones.mostrarError(cierre, `El período debe ser de aproximadamente un año (364-366 días). Días actuales: ${diferencia}`);
        Utilidades.mensaje('error', 'Error', `El período debe ser de un año (364-366 días). Días calculados: ${diferencia}.`);
        return false;
    }

    Validaciones.limpiar(inicio);
    Validaciones.limpiar(cierre);
    return true;
}