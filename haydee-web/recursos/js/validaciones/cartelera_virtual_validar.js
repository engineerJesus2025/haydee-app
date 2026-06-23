/**
 * cartelera_virtual_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {

    const inputTitulo = document.getElementById("titulo");
    const inputDesc = document.getElementById("descripcion");
    const selectPrioridad = document.getElementById("prioridad");

    // Validaciones en tiempo real
    if (inputTitulo) {
        inputTitulo.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCartelera));
        inputTitulo.addEventListener("keyup", function() {
            Validador.evaluarInput(this, Patrones.tituloCartelera, "Entre 3 y 100 caracteres permitidos");
        });
    }

    if (inputDesc) {
        inputDesc.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCartelera));
        inputDesc.addEventListener("keyup", function() {
            Validador.evaluarInput(this, Patrones.descripcionCartelera, "Entre 3 y 200 caracteres permitidos");
        });
    }

    if (selectPrioridad) {
        selectPrioridad.addEventListener("change", function() { Validador.evaluarSelect(this.id); });
    }

    // Envío del formulario
    const btnFormulario = document.getElementById("boton_formulario");
    if (btnFormulario) {
        btnFormulario.addEventListener("click", async function(e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarEnvio(accion)) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} esta publicación?`,
                    "question", 
                    () => {
                        envio(accion);
                    }
                );
            }
        });
    }
});

async function validarEnvio(accion) {
    const titulo = document.getElementById("titulo");
    const descripcion = document.getElementById("descripcion");

    if (!Validador.evaluarInput(titulo, Patrones.tituloCartelera, 'Entre 3 y 100 caracteres')) {
        Alertas.mostrar('error', 'Error', 'El título debe tener entre 3 y 100 caracteres.');
        return false;
    }

    if (!Validador.evaluarInput(descripcion, Patrones.descripcionCartelera, 'Entre 3 y 200 caracteres')) {
        Alertas.mostrar('error', 'Error', 'La descripción debe tener entre 3 y 200 caracteres.');
        return false;
    }

    if (!Validador.evaluarSelect('prioridad')) {
        Alertas.mostrar('error', 'Error', 'Debe seleccionar una prioridad.');
        return false;
    }

    return true;
}