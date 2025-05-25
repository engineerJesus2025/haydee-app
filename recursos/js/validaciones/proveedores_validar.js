$(document).ready(function() {
    $("#nombre_proveedor").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#nombre").on("keyup", function() {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/,
            $(this), this.nextElementSibling, "Debe ingresar el nombre del proveedor");
    });

    $("#servicio").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-z \b]*$/, e);
    });

    $("#servicio").on("keyup", function() {
        validarKeyUp(/^[A-Za-z \b]{3,30}$/,
            $(this), this.nextElementSibling, "Debe ingresar el tipo de servicio");
    });

    $("#rif").on("keypress", function(e) {
        let valor = $(this).val();
        let tecla = String.fromCharCode(e.which);

        if (valor.length === 0) {
            // Solo permitir V, E, J o P como primera letra
            if (!/^[VEJPvejp]$/.test(tecla)) {
                e.preventDefault();
            }
        } else {
            // Después de la letra, solo números
            if (!/[0-9]/.test(tecla)) {
                e.preventDefault();
            }
        }
    });
    $("#rif").on("keyup", function() {
        validarKeyUp(/^[VEJPvejp][0-9]{5,9}$/,
            $(this), this.nextElementSibling, "Debe ingresar el RIF del proveedor. Ejemplo: V-E-J-P12345678");
    });
    $("#direccion").on("keypress", function(e) {
        validarKeyPress(/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]$/, e);
    });

    $("#direccion").on("keyup", function() {
        validarKeyUp(/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]{3,100}$/,
            $(this), this.nextElementSibling, "Debe ingresar la dirección del proveedor");
    });

    $("#boton_formulario").on("click", async function(e) {
        let accion = (e.target.hasAttribute("modificar")) ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion) == true) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} este proveedor?`,
                showCancelButton: true,
                confirmButtonText: accion,
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
        $("#nombre_proveedor"), document.querySelector("#nombre_proveedor").nextElementSibling, 'Debe ingresar el nombre del proveedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el nombre del proveedor',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[A-Za-z ]{3,30}$/,
        $("#servicio"), document.querySelector("#servicio").nextElementSibling, 'Debe ingresar el tipo de servicio del proovedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el tipo de servicio del proveedor',
            'El formato debe ser sólo en letras');
        return false;
    }
    if(validarKeyUp(
        /^[VEJPvejp][0-9]{5,9}$/,
        $("#rif"), document.querySelector("#rif").nextElementSibling, 'Debe ingresar el RIF del proveedor'
    ) === 0) {
        mensajes('error', 4000, 'Debe ingresar el RIF del proveedor',
            'El formato debe ser la letra inicial y números. Ejemplo: VEJP123456789');
        return false;
    }
    if(validarKeyUp(
        /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9,.\-#° ]{3,100}$/,
        $("#direccion"), document.querySelector("#direccion").nextElementSibling, 'Debe ingresar la dirección del proveedor'
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
    a = er.test(etiqueta.val());

    if(a){

        etiquetamensaje.textContent = "";
        return 1;
    }
    else{
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