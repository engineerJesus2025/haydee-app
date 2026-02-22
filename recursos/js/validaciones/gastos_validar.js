/**
 * gastos_validar.js
 * Validaciones en tiempo real para Gastos
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function () {

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - CAMPOS PRINCIPALES
    // ============================================================

    // Clasificación (select fijo)
    $("#clasificacion").on("change", function () {
        Validaciones.select(this.id);
    });

    // Tipo de Gasto (FK)
    $("#tipo_gasto").on("change", async function () {
        if (!Validaciones.select(this.id)) return;
        await validarClaveForanea(this, 'tipo_gasto', 'id_tipo_gasto');
    });

    // Proveedor (FK)
    $("#proveedor").on("change", async function () {
        if (!Validaciones.select(this.id)) return;
        await validarClaveForanea(this, 'proveedores', 'id_proveedor');
    });

    // Solicitud (FK opcional)
    $("#solicitud").on("change", async function () {
        if (this.value === "") {
            Validaciones.limpiar(this);
            return;
        }
        if (!/^\d+$/.test(this.value)) {
            Validaciones.mostrarError(this, "ID inválido");
            return;
        }
        await validarClaveForanea(this, 'solicitudes_gasto', 'id_solicitud');
    });

    // Descripción general
    $("#descripcion_gasto").on("keyup", function () {
        Validaciones.keyUp(/^.{10,}$/, this, this.nextElementSibling,
            "La descripción debe tener al menos 10 caracteres");
    });

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - DETALLES (delegación)
    // ============================================================

    const contenedor = $("#detalles-container");

    // Fecha
    contenedor.on("change", ".fecha_detalle", function () {
        Validaciones.fecha(this, this.nextElementSibling);
    });

    // Método de pago
    contenedor.on("change", ".metodo_pago", function () {
        if (!Validaciones.selectCustom(this, /^[a-zA-Z ]{3,20}$/, "Método de pago inválido")) return;
        // La visibilidad de los campos condicionales se maneja en el JS de UI
    });

    // Monto
    contenedor.on("keypress", ".monto", function (e) {
        Validaciones.keyPress(/^[\d.,]$/, e);
    });
    contenedor.on("keyup", ".monto", function () {
        Validaciones.keyUp(/^\d{1,10}([.,]\d{1,2})?$/, this, this.nextElementSibling,
            "Monto inválido (ej: 150,50)");
    });

    // Descripción del detalle
    contenedor.on("keyup", ".descripcion_detalle", function () {
        Validaciones.keyUp(/^.{3,}$/, this, this.nextElementSibling,
            "Mínimo 3 caracteres");
    });

    // Referencia (visible condicionalmente)
    contenedor.on("keypress", ".referencia", function (e) {
        Validaciones.keyPress(/^[0-9]$/, e);
    });
    contenedor.on("keyup", ".referencia", function () {
        Validaciones.keyUp(/^\d{4,20}$/, this, this.nextElementSibling,
            "Solo números, de 4 a 20 dígitos");
    });

    // Banco (FK)
    contenedor.on("change", ".banco", async function () {
        if (this.value === "") {
            Validaciones.mostrarError(this, "Seleccione un banco");
            return;
        }
        if (!/^\d+$/.test(this.value)) {
            Validaciones.mostrarError(this, "ID inválido");
            return;
        }
        await validarClaveForanea(this, 'bancos', 'id_banco');
    });

    // ============================================================
    // VALIDACIÓN AL ENVIAR EL FORMULARIO
    // ============================================================

    $("#boton_formulario").on("click", async function (e) {
        e.preventDefault();
        const accion = $(this).attr("modificar") ? "Editar" : "Registrar";

        if (await validarFormularioCompleto()) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Desea ${accion.toLowerCase()} este gasto?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1b8a40",
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    if (accion === "Registrar") {
                        registrar();
                    } else {
                        modificar(document.getElementById("boton_formulario").getAttribute("id_modificar"));
                    }
                }
            });
        }
    });

}); // Fin de $(document).ready()

// ============================================================
// FUNCIONES AUXILIARES DE VALIDACIÓN
// ============================================================

/**
 * Valida que una clave foránea exista en la base de datos.
 * @param {HTMLElement} input - El elemento select/input a validar.
 * @param {string} tabla - Nombre de la tabla.
 * @param {string} campo - Nombre del campo clave.
 */
