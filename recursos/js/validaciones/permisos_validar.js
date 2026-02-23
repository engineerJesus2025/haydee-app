/**
 * permisos_validar.js
 * Validaciones en tiempo real para Permisos
 */

$(document).ready(function() {
    $('#accion').on('keypress', function(e) {
        Validaciones.keyPress(/^[A-Za-z_]$/, e);
    });
    $('#accion').on('keyup', function() {
        Validaciones.keyUp(/^[A-Za-z_]{3,50}$/, this, this.nextElementSibling, 'Mínimo 3 caracteres, solo letras y guión bajo');
    });
});