/**
 * modulos_validar.js
 */
document.addEventListener("DOMContentLoaded", function() {
    const inputNombre = document.getElementById('nombre');
    if (inputNombre) {
        inputNombre.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.textoCorto, 'Mínimo 3 letras'));
    }
});


async function validarFormulario() {
    const inputNombre = document.getElementById('nombre');
    if (!Validador.evaluarInput(inputNombre, Patrones.textoModulo, 'Mínimo 3 letras')) {
        Alertas.mostrar('error', 'Error', 'El nombre del módulo no es válido');
        return false;
    }
    return true;
}

document.getElementById("boton_formulario")?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = document.getElementById("boton_formulario").hasAttribute('modificar');
    const accion = esEdicion ? 'modificar' : 'Registrar';

    if (await validarFormulario()) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Desea ${accion.toLowerCase()} este módulo?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1b8a40',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                if (esEdicion) {
                    modificar(document.getElementById("boton_formulario").getAttribute('id_modificar'));
                } else {
                    registrar();
                }
            }
        });
    }
});