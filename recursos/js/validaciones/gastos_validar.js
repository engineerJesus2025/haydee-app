$(document).ready(function () {

    // Evento principal para validar y enviar el formulario
    $("#boton_formulario").on("click",async function (e) {
        e.preventDefault();
        let accion = $(this).attr("modificar") ? "Editar" : "Registrar";
        if (await validarFormularioCompleto()) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este gasto?`,
                showCancelButton: true,
				confirmButtonText: "Si, " + accion,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    envio(accion);
                }
            });
        }
    });

    const formulario = $("#form_gastos");

    // Para el campo MONTO
    formulario.on('keypress', '.monto', function (e) {
        // Permite números y un solo punto o coma
        validarKeyPress(/^[\d.,]*$/, e);
    });
    formulario.on('keyup', '.monto', function () {
        // Valida que el formato sea, por ejemplo, 150.50 o 150,50
        validarKeyUp(/^\d{1,10}([.,]\d{1,2})?$/, $(this), "El monto debe ser un número válido (ej: 150,50).");
    });

    // Para el campo REFERENCIA
    formulario.on('keypress', '.referencia', function (e) {
        validarKeyPress(/^[0-9]*$/, e);
    });
    formulario.on('keyup', '.referencia', function () {
        validarKeyUp(/^[0-9]{4,20}$/, $(this), "La referencia debe tener entre 4 y 20 números.");
    });

    // Para los campos de DESCRIPCIÓN (general y de detalle)
    formulario.on('keyup', '#descripcion_gasto, .descripcion_detalle', function () {
        validarKeyUp(/^.{10,}$/, $(this), "La descripción debe tener al menos 10 caracteres.");
    });

    // Para el campo FECHA
    formulario.on('change', '.fecha_detalle', function () {
        validarKeyUp(/.+/, $(this), "Debe seleccionar una fecha.");
    });

    document.getElementById('tipo').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[a-zA-z]{3,15}$/,
        e.target,e.target.nextElementSibling,"El valor del tipo no es válido");

        if (!valido) return;
    });

    document.getElementById('tipo_gasto').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        e.target,e.target.nextElementSibling,"El valor del tipo de gasto no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','tipo_gasto');
        datos.append('nombre_clave','id_tipo_gasto');
        datos.append('valor',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "El tipo de gasto seleccionado no existe";
        }
    });
 
    document.getElementById('proveedor').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        e.target,e.target.nextElementSibling,"El valor del proveedor no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','proveedores');
        datos.append('nombre_clave','id_proveedor');
        datos.append('valor',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "El proveedor seleccionado no existe";
        }
    });

    document.getElementById('solicitud').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        e.target,e.target.nextElementSibling,"El valor de la solicitud no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','solicitudes_gasto');
        datos.append('nombre_clave','id_solicitud');
        datos.append('valor',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "La solicitud seleccionada no existe";
        }
    });

    document.querySelector('.metodo_pago').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
        e.target,e.target.nextElementSibling,"El valor del método de pago no es válido");

        if (!valido) return;
    });

    document.querySelector('.banco').addEventListener("change",async e=>{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        e.target,e.target.nextElementSibling,"El valor del banco no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','bancos');
        datos.append('nombre_clave','id_banco');
        datos.append('valor',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "El banco seleccionado no existe";
        }
    });
}); // Fin de $(document).ready()


// ✅ --- FUNCIONES DE AYUDA PARA LA VALIDACIÓN

/**
 * Bloquea la escritura de caracteres que no coincidan con la expresión regular.
 * @param {RegExp} regex - La expresión regular para validar la tecla.
 * @param {Event} e - El objeto del evento keypress.
 */
function validarKeyPress(regex, e) {
    const char = String.fromCharCode(e.which);
    if (!regex.test(char)) {
        e.preventDefault();
    }
}

/**
 * Muestra u oculta un mensaje de validación al escribir.
 * @param {RegExp} regex - La expresión regular para validar el valor completo.
 * @param {jQuery} campo - El objeto jQuery del campo (input/textarea).
 * @param {string} mensaje - El mensaje de error a mostrar.
 */
function validarKeyUp(regex, campo, mensaje) {
    let mensajeContenedor = campo.closest('.input-group, .form-group').find('.mensaje-validacion');
    if (mensajeContenedor.length === 0) { // Fallback por si la estructura es diferente
        mensajeContenedor = campo.parent().find('.mensaje-validacion');
    }

    if (!regex.test(campo.val()) && campo.val() !== "") {
        campo.addClass('is-invalid');
        mensajeContenedor.text(mensaje);
    } else {
        campo.removeClass('is-invalid');
        mensajeContenedor.text('');
    }
}

/**
 * Muestra una alerta de SweetAlert2.
 * @param {string} icono - 'success', 'error', 'warning', 'info'
 * @param {number} tiempo - Tiempo en milisegundos para que se cierre sola.
 * @param {string} titulo - El título de la alerta.
 * @param {string} texto - El mensaje de la alerta.
 */
function mensajes(icono, tiempo, titulo, texto) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: texto,
        confirmButtonColor: "#1b8a40",
    });
}

/**
 * Valida el formulario completo, incluyendo todos los detalles de gasto.
 * @returns {boolean} - Devuelve true si todo es válido, de lo contrario, false.
 */
async function validarFormularioCompleto() {
    const tipo = document.getElementById('tipo'), 
    tipo_gasto = document.getElementById('tipo_gasto'),
    proveedor = document.getElementById('proveedor'),
    solicitud = document.getElementById('solicitud');

    // --- 1. VALIDACIÓN DE CAMPOS PRINCIPALES ---
    if ($("#tipo").val() === null || $("#tipo").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Tipo.');
        return false;
    }
    else{
        let valido = validarKeyUpSelect(/^[a-zA-z]{3,15}$/,
        tipo,tipo.nextElementSibling,"El valor del tipo no es válido");

        if (!valido) {
            mensajes('error', 4000, 'Atención', 'El campo tipo no posee un valor válido');
            return false;
        }
    }

    if ($("#tipo_gasto").val() === null || $("#tipo_gasto").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Tipo de Gasto.');
        return false;
    }
    else{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        tipo_gasto,tipo_gasto.nextElementSibling,"El valor del tipo de gasto no es válido");

        if (!valido) {
            mensajes('error', 4000, 'Atención', 'El campo tipo de gasto no posee un valor válido');
            return false;
        }

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','tipo_gasto');
        datos.append('nombre_clave','id_tipo_gasto');
        datos.append('valor',tipo_gasto.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            tipo_gasto.classList.add('is-valid');
            tipo_gasto.classList.remove('is-invalid');
            tipo_gasto.nextElementSibling.textContent = "";
        }
        else{
            tipo_gasto.classList.remove('is-valid');
            tipo_gasto.classList.add('is-invalid');
            tipo_gasto.nextElementSibling.textContent = "El tipo de gasto seleccionado no existe";

            mensajes('error', 4000, 'Atención', 'El campo de tipo de gasto seleccionado no existe');
            return false;
        }
    }

    if ($("#descripcion_gasto").val().trim() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes ingresar la Descripción del Gasto.');
        return false;
    }

    if ($("#proveedor").val() === null || $("#proveedor").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar un Proveedor.');
        return false;
    }
    else{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        proveedor,proveedor.nextElementSibling,"El valor del proveedor no es válido");

        if (!valido) {
            mensajes('error', 4000, 'Atención', 'El campo proveedor no posee un valor válido');
            return false;
        }

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','proveedores');
        datos.append('nombre_clave','id_proveedor');
        datos.append('valor',proveedor.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            proveedor.classList.add('is-valid');
            proveedor.classList.remove('is-invalid');
            proveedor.nextElementSibling.textContent = "";
        }
        else{
            proveedor.classList.remove('is-valid');
            proveedor.classList.add('is-invalid');
            proveedor.nextElementSibling.textContent = "El proveedor seleccionado no existe";

            mensajes('error', 4000, 'Atención', 'El campo de proveedores seleccionado no existe');
            return false;
        }
    }

    if ($("#solicitud").val() === null || $("#solicitud").val() === "") {
        mensajes('error', 4000, 'Campo Requerido', 'Debes seleccionar una Solicitud.');
        return false;
    }
    else{
        let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
        solicitud,solicitud.nextElementSibling,"El valor de la solicitud no es válido");

        if (!valido) {
            mensajes('error', 4000, 'Atención', 'El campo solicitud de gasto no posee un valor válido');
            return false;
        }

        let datos = new FormData();
        datos.append('validar','validar_clave_foranea');
        datos.append('tabla','solicitudes_gasto');
        datos.append('nombre_clave','id_solicitud');
        datos.append('valor',solicitud.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            solicitud.classList.add('is-valid');
            solicitud.classList.remove('is-invalid');
            solicitud.nextElementSibling.textContent = "";
        }
        else{
            solicitud.classList.remove('is-valid');
            solicitud.classList.add('is-invalid');
            solicitud.nextElementSibling.textContent = "La solicitud seleccionada no existe";

            mensajes('error', 4000, 'Atención', 'El campo de solicitud de gasto seleccionado no existe');
            return false;
        }
    }

    // --- 2. VALIDACIÓN DE TODOS LOS BLOQUES DE DETALLE ---
    let todosLosDetallesValidos = true;
    $(".detalle-gasto:visible").each(async function (index) {
        let bloque = $(this);
        let numeroDetalle = index + 1;

        // Validar Fecha
        if (bloque.find(".fecha_detalle").val() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar una fecha.');
            todosLosDetallesValidos = false;
            return false; // Detiene el bucle .each()
        }

        // Validar Método de Pago
        if (bloque.find(".metodo_pago").val() === null || bloque.find(".metodo_pago").val() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar un método de pago.');
            todosLosDetallesValidos = false;
            return false;
        }
        else{
            let valido = validarKeyUpSelect(/^[a-zA-z ]{3,20}$/,
            this.querySelector('.metodo_pago'),this.querySelector('.metodo_pago').nextElementSibling,"El valor del método de pago no es válido");

            if (!valido) {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'El método de pago no pose un valor valido');
                todosLosDetallesValidos = false;
                return false;
            }
        }

        // Validar Monto
        const monto = bloque.find(".monto").val();
        const montoRegex = /^[0-9]+([.,][0-9]{1,2})?$/;
        if (monto.trim() === "" || !montoRegex.test(monto)) {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'El monto no es válido. Usa solo números y hasta dos decimales.');
            todosLosDetallesValidos = false;
            return false;
        }

        // Validar Descripción del Detalle
        if (bloque.find(".descripcion_detalle").val().trim() === "") {
            mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes ingresar la descripción del detalle.');
            todosLosDetallesValidos = false;
            return false;
        }

        // --- VALIDACIÓN DE CAMPOS CONDICIONALES (si están visibles) ---
        if (bloque.find(".grupo_referencia").is(":visible")) {

            // Validar Referencia
            const referencia = bloque.find(".referencia").val();
            const referenciaRegex = /^[0-9]{4,20}$/; // Ejemplo: solo números, de 4 a 20 dígitos
            if (referencia.trim() === "" || !referenciaRegex.test(referencia)) {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'La referencia es inválida (solo números, de 4 a 20 dígitos).');
                todosLosDetallesValidos = false;
                return false;
            }

            // Validar Banco
            if (bloque.find(".banco").val() === null || bloque.find(".banco").val() === "") {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debes seleccionar un banco.');
                todosLosDetallesValidos = false;
                return false;
            }
            else{
                let banco = this.querySelector('.banco');
                let valido = validarKeyUpSelect(/^[0-9]{1,11}$/,
                
                banco,banco.nextElementSibling,"El valor del método de pago no es válido");

                if (!valido) {
                    mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'El banco no pose un valor valido');
                    todosLosDetallesValidos = false;
                    return false;
                }

                let datos = new FormData();
                datos.append('validar','validar_clave_foranea');
                datos.append('tabla','bancos');
                datos.append('nombre_clave','id_banco');
                datos.append('valor',banco.value);

                valido = await verificar_clave_foranea(datos);
                
                if (valido) {
                    banco.classList.add('is-valid');
                    banco.classList.remove('is-invalid');
                    banco.nextElementSibling.textContent = "";
                }
                else{
                    banco.classList.remove('is-valid');
                    banco.classList.add('is-invalid');
                    banco.nextElementSibling.textContent = "El banco seleccionado no existe";

                    mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'El banco seleccionado no existe');
                    todosLosDetallesValidos = false;
                    return false;
                }
            }

            const hayArchivoNuevo = bloque.find(".imagen").get(0).files.length > 0;
            const hayComprobanteCargado = bloque.find(".nombre_imagen_cargada").text().trim() !== "";

            if (!hayArchivoNuevo && !hayComprobanteCargado) {
                mensajes('error', 4000, `Error en Detalle #${numeroDetalle}`, 'Debe seleccionar una imagen de comprobante para este método de pago.');
                todosLosDetallesValidos = false;
                return false; // Detiene el bucle
            }
        }
    });

    if (!todosLosDetallesValidos) {
        return false; // Si algún detalle falló, la validación general falla.
    }

    // Si todas las validaciones pasaron
    return true;
}

function validarKeyUpSelect(er,etiqueta,etiquetamensaje,
mensaje){
    a = er.test(etiqueta.value);
    
    if(a){
        etiqueta.classList.add('is-valid');
        etiqueta.classList.remove('is-invalid');

        if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
            etiqueta.nextElementSibling.classList.remove('border-danger');
            etiqueta.nextElementSibling.classList.remove('text-danger');

            etiqueta.nextElementSibling.classList.add('border-success');
            etiqueta.nextElementSibling.classList.add('text-success');
        }
        etiquetamensaje.textContent = "";
        return 1;
    }
    else{
        etiqueta.classList.add('is-invalid');
        etiqueta.classList.remove('is-valid');

        if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
            etiqueta.nextElementSibling.classList.remove('border-success');
            etiqueta.nextElementSibling.classList.remove('text-success');

            etiqueta.nextElementSibling.classList.add('border-danger');
            etiqueta.nextElementSibling.classList.add('text-danger');
        }
        etiquetamensaje.textContent = mensaje;
        return 0;
    }
}

async function verificar_clave_foranea(datos){  
    let data = await fetch("",{method:"POST", body:datos}).then(res=>{      
        let result = res.json()
        return result;
    });

    return data     
}