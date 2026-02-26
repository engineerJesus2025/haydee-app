$(document).ready(function() {
    const campos = [
        { 
            selector: '#nombre_proveedor', 
            regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ() ]{3,50}$/, 
            mensaje: 'Solo letras, entre 3 y 50 caracteres.',
            keyPressRegex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ() ]$/i
        },
        { 
            selector: '#servicio', 
            regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,30}$/, 
            mensaje: 'Solo letras, entre 3 y 30 caracteres.',
            keyPressRegex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]$/i
        },
        { 
            selector: '#direccion', 
            regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9,.\-#° ]{3,100}$/, 
            mensaje: 'Dirección inválida.',
            keyPressRegex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9,.\-#° ]$/i
        }
    ];

    campos.forEach(c => {
        $(c.selector).on('keypress', e => Validaciones.keyPress(c.keyPressRegex, e));
        $(c.selector).on('keyup', function() {
            Validaciones.campo(this, c.regex, c.mensaje);
        });
    });

    $('#rif').on('keypress', e => {
        if (!/[0-9]/.test(String.fromCharCode(e.which))) e.preventDefault();
    });

    $('#rif').on('keyup', function() {
        Validaciones.campo(this, /^[0-9]{7,9}$/, 'Debe tener entre 7 y 9 dígitos.');
    });

    $('#tipo_documento').on('change', function() {
        const valido = Validaciones.campo(this, /^[VEJG]$/, 'Tipo de documento inválido');
        if (valido) {
            $('#rif').val('').focus();
            document.getElementById('rif').removeAttribute('disabled')
            $('#rif').removeClass('is-invalid is-valid');
        }
    });

    $('#boton_formulario').on('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'modificar' : 'Registrar';
        if (await validarEnvio(accion)) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Desea ${accion} este proveedor?`,
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
    const campos = [
        { input: '#nombre_proveedor', regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ() ]{3,50}$/, msg: 'Nombre inválido' },
        { input: '#servicio', regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,30}$/, msg: 'Servicio inválido' },
        { input: '#tipo_documento', regex: /^[VEJG]$/, msg: 'Seleccione tipo de documento' },
        { input: '#rif', regex: /^[0-9]{7,9}$/, msg: 'RIF inválido (7-9 dígitos)' },
        { input: '#direccion', regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9,.\-#° ]{3,100}$/, msg: 'Dirección inválida' }
    ];

    for (const c of campos) {
        const el = document.querySelector(c.input);
        if (!Validaciones.campo(el, c.regex, c.msg)) {
            Utilidades.mensaje('error', 'Error', c.msg);
            return false;
        }
    }
    return true;
}