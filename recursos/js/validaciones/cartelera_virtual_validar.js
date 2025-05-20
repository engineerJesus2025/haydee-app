$(document).ready(function () {

    /* UN solo carácter permitido (letra, número, símbolos básicos) */
    const regexChar  = /[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]/;

    /* Texto completo: 3-200 caracteres con los mismos símbolos */
    const regexTexto = /^[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'"!?¡¿%°\- ]{3,200}$/;

    /* ----------  TÍTULO  ---------- */
    $("#titulo").on("keypress", e => validarKeyPress(regexChar, e));
    $("#titulo").on("keyup",   function () {
        validarKeyUp(regexTexto, $(this), this.nextElementSibling,
                      "Debe ingresar un título válido (mín. 3 caracteres)");
    });

    /* ----------  DESCRIPCIÓN  ---------- */
    $("#descripcion").on("keypress", e => validarKeyPress(regexChar, e));
    $("#descripcion").on("keyup",   function () {
        validarKeyUp(regexTexto, $(this), this.nextElementSibling,
                      "Debe ingresar una descripción válida (mín. 3 caracteres)");
    });

    /* ----------  FECHA  ---------- */
    $("#fecha").on("keyup change", () => validarFecha($("#fecha")));

    /* ----------  BOTÓN  ---------- */
    $("#boton_formulario").on("click", async function (e) {
        const accion = e.target.hasAttribute("modificar") ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion, regexTexto)) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} esta publicación?`,
                showCancelButton: true,
                confirmButtonText: accion,
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
    if (!validarKeyUp(regexTexto, $("#titulo"), $("#titulo")[0].nextElementSibling))
        { mensajes("error", 2000, "Error", "Debe ingresar un título válido"); return false; }

    if (!validarKeyUp(regexTexto, $("#descripcion"), $("#descripcion")[0].nextElementSibling))
        { mensajes("error", 2000, "Error", "Debe ingresar una descripción válida"); return false; }

    if (!validarFecha($("#fecha"))) {
        mensajes("error", 2000, "Error", "Debe ingresar una fecha válida"); return false;
    }
    if (!validar_select("prioridad")) {
        mensajes("error", 2000, "Error", "Debe seleccionar la prioridad"); return false;
    }
    return true;
}


function validarKeyPress(er, e) {
    const key = e.keyCode || e.which;
    if (!er.test(String.fromCharCode(key))) e.preventDefault();
}

function validarKeyUp(er, $input, msgElem, msg = "") {
    const ok = er.test($input.val().trim());
    if (msgElem) msgElem.textContent = ok ? "" : msg;
    return ok;
}

function validar_select(id) {
    return document.getElementById(id).value.trim() !== "";
}

function validarFecha($input) {
    const v = $input.val();
    return /^\d{4}-\d{2}-\d{2}$/.test(v);  // yyyy-mm-dd
}