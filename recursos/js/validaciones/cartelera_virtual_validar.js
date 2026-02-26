/**
 * cartelera_virtual_validar.js
 * Validaciones en tiempo real para Cartelera Virtual
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    // ============================================
    // VALIDACIONES EN TIEMPO REAL
    // ============================================

    const regexChar = /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'"!?¡¿%°\-\s]*$/;
    const regexTitulo = /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'"!?¡¿%°\-\s]{3,100}$/;
    const regexDescripcion = /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'"!?¡¿%°\-\s]{3,200}$/;

    // Título
    $("#titulo").on("keypress", function(e) {
        Validaciones.keyPress(regexChar, e);
    });
    $("#titulo").on("keyup", function() {
        Validaciones.keyUp(regexTitulo, this, this.nextElementSibling,
            "Debe tener entre 3 y 100 caracteres (letras, números y signos básicos)");
    });

    // Descripción
    $("#descripcion").on("keypress", function(e) {
        Validaciones.keyPress(regexChar, e);
    });
    $("#descripcion").on("keyup", function() {
        Validaciones.keyUp(regexDescripcion, this, this.nextElementSibling,
            "Debe tener entre 3 y 200 caracteres (letras, números y signos básicos)");
    });

    // Fecha
    $("#fecha").on("keyup change", function() {
        const valido = /^\d{4}-\d{2}-\d{2}$/.test(this.value);
        if (valido) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            this.nextElementSibling.textContent = "";
        } else {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            this.nextElementSibling.textContent = "Formato YYYY-MM-DD";
        }
    });

    // Prioridad
    $("#prioridad").on("change", function() {
        Validaciones.select('prioridad');
    });

    // ============================================
    // ENVÍO DEL FORMULARIO
    // ============================================
    $("#boton_formulario").on("click", async function(e) {
        e.preventDefault();
        const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

        if (await validarEnvio(accion)) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} esta publicación?`,
                showCancelButton: true,
                confirmButtonText: `Sí, ${accion}`,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) envio(accion);
            });
        }
    });
});

/**
 * Validación completa del formulario antes del envío
 */
async function validarEnvio(accion) {
    const titulo = document.getElementById("titulo");
    const descripcion = document.getElementById("descripcion");
    const fecha = document.getElementById("fecha");
    const prioridad = document.getElementById("prioridad");

    const regexTitulo = /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'"!?¡¿%°\-\s]{3,100}$/;
    const regexDescripcion = /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'"!?¡¿%°\-\s]{3,200}$/;

    if (!Validaciones.keyUp(regexTitulo, titulo, titulo.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'El título debe tener entre 3 y 100 caracteres.');
        return false;
    }

    if (!Validaciones.keyUp(regexDescripcion, descripcion, descripcion.nextElementSibling, '')) {
        Utilidades.mensaje('error', 'Error', 'La descripción debe tener entre 3 y 200 caracteres.');
        return false;
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha.value)) {
        Utilidades.mensaje('error', 'Error', 'La fecha debe tener formato YYYY-MM-DD.');
        return false;
    }

    if (!Validaciones.select('prioridad')) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar una prioridad.');
        return false;
    }

    return true;
}