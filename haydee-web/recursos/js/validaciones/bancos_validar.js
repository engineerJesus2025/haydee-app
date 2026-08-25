document.addEventListener("DOMContentLoaded", function() {
    const inputNombre = document.getElementById("nombre_banco");
    const inputCodigo = document.getElementById("codigo");

    // Validaciones de Nombre
    if (inputNombre) {
        inputNombre.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener("keyup", function() { 
            Validador.evaluarInput(this, Patrones.textoCorto, "Solo letras, mínimo 3 caracteres"); 
        });
    }

    // Validaciones de Código Bancario
    if (inputCodigo) {
        inputCodigo.addEventListener("keypress", e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputCodigo.addEventListener("keyup", function() { 
            Validador.evaluarInput(this, Patrones.codigoBanco, "Debe ser de 4 dígitos"); 
        });

        inputCodigo.addEventListener("blur", async function() {
            // Solo consultamos al servidor si el formato regex previo es válido
            if (Validador.evaluarInput(this, Patrones.codigoBanco, "Debe ser de 4 dígitos")) {
                
                const idBancoActual = document.getElementById("boton_formulario").getAttribute("id_modificar") || ""; 
                
                await Validador.verificarDatoUnico(
                    'codigo_banco',
                    { 
                        codigo: this.value, 
                        id_banco: idBancoActual
                    }, 
                    this, 
                    'Este código ya está registrado'
                );
            }
        });
    }

    // Envío del Formulario
    const btnForm = document.getElementById("boton_formulario");
    if (btnForm) {
        btnForm.addEventListener("click", async function(e) {
            e.preventDefault();
            let accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarEnvio(accion)) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este banco?`,
                    "question", 
                    () => {
                        accion === 'modificar' ? modificar() : registrar();
                    }
                );
            }
        });
    }
});

/**
 * Barrera final antes de enviar los datos a guardar
 */
async function validarEnvio(accion) {
    const inputNombre = document.getElementById("nombre_banco");
    const inputCodigo = document.getElementById("codigo");
    
    const nombreValido = Validador.evaluarInput(inputNombre, Patrones.textoCorto, 'Solo letras, mínimo 3 caracteres');
    const codigoValido = Validador.evaluarInput(inputCodigo, Patrones.codigoBanco, 'Debe ser de 4 dígitos');

    if (!nombreValido || !codigoValido) {
        Alertas.mostrar('error', 'Error de Formato', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    const idBancoActual = document.getElementById("boton_formulario").getAttribute("id_modificar") || "";
    
    const codigoUnico = await Validador.verificarDatoUnico(
        'codigo_banco',
        { 
            codigo: inputCodigo.value, 
            id_banco: idBancoActual 
        }, 
        inputCodigo, 
        'Este código ya está registrado'
    );

    if (!codigoUnico) {
        Alertas.mostrar('error', 'Código Duplicado', 'El código bancario ingresado ya pertenece a otra entidad o banco inactivo.');
        return false;
    }

    return true; // Todo en orden, procedemos con el modal de confirmación
}