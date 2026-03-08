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