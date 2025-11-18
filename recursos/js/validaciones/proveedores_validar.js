$(document).ready(function() {
    $("#fecha").on("keyup",function(){
        validarKeyUp(/^(?:(?:1[6-9]|[2-9]\d)?\d{2})(?:(?:(\/|-|\.)(?:0?[13578]|1[02])\1(?:31))|(?:(\/|-|\.)(?:0?[13-9]|1[0-2])\2(?:29|30)))$|^(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\/|-|\.)0?2\3(?:29)$|^(?:(?:1[6-9]|[2-9]\d)?\d{2})(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:0?[1-9]|1\d|2[0-8])$/,
        this,this.nextElementSibling,"Ingrese una fecha valida");
    });

    $("#nombre_proveedor").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z ]$/, e);
    });

    $("#nombre_proveedor").on("keyup", function() {
        validarKeyUp(/^[A-Za-z ]{3,20}$/,
            this, this.nextElementSibling, "Solo texto, no mas de 20 caracteres");
    });

    $("#servicio").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z ]$/, e);
    });

    $("#servicio").on("keyup", function() {
        validarKeyUp(/^[A-Za-z ]{3,20}$/,
            this, this.nextElementSibling, "Solo texto, no mas de 20 caracteres");
    });

    $("#observaciones").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-z0-9ñ., \b]*$/, e);
    });

    $("#rif").on("keypress", function(e) {
        let valor = $(this).val();
        let tecla = String.fromCharCode(e.which);
        if (!/[0-9]/.test(tecla)) {
            e.preventDefault();
        }
    });

    $("#rif").on("keyup", function() {
        validarKeyUp(/^[0-9]{7,9}$/,
            this, this.nextElementSibling, "Debe ingresar el RIF del proveedor. Ejemplo: V-E-J-P12345678");
    });
    $("#direccion").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]$/, e);
    });

    $("#direccion").on("keyup", function() {
        validarKeyUp(/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]{3,100}$/,
            this, this.nextElementSibling, "Debe ingresar la dirección del proveedor");
    });

    $("#boton_formulario").on("click", async function(e) {
        let accion = (e.target.hasAttribute("modificar")) ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion) == true) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este proveedor?`,
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
    })

    document.getElementById('tipo_documento').addEventListener('change',e=>{
        let documento_afiliado = document.getElementById('rif');
        documento_afiliado.removeAttribute("disabled");
        documento_afiliado.value = "";

        let valido = validarKeyUp(/^[VEJG\b]{1}$/,
        e.target,documento_afiliado.nextElementSibling,"Tipo de documento no válido");

        if (!valido) return;

        documento_afiliado.classList.add('is-valid');
        documento_afiliado.classList.remove('is-invalid');
        documento_afiliado.nextElementSibling.textContent = "";
    });

}); // Fin de AJAX

function mensajes(icono, tiempo, titulo, mensaje){
    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        timer: tiempo,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: "#e01d22",
    });
}

async function validarEnvio(accion = "Registrar"){
    if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        document.querySelector("#nombre_proveedor"), document.querySelector("#nombre_proveedor").nextElementSibling, 'Debe ingresar el nombre del proveedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el nombre del proveedor',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        document.querySelector("#servicio"), document.querySelector("#servicio").nextElementSibling, 'Debe ingresar el tipo de servicio del proovedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el tipo de servicio del proveedor',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[VEJG\b]{1}$/,
        document.getElementById('tipo_documento'),document.querySelector("#rif").nextElementSibling,'Tipo de documento no válido'
        )==0)
    {
        mensajes('error',4000,'Debe ingresar el tipo de documento','El valor del tipo de documento ingresado no es válido');
        
        return false;
    }
    if(validarKeyUp(
        /^[0-9]{7,9}$/,
        document.querySelector("#rif"), document.querySelector("#rif").nextElementSibling, 'Debe ingresar el RIF del proveedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el RIF del proveedor',
            'El formato debe ser la letra inicial y números. Ejemplo: VEJP123456789');
        return false;
    }
    if(validarKeyUp(
        /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]{3,100}$/,
        document.querySelector("#direccion"), document.querySelector("#direccion").nextElementSibling, 'Debe ingresar la dirección del proveedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar la dirección del proveedor',
            'El formato debe ser en letras');
        return false;
    }

    return true;

}

function validarKeyPress(er, e){
    key = e.keyCode;
    tecla = String.fromCharCode(key);
    a = er.test(tecla);
    if (!a) {
        e.preventDefault();
    }
}

function validarKeyUp(er,etiqueta,etiquetamensaje,
mensaje){
    a = er.test(etiqueta.value);
    
    if(a){
        etiqueta.classList.add('is-valid');
        etiqueta.classList.remove('is-invalid');
        etiquetamensaje.textContent = "";
        return 1;
    }
    else{
        etiqueta.classList.add('is-invalid')
        etiqueta.classList.remove('is-valid');
        etiquetamensaje.textContent = mensaje;
        return 0;
    }
}

async function verificar_duplicados(datos){
    // Solo es un fetching de datos, en body mandamos los datos
    // Estos datos se mandan al controdalor
    let data = await fetch("",{method:"POST", body:datos}).then(res=>{
        let result = res.json()
        return result;//Convertimos el resultado de json a js y lo mandamos
    })
    // aqui revisamos el estatus, si es true es porque esta duplicado y mandamos un mensaje
    if(data.estatus){
        document.querySelector(`#${data.busqueda}`).nextElementSibling.textContent = `${data.busqueda} ya registrado/a`
        return true;
    }
    return false;
}