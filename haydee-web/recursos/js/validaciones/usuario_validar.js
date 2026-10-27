document.addEventListener("DOMContentLoaded", function() {
    const inputsNombres = document.querySelectorAll("#nombre, #apellido");
    const inputCorreo = document.querySelector("#correo");
    const inputsContrasenas = document.querySelectorAll("#contra, #confir_contra");
    const selectRol = document.querySelector("#rol_id");

    // Nombre y Apellido
    inputsNombres.forEach(input => {
        input.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        input.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.nombrePersona, "Solo texto, no más de 20 caracteres"));
    });

    // Correo
    inputCorreo.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasCorreo));
    inputCorreo.addEventListener("keyup", (e) => Validador.evaluarInput(e.target, Patrones.correo, "El formato debe ser: ejemplo@gmail.com"));
    
    // Validar duplicidad de correo al salir del input (blur)
    inputCorreo.addEventListener("blur", async function() {
        if (this.value === correo_an) return; // Si es el mismo de la BD, omitir
        
        if (Patrones.correo.test(this.value)) {
            await Validador.verificarDuplicadoEnServidor(
                'correo', 
                { correo: this.value }, 
                this, 
                'Este correo ya está en uso, ingrese uno diferente.'
            );
        }       
    });

    // Contraseñas
    inputsContrasenas.forEach(input => {
        input.addEventListener("keypress", (e) => Validador.bloquearTeclasInvalidas(e, Patrones.teclasContrasenaExtendida));
        input.addEventListener("keyup", (e) => Validador.evaluarContrasenaFuerte(e.target));
    });

    // MEDIDOR DE FORTALEZA DE CONTRASEÑA
    const inputContra = document.querySelector("#contra");
    if (inputContra) {
        ComponentesUI.inicializarMedidorContrasena(
            inputContra,
            document.getElementById('barra_seguridad'),
            document.getElementById('texto_seguridad')
        );
    }

    // Validación de Rol (Clave foránea)
    selectRol.addEventListener("change", async function() {
        if (!Validador.evaluarInput(this, Patrones.digitos, "El valor del rol no es válido")) return;

        let datos = new FormData();
        datos.append('validar', 'validar_clave_foranea');
        datos.append('tabla', 'roles');
        datos.append('nombre_clave', 'id_rol');
        datos.append('valor', this.value);

        let res = await Peticiones.enviar(datos);
        
        if (res.estatus) {
            EstadoInputs.marcarExito(this);
        } else {
            EstadoInputs.marcarError(this, "El rol seleccionado no existe");
        }
    });

    // ENVÍO DE FORMULARIO
    document.querySelector("#boton_formulario").addEventListener("click", async function(e) {
        e.preventDefault();
        let accion = (this.hasAttribute("modificar")) ? "modificar" : "Registrar";      
        
        if(await validarEnvio(accion) === true){
            Alertas.confirmarAccion(
                "Confirmar Operación",
                `¿Está seguro que desea ${accion.toLowerCase()} este usuario?`,
                "question", 
                () => {
                    envio(accion);
                    correo_an = null;
                }
            );
        }   
    });
});

// FUNCIONES DE VALIDACIÓN GENERALES
async function validarEnvio(accion = "Registrar"){  
    
    // Validaciones básicas usando el helper
    const nombreV = Validador.evaluarInput(document.querySelector("#nombre"), Patrones.nombrePersona, 'Formato incorrecto');
    const apellidoV = Validador.evaluarInput(document.querySelector("#apellido"), Patrones.nombrePersona, 'Formato incorrecto');
    const correoV = Validador.evaluarInput(document.querySelector("#correo"), Patrones.correo, 'Correo inválido');
    const rolV = Validador.evaluarSelect("rol_id");

    if (!nombreV || !apellidoV || !correoV || !rolV) {
        Alertas.mostrar('error', 'Formulario Incompleto', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    const inputContra = document.querySelector("#contra");
    const inputConfirContra = document.querySelector("#confir_contra");

    if (accion === "Registrar") {
        const contraV = Validador.evaluarContrasenaFuerte(inputContra);
        if (!contraV) {
            Alertas.mostrar('error', 'Error de Seguridad',
                'La contraseña debe tener mínimo 8 caracteres, con mayúscula, minúscula, número y un carácter especial.');
            return false;
        }

        if(inputContra.value !== inputConfirContra.value) {
            EstadoInputs.marcarError(inputConfirContra, 'Las contraseñas no coinciden');
            Alertas.mostrar('error', 'Error de Seguridad', 'Las contraseñas ingresadas no coinciden.');
            return false;
        }
    } else if (accion === "modificar") {
        // Si escribe algo, validamos. Si lo deja en blanco, el backend mantendrá la actual.
        if (inputContra.value !== '' || inputConfirContra.value !== '') {
            const nuevaV = Validador.evaluarContrasenaFuerte(inputContra);
            if(!nuevaV) {
                Alertas.mostrar('error', 'Error de Seguridad',
                'La contraseña debe tener mínimo 8 caracteres, con mayúscula, minúscula, número y un carácter especial.');
                return false;
            }

            if(inputContra.value !== inputConfirContra.value) {
                EstadoInputs.marcarError(inputConfirContra, 'Las contraseñas no coinciden');
                Alertas.mostrar('error', 'Error', 'Las nuevas contraseñas no coinciden.');
                return false;
            }
        } else {
            // Limpiamos los inputs visualmente por si el usuario borró lo que había escrito
            EstadoInputs.limpiar(inputContra);
            EstadoInputs.limpiar(inputConfirContra);
        }
    }
    
    // Verificar correo duplicado de forma asíncrona (solo si lo editó)
    const inputCorreoDOM = document.querySelector("#correo");
    if(correo_an !== inputCorreoDOM.value){
        let esValido = await Validador.verificarDuplicadoEnServidor('correo', { correo: inputCorreoDOM.value }, inputCorreoDOM, 'Este correo ya está en uso');
        
        if(esValido === false) {
            return false;
        }
    }
    return true;
}