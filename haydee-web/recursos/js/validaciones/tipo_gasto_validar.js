document.addEventListener("DOMContentLoaded", function() {

    const inputNombre = document.querySelector("#nombre_tipo_gasto");
    const formulario = document.getElementById("form_tipo_gasto");

    // Validaciones en tiempo real para la Partida Principal
    if (inputNombre) {
        inputNombre.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.textoMedio, "Debe ingresar el nombre del tipo de gasto"));
    }
    
    if (formulario) {
        const selectorConceptos = "input[name='nombre_concepto[]']";

        formulario.addEventListener("keypress", function(e) {
            if (e.target.matches(selectorConceptos)) {
                Validador.bloquearTeclasInvalidas(e, Patrones.teclasAlfanumerico);
            }
        });

        formulario.addEventListener("keyup", function(e) {
            if (e.target.matches(selectorConceptos)) {
                Validador.evaluarInput(e.target, Patrones.textoAlfanumerico, "Ingrese un nombre de concepto válido");
            }
        });
    }

    document.querySelector("#boton_formulario").addEventListener("click", async function(e) {
        e.preventDefault();
        const actionButton = this;
        const accion = actionButton.hasAttribute("modificar") ? "modificar" : "Registrar";      
        
        if (await validarEnvio()) {
            Alertas.confirmarAccion(
                "Confirmar Operación",
                `¿Está seguro que desea ${accion.toLowerCase()} esta estructura de gastos?`,
                "question", 
                () => {
                    if (accion === "Registrar" && document.getElementById('contenedor_conceptos').childElementCount === 0) {
                        agregarFilaConcepto();
                    }
                    envio(accion);
                }
            );
        }   
    });
});

async function validarEnvio() { 
    const inputNombre = document.querySelector("#nombre_tipo_gasto");
    
    // Validar cabecera
    let formularioValido = Validador.evaluarInput(inputNombre, Patrones.textoMedio, 'Debe ingresar el nombre del tipo de gasto');
    
    if (!formularioValido) {
        Alertas.mostrar('error', 'Atención', 'El nombre de la partida principal es obligatorio y debe tener formato correcto.');
        return false;
    }

    // Validar cada uno de los renglones (Conceptos)
    const inputsConceptos = document.querySelectorAll("input[name='nombre_concepto[]']");
    
    if (inputsConceptos.length === 0) {
        Alertas.mostrar('error', 'Atención', 'Debe añadir al menos un concepto a la partida antes de guardar.');
        return false;
    }

    inputsConceptos.forEach(input => {
        const renglonValido = Validador.evaluarInput(input, Patrones.textoAlfanumerico, 'Ingrese un concepto válido (mínimo 3 caracteres)');
        if (!renglonValido) {
            formularioValido = false;
        }
    });

    if (!formularioValido) {
        Alertas.mostrar('error', 'Atención', 'Existen errores o campos vacíos en el listado de conceptos. Por favor, corríjalos.');
        return false;
    }
    
    return true;
}