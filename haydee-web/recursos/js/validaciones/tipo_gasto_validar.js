/**
 * tipo_gasto_validar.js
 * Dependencias: Validador.js, Patrones.js, Alertas.js
 */
document.addEventListener("DOMContentLoaded", function() {

    const inputNombre = document.querySelector("#nombre_tipo_gasto");

    // Validaciones en tiempo real
    inputNombre.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
    inputNombre.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.textoMedio, "Debe ingresar el nombre del tipo de gasto"));
    
    // Envío del formulario
    document.querySelector("#boton_formulario").addEventListener("click", async function(e) {
        e.preventDefault();
        const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";		
        
        if (await validarEnvio()) {
            Alertas.confirmarAccion(
                "Confirmar Operación",
                `¿Está seguro que desea ${accion.toLowerCase()} este tipo de gasto?`,
                "question", 
                () => {
                    envio(accion);
                }
            );
        }	
    });
});

async function validarEnvio() {	
    const inputNombre = document.querySelector("#nombre_tipo_gasto");
    const esValido = Validador.evaluarInput(inputNombre, Patrones.textoMedio, 'Debe ingresar el nombre del tipo de gasto');
    
    if (!esValido) {
        Alertas.mostrar('error', 'Atención', 'El nombre del tipo de gasto debe contener solo letras y formato correcto.');
        return false;
    }
    
    return true;
}