async function validarClaveForanea(input, tabla, campo) {
    const valor = input.value;
    const respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: tabla,
        nombre_clave: campo,
        valor: valor
    });
    if (respuesta.estatus) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        if (input.nextElementSibling) input.nextElementSibling.textContent = "";
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if (input.nextElementSibling) input.nextElementSibling.textContent = 
            `El valor seleccionado no existe en ${tabla}`;
    }
}

/**
 * Validación completa del formulario (cabecera + todos los detalles).
 * @returns {Promise<boolean>} - true si todo es válido.
 */
async function validarFormularioCompleto() {
    // 1. Validar campos principales
    if (!Validaciones.select("clasificacion")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar una clasificación");
        return false;
    }

    if (!Validaciones.select("tipo_gasto")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar un tipo de gasto");
        return false;
    }
    if (!await verificarExistencia("tipo_gasto", "tipo_gasto", "id_tipo_gasto")) return false;

    if (!Validaciones.select("proveedor")) {
        Utilidades.mensaje("error", "Error", "Debe seleccionar un proveedor");
        return false;
    }
    if (!await verificarExistencia("proveedor", "proveedores", "id_proveedor")) return false;

    const solicitud = $("#solicitud").val();
    if (solicitud && solicitud !== "") {
        if (!/^\d+$/.test(solicitud)) {
            Validaciones.mostrarError(document.getElementById("solicitud"), "ID inválido");
            Utilidades.mensaje("error", "Error", "La solicitud tiene formato inválido");
            return false;
        }
        if (!await verificarExistencia("solicitud", "solicitudes_gasto", "id_solicitud")) return false;
    }

    if (!Validaciones.keyUp(/^.{10,}$/, $("#descripcion_gasto")[0], null, "")) {
        Utilidades.mensaje("error", "Error", "La descripción general debe tener al menos 10 caracteres");
        return false;
    }

    // 2. Validar que haya al menos un detalle
    const bloques = document.querySelectorAll(".detalle-gasto");
    if (bloques.length === 0) {
        Utilidades.mensaje("error", "Error", "Debe agregar al menos un detalle de gasto");
        return false;
    }

    // 3. Validar cada detalle (el -1 es por un detalle gasto inidividual)
    for (let i = 0; i < (bloques.length - 1); i++) {
        const bloque = bloques[i];
        const num = i + 1;

        // Fecha
        const fechaInput = bloque.querySelector(".fecha_detalle");
        if (!Validaciones.fecha(fechaInput, fechaInput.nextElementSibling, true)) {
            Utilidades.mensaje("error", `Detalle #${num}`, "Fecha inválida");
            return false;
        }

        // Método de pago
        const metodoSelect = bloque.querySelector(".metodo_pago");
        if (!Validaciones.selectCustom(metodoSelect, /^[a-zA-Z ]{3,20}$/, "Método de pago inválido")) {
            Utilidades.mensaje("error", `Detalle #${num}`, "Seleccione un método de pago válido");
            return false;
        }

        // Monto
        const montoInput = bloque.querySelector(".monto");
        if (!Validaciones.keyUp(/^\d{1,10}([.,]\d{1,2})?$/, montoInput, montoInput.nextElementSibling, "")) {
            Utilidades.mensaje("error", `Detalle #${num}`, "Monto inválido (use números y hasta 2 decimales)");
            return false;
        }

        // Descripción del detalle
        const descDet = bloque.querySelector(".descripcion_detalle");
        if (!Validaciones.keyUp(/^.{3,}$/, descDet, descDet.nextElementSibling, "")) {
            Utilidades.mensaje("error", `Detalle #${num}`, "La descripción debe tener al menos 3 caracteres");
            return false;
        }

        // Campos condicionales (si el grupo está visible)
        const grupoRef = bloque.querySelector(".grupo_referencia");
        if (grupoRef && !grupoRef.classList.contains("d-none")) {
            // Referencia
            const refInput = bloque.querySelector(".referencia");
            if (!Validaciones.keyUp(/^\d{4,20}$/, refInput, refInput.nextElementSibling, "")) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Referencia inválida (solo números, 4-20 dígitos)");
                return false;
            }

            // Banco
            const bancoSelect = bloque.querySelector(".banco");
            if (!Validaciones.selectCustom(bancoSelect, /^\d+$/, "Seleccione un banco")) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Debe seleccionar un banco");
                return false;
            }
            if (!await verificarExistenciaElemento(bancoSelect, 'bancos', 'id_banco')) {
                Utilidades.mensaje("error", `Detalle #${num}`, "El banco seleccionado no existe");
                return false;
            }

            // Imagen: debe haber archivo o imagen previa (en edición)
            const inputImagen = bloque.querySelector(".imagen");
            const nombreImagen = bloque.querySelector(".nombre_imagen_cargada")?.textContent.trim();
            const hayArchivo = inputImagen.files.length > 0;
            const hayImagenPrevia = nombreImagen && nombreImagen !== "";

            if (!hayArchivo && !hayImagenPrevia) {
                Utilidades.mensaje("error", `Detalle #${num}`, "Debe adjuntar un comprobante de pago");
                return false;
            }
        }
    }

    return true;
}

