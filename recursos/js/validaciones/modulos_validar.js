/**
 * modulos_validar.js
 * Validaciones en tiempo real para Módulos
 */

$(document).ready(function() {
    $('#nombre').on('keypress', function(e) {
        Validaciones.keyPress(/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]$/, e);
    });
    $('#nombre').on('keyup', function() {
        Validaciones.keyUp(/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{3,50}$/, this, this.nextElementSibling, 'Mínimo 3 letras');
    });
});