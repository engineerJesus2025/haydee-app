/**
 * permisos_validar.js
 */
document.addEventListener("DOMContentLoaded", function() {
    const inputAccion = document.getElementById('accion');
    if (inputAccion) {
        inputAccion.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasAccion));
        inputAccion.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.accionPermiso, 'Mínimo 3 caracteres, solo letras y guión bajo'));
    }
});

async function validarFormulario() {
    const accion = document.getElementById('accion');
    if (!Validador.evaluarInput(accion, Patrones.accionPermiso, 'Mínimo 3 caracteres, solo letras y guión bajo')) {
        Alertas.mostrar('error', 'Error', 'El nombre de la acción no es válido');
        return false;
    }
    return true;
}

document.getElementById("boton_formulario")?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = document.getElementById("boton_formulario").hasAttribute('modificar');
    const accion = esEdicion ? 'modificar' : 'Registrar';

    if (await validarFormulario()) {
        Alertas.confirmarAccion(
            "Confirmar Operación",
            `¿Está seguro que desea ${accion.toLowerCase()} este permiso?`,
            "question", 
            () => {
                if (esEdicion) {
                    modificar(document.getElementById("boton_formulario").getAttribute('id_modificar'));
                } else {
                    registrar();
                }
            }
        );
    }
});