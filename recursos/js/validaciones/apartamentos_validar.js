// Variables globales para comparar valores originales
let nro_apartamento_anterior = null;
let cedula_an = null;
let correo_an = null;
let tipo_vinculo_an = null;

$(document).ready(function() {
    // ============================================
    // VALIDACIONES DE APARTAMENTOS
    // ============================================
    
    // Número de apartamento
    $('#nro_apartamento').on('keypress', e => Validaciones.keyPress(/^[0-9-]$/, e));
    $('#nro_apartamento').on('keyup', function() {
        Validaciones.campo(this, /^[0-9-]{1,3}$/, 'Número de apartamento inválido (máx 3 dígitos).');
    });
    $('#nro_apartamento').on('blur', async function() {
        if ($(this).val() === nro_apartamento_anterior) return;
        if (!Validaciones.campo(this, /^[0-9-]{1,3}$/, '')) return;
        const valido = await Validaciones.verificarExistencia(
            'nro_apartamento',
            { nro_apartamento: this.value },
            this,
            'Este número ya está registrado.'
        );
        if (valido) this.classList.add('is-valid');
    });

    // Porcentaje de participación
    $('#porcentaje_participacion').on('keypress', e => Validaciones.keyPress(/^[0-9.]$/, e));
    $('#porcentaje_participacion').on('keyup', function() {
        Validaciones.campo(this, /^\d{1,2}(\.\d{1,2})?$/, 'Porcentaje inválido (ej. 12.5).');
    });

    // Selects (gas, agua, alquilado)
    $('#gas, #agua, #alquilado').on('change', function() {
        Validaciones.campo(this, /^[01]$/, 'Seleccione una opción válida.');
    });

    // ============================================
    // VALIDACIONES DE HABITANTES
    // ============================================

    const campos = [
        { selector: '#nombre', regex: /^[A-Za-záéíóúñÑ\s]{3,30}$/, mensaje: 'Solo letras, entre 3 y 30 caracteres.' },
        { selector: '#apellido', regex: /^[A-Za-záéíóúñÑ\s]{3,30}$/, mensaje: 'Solo letras, entre 3 y 30 caracteres.' },
        { selector: '#cedula', regex: /^[0-9]{7,8}$/, mensaje: 'Cédula inválida (7-8 dígitos).' },
        { selector: '#telefono', regex: /^\d{11}$/, mensaje: 'Teléfono inválido (11 dígitos).' },
        { selector: '#correo', regex: /^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, mensaje: 'Correo inválido.' },
        { selector: '#fecha_nacimiento', regex: /^\d{4}-\d{2}-\d{2}$/, mensaje: 'Fecha inválida (YYYY-MM-DD).' }
    ];

    campos.forEach(c => {
        $(c.selector).on('keypress', e => Validaciones.keyPress(/^[A-Za-z0-9áéíóúñÑ@._-]$/i, e));
        $(c.selector).on('keyup', function() {
            Validaciones.campo(this, c.regex, c.mensaje);
        });
    });

    // Tipo de cédula
    $('#tipo_cedula').on('change', function() {
        if (!this.value) {
            Validaciones.mostrarError(this, 'Seleccione un tipo de cédula');
            $('#cedula').prop('disabled', true);
        } else {
            Validaciones.limpiar(this);
            $('#cedula').prop('disabled', false);
            $('#cedula').val('');
        }
    });

    // Sexo, apartamento, tipo de vínculo (solo limpiar)
    $('#sexo, #apartamento_id, #tipo_vinculo').on('change', function() {
        Validaciones.limpiar(this);
    });

    // Validación de duplicados en tiempo real
    $('#cedula').on('blur', async function() {
        if (!Validaciones.campo(this, /^[0-9]{7,8}$/, '')) return;
        const cedulaCompleta = $('#tipo_cedula').val() + this.value;
        await Validaciones.verificarExistencia('cedula', { cedula: cedulaCompleta }, this, 'Esta cédula ya está registrada.');
    });

    $('#correo').on('blur', async function() {
        if (!Validaciones.campo(this, /^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, '')) return;
        await Validaciones.verificarExistencia('correo', { correo: this.value }, this, 'Este correo ya está registrado.');
    });

    $('#tipo_vinculo').on('change', async function() {
        if (!this.value) return Validaciones.mostrarError(this, 'Seleccione un tipo de vínculo');
        const respuesta = await Utilidades.validar('tipo_vinculo', {
            tipo_vinculo: this.value,
            apartamento_id: $('#apartamento_id').val()
        });
        if (respuesta.existe) {
            Validaciones.mostrarError(this, 'Este apartamento ya tiene un propietario.');
        } else {
            Validaciones.limpiar(this);
        }
    });

    // Validación de clave foránea (apartamento)
    $('#apartamento_id').on('change', async function() {
        if (!this.value) return Validaciones.mostrarError(this, 'Seleccione un apartamento');
        const datos = new FormData();
        datos.append('validar', 'validar_clave_foranea');
        datos.append('tabla', 'apartamentos');
        datos.append('nombre_clave', 'id_apartamento');
        datos.append('valor', this.value);
        const respuesta = await Utilidades.query(datos);
        if (!respuesta.estatus) {
            Validaciones.mostrarError(this, 'El apartamento seleccionado no existe.');
        } else {
            Validaciones.limpiar(this);
        }
    });

    // ============================================
    // VALIDACIÓN DE ENVÍO DE APARTAMENTOS
    // ============================================
    $('#boton_formulario').on('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'modificar' : 'Registrar';
        if (await validarEnvioApartamento(accion)) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Desea ${accion} este apartamento?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1b8a40',
                confirmButtonText: 'Sí, ' + accion
            }).then(result => {
                if (result.isConfirmed) {
                    accion === 'modificar' ? modificarApartamento() : registrarApartamento();
                }
            });
        }
    });

    // ============================================
    // VALIDACIÓN DE ENVÍO DE HABITANTES
    // ============================================
    $('#boton_formulario_habitantes').on('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'modificar' : 'Registrar';
        if (await validarEnvioHabitante(accion)) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Desea ${accion} este habitante?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1b8a40',
                confirmButtonText: 'Sí, ' + accion
            }).then(result => {
                if (result.isConfirmed) {
                    accion === 'modificar' ? modificarHabitante() : registrarHabitante();
                }
            });
        }
    });
});

