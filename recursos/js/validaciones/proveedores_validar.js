/**
 * proveedores_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Peticiones.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const inputNombre = document.querySelector('#nombre_proveedor');
    const inputServicio = document.querySelector('#servicio');
    const inputDireccion = document.querySelector('#direccion');
    const inputRif = document.querySelector('#rif');
    const selectDocumento = document.querySelector('#tipo_documento');

    // Validaciones en tiempo real (keypress y keyup)
    inputNombre.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
    inputNombre.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.textoMedio, 'Solo letras, entre 3 y 50 caracteres.'));

    inputServicio.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
    inputServicio.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.textoCorto, 'Solo letras, entre 3 y 30 caracteres.'));

    inputDireccion.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasDireccion));
    inputDireccion.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.direccion, 'Dirección inválida.'));

    inputRif.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
    inputRif.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.rif, 'Debe tener entre 7 y 9 dígitos.'));

    // Lógica del Tipo de Documento
    selectDocumento.addEventListener('change', function() {
        const valido = Validador.evaluarInput(this, Patrones.tipoDocumento, 'Tipo de documento inválido');
        if (valido) {
            inputRif.value = '';
            inputRif.removeAttribute('disabled');
            EstadoInputs.limpiar(inputRif);
            inputRif.focus();
        }
    });

    // Envío de formulario
    document.querySelector('#boton_formulario').addEventListener('click', async function(e) {
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
    const vNombre = Validador.evaluarInput(document.querySelector('#nombre_proveedor'), Patrones.textoMedio, 'Nombre inválido');
    const vServicio = Validador.evaluarInput(document.querySelector('#servicio'), Patrones.textoCorto, 'Servicio inválido');
    const vDocumento = Validador.evaluarInput(document.querySelector('#tipo_documento'), Patrones.tipoDocumento, 'Seleccione tipo');
    const vRif = Validador.evaluarInput(document.querySelector('#rif'), Patrones.rif, 'RIF inválido (7-9 dígitos)');
    const vDireccion = Validador.evaluarInput(document.querySelector('#direccion'), Patrones.direccion, 'Dirección inválida');

    if (!vNombre || !vServicio || !vDocumento || !vRif || !vDireccion) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    return true;
}