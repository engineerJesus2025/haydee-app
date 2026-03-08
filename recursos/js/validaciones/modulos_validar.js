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