/**
 * Versión simplificada de verificar existencia para un elemento por su ID.
 */
async function verificarExistencia(idElemento, tabla, campo) {
    const input = document.getElementById(idElemento);
    const valor = input.value;
    const respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: tabla,
        nombre_clave: campo,
        valor: valor
    });
    if (respuesta.estatus) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        if (input.nextElementSibling) input.nextElementSibling.textContent = "";
        return true;
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if (input.nextElementSibling) input.nextElementSibling.textContent = 
            `El valor seleccionado no existe en ${tabla}`;
        return false;
    }
}

/**
 * Versión para elementos sin ID (dentro de detalles).
 */
async function verificarExistenciaElemento(elemento, tabla, campo) {
    const valor = elemento.value;
    const respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: tabla,
        nombre_clave: campo,
        valor: valor
    });
    if (respuesta.estatus) {
        elemento.classList.add('is-valid');
        elemento.classList.remove('is-invalid');
        if (elemento.nextElementSibling) elemento.nextElementSibling.textContent = "";
        return true;
    } else {
        elemento.classList.remove('is-valid');
        elemento.classList.add('is-invalid');
        if (elemento.nextElementSibling) elemento.nextElementSibling.textContent = 
            `El valor seleccionado no existe en ${tabla}`;
        return false;
    }
}

// Extensión de Validaciones para select con patrón personalizado (si no existe)
if (!Validaciones.selectCustom) {
    Validaciones.selectCustom = function (select, regex, mensajeError) {
        if (!select) return false;
        const valor = select.value;
        if (valor === null || valor === "") {
            select.classList.add('is-invalid');
            select.classList.remove('is-valid');
            if (select.nextElementSibling) select.nextElementSibling.textContent = mensajeError || "Seleccione una opción";
            return false;
        }
        if (regex && !regex.test(valor)) {
            select.classList.add('is-invalid');
            select.classList.remove('is-valid');
            if (select.nextElementSibling) select.nextElementSibling.textContent = mensajeError;
            return false;
        }
        select.classList.add('is-valid');
        select.classList.remove('is-invalid');
        if (select.nextElementSibling) select.nextElementSibling.textContent = "";
        return true;
    };
}