// ============================================
// FUNCIÓN DE VALIDACIÓN DE ENVÍO DE APARTAMENTOS
// ============================================
async function validarEnvioApartamento(accion) {
    const campos = [
        { input: '#nro_apartamento', regex: /^[0-9-]{1,3}$/, msg: 'Número inválido' },
        { input: '#porcentaje_participacion', regex: /^\d{1,2}(\.\d{1,2})?$/, msg: 'Porcentaje inválido' },
        { input: '#gas', regex: /^[01]$/, msg: 'Seleccione una opción' },
        { input: '#agua', regex: /^[01]$/, msg: 'Seleccione una opción' },
        { input: '#alquilado', regex: /^[01]$/, msg: 'Seleccione una opción' }
    ];

    for (const c of campos) {
        const el = document.querySelector(c.input);
        if (!Validaciones.campo(el, c.regex, c.msg)) {
            Utilidades.mensaje('error', 'Error', c.msg);
            return false;
        }
    }

    // Verificar duplicado de número si cambió
    if (nro_apartamento_anterior !== $('#nro_apartamento').val()) {
        const datos = new FormData();
        datos.append('validar', 'nro_apartamento');
        datos.append('nro_apartamento', $('#nro_apartamento').val());

        const respuesta = await Utilidades.query(datos);
        if (respuesta?.estatus && respuesta.existe) {
            Validaciones.mostrarError($('#nro_apartamento')[0], 'Este número ya está registrado.');
            Utilidades.mensaje('error', 'Error', 'Número de apartamento ya registrado.');
            return false;
        }
    }

    return true;
}

