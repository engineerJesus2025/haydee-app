$(document).ready(function () {

    /* UN solo carácter permitido (letra, número, símbolos básicos) */
    const regexChar  = /[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]/;

    /* Texto completo: 3-200 caracteres con los mismos símbolos */
    const regexTexto = /^[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]{3,200}$/;

    /* ----------  TÍTULO  ---------- */
    $("#titulo").on("keypress", e => validarKeyPress(regexChar, e));
    $("#titulo").on("keyup",   function () {
        validarKeyUp(/^[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]{3,100}$/, this, this.nextElementSibling,
                      "Debe ingresar un título válido (mín. 3 caracteres y max. 100)");
    });

    /* ----------  DESCRIPCIÓN  ---------- */
    $("#descripcion").on("keypress", e => validarKeyPress(regexChar, e));
    $("#descripcion").on("keyup",   function () {
        validarKeyUp(regexTexto, this, this.nextElementSibling,
                      "Debe ingresar una descripción válida (mín. 3 caracteres y max. 200)");
    });

    /* ----------  FECHA  ---------- */
    $("#fecha").on("keyup change", () => validarFecha(document.getElementById("fecha")));


    document.getElementById('prioridad').addEventListener("change",e=>{
        let valido = validarKeyUp(/^[0-9]{1}$/,
        e.target,e.target.nextElementSibling,"El valor de la prioridad no es válido");

        if (!valido) return;
    });

    /* ----------  BOTÓN  ---------- */
    $("#boton_formulario").on("click", async function (e) {
        const accion = e.target.hasAttribute("modificar") ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion, regexTexto)) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} esta publicación?`,
                showCancelButton: true,
                confirmButtonText: "Si, " + accion,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then(res => { if (res.isConfirmed) envio(accion); });
        }
    });
});
// Fin de AJAX

function mensajes(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        title: titulo,
        text: mensaje,
        timer: tiempo,
        showConfirmButton: true,
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#e01d22",
    });
} // Fin de mensajes

async function validarEnvio(accion, regexTexto) {
    if (!validarKeyUp(/^[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]{3,100}$/, document.getElementById("titulo"), document.getElementById("titulo").nextElementSibling, 'ingresar un título válido (mín. 3 caracteres y max. 100)'))
        { mensajes("error", 2000, "Error", "Debe ingresar un título válido"); return false; }

    if (!validarKeyUp(regexTexto, document.getElementById("descripcion"), document.getElementById("descripcion").nextElementSibling, 'ingresar una descripción válida (mín. 3 caracteres y max. 200)'))
        { mensajes("error", 2000, "Error", "Debe ingresar una descripción válida"); return false; }

    if (!validarFecha(document.getElementById("fecha"))) {
        mensajes("error", 2000, "Error", "Debe ingresar una fecha válida"); return false;
    }
    if (!validar_select("prioridad")) {
        mensajes("error", 2000, "Error", "Debe seleccionar la prioridad"); return false;
    }else{
        let valido = validarKeyUp(/^[0-9]{1}$/,
        document.getElementById('prioridad'),document.getElementById('prioridad').nextElementSibling,"El valor de la prioridad no es válido");
        if (!valido) {
            mensajes("error", 2000, "Atención", "El valor de la prioridad no es válido");
            return false;
        }
    }
    return true;
}


function validarKeyPress(er, e) {
    const key = e.keyCode || e.which;
    if (!er.test(String.fromCharCode(key))) e.preventDefault();
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

function validar_select(id) {
    let selec = document.querySelector("#"+id);
    if (selec.value == '') {
        selec.classList.add('is-invalid')
        selec.classList.remove('is-valid');
        selec.nextElementSibling.textContent = "Debe seleccionar una opcion";
        return false;
    }
    else{
        selec.classList.add('is-valid');
        selec.classList.remove('is-invalid');
        selec.nextElementSibling.textContent = "";
        return true;
    }
}

function validarFecha($input) {
    const v = $input.value;
    let res = /^\d{4}-\d{2}-\d{2}$/.test(v);// yyyy-mm-dd
    if (res) {
        $input.classList.add('is-valid');
        $input.classList.remove('is-invalid');
        $input.nextElementSibling.textContent = "";
        return true;
    }
    else{
        $input.classList.add('is-invalid')
        $input.classList.remove('is-valid');
        $input.nextElementSibling.textContent = "El formato de la fecha es incorrecta";
        return false;
    }
}