// ============================================
// FUNCIÓN DE VALIDACIÓN DE ENVÍO DE HABITANTES
// ============================================
async function validarEnvioHabitante(accion) {
    const campos = [
        { input: '#tipo_cedula', msg: 'Seleccione tipo de cédula' },
        { input: '#cedula', regex: /^[0-9]{7,8}$/, msg: 'Cédula inválida' },
        { input: '#nombre', regex: /^[A-Za-záéíóúñÑ\s]{3,30}$/, msg: 'Nombre inválido' },
        { input: '#apellido', regex: /^[A-Za-záéíóúñÑ\s]{3,30}$/, msg: 'Apellido inválido' },
        { input: '#fecha_nacimiento', regex: /^\d{4}-\d{2}-\d{2}$/, msg: 'Fecha inválida' },
        { input: '#telefono', regex: /^\d{11}$/, msg: 'Teléfono inválido' },
        { input: '#correo', regex: /^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/, msg: 'Correo inválido' },
        { input: '#sexo', msg: 'Seleccione sexo' },
        { input: '#apartamento_id', msg: 'Seleccione apartamento' },
        { input: '#tipo_vinculo', msg: 'Seleccione tipo de vínculo' }
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

    // Validar duplicados de cédula si cambió (usando cédula completa)
    const cedulaCompletaActual = $('#tipo_cedula').val() + $('#cedula').val();
    if (accion === 'Registrar' || cedulaCompletaActual !== cedula_an) {
        const valido = await Validaciones.verificarExistencia(
            'cedula',
            { cedula: cedulaCompletaActual },
            $('#cedula')[0],
            'Cédula duplicada'
        );
        if (!valido) {
            Utilidades.mensaje('error', 'Error', "El valor del campo 'cedula' ya está registrado.");
            return false;
        }
    }

    // Validar duplicados de correo si cambió
    if (accion === 'Registrar' || $('#correo').val() !== correo_an) {
        const valido = await Validaciones.verificarExistencia(
            'correo',
            { correo: $('#correo').val() },
            $('#correo')[0],
            'Correo duplicado'
        );
        if (!valido) {
            Utilidades.mensaje('error', 'Error', "El valor del campo 'correo' ya está registrado.");
            return false;
        }
    }

    // Validar tipo de vínculo (solo un propietario por apartamento)
    if (accion === 'Registrar' || $('#tipo_vinculo').val() !== tipo_vinculo_an) {
        const respuesta = await Utilidades.validar('tipo_vinculo', {
            tipo_vinculo: $('#tipo_vinculo').val(),
            apartamento_id: $('#apartamento_id').val()
        });
        if (respuesta.existe) {
            Validaciones.mostrarError($('#tipo_vinculo')[0], 'Este apartamento ya tiene propietario');
            Utilidades.mensaje('error', 'Error', 'Este apartamento ya tiene un propietario asignado.');
            return false;
        }
    }

    // Validar que el apartamento exista (clave foránea)
    const datosApartamento = new FormData();
    datosApartamento.append('validar', 'validar_clave_foranea');
    datosApartamento.append('tabla', 'apartamentos');
    datosApartamento.append('nombre_clave', 'id_apartamento');
    datosApartamento.append('valor', $('#apartamento_id').val());
    const respApartamento = await Utilidades.query(datosApartamento);
    if (!respApartamento.estatus) {
        Validaciones.mostrarError($('#apartamento_id')[0], 'Apartamento no existe');
        Utilidades.mensaje('error', 'Error', 'El apartamento seleccionado no existe.');
        return false;
    }

    // Validar edad (mayor de 18)
    const edad = FormatoFechas.calcularEdad($('#fecha_nacimiento').val());
    if (edad < 18) {
        Utilidades.mensaje('error', 'Error', 'Debe ser mayor de 18 años.');
        return false;
    }

    return true;
}