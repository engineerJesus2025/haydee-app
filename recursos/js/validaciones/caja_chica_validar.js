/**
 * caja_chica_validar.js
 * Validaciones en tiempo real para Caja Chica
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    // Fecha
    $("#fecha").on("keyup change", function() {
        Validaciones.fecha(this, this.nextElementSibling);
    });

    // Monto (permite números con hasta 2 decimales)
    $("#monto, #monto_reponer").on("keypress", function(e) {
        Validaciones.keyPress(/^[0-9,.]$/, e);
    });

    $("#monto, #monto_reponer").on("keyup", function() {
        let valido = Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
            this, this.nextElementSibling.nextElementSibling,
            "Solo números, máximo 12 enteros y 2 decimales");

        if (valido) {
            let id = this.id;
            let esBs = this.getAttribute("monto") === "bs";
            let inputConvertir = document.getElementById(id === "monto" ? "monto_cambio" : "monto_cambio_reponer");

            if (this.value === '' || parseFloat(this.value) === 0) {
                inputConvertir.value = '';
                return;
            }

            if (esBs) {
                inputConvertir.value = (parseFloat(this.value.replace(',', '.')) / tasa_dolar).toFixed(2);
            } else {
                inputConvertir.value = (parseFloat(this.value.replace(',', '.')) * tasa_dolar).toFixed(2);
            }

            // Actualizar fondo restante si es el monto del formulario de gasto
            if (id === "monto") {
                actualizarFondoRestante();
            }
        }
    });

    // Concepto
    $("#concepto").on("keypress", function(e) {
        Validaciones.keyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]$/, e);
    });

    $("#concepto").on("keyup", function() {
        Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]{3,100}$/,
            this, this.nextElementSibling,
            "Solo texto, mínimo 3 y máximo 100 caracteres");
    });

    // Select de caja chica
    $("#mes_select").on("change", async function() {
        if (!Validaciones.select(this.id)) return;

        let datos = new FormData();
        datos.append('validar', 'validar_clave_foranea');
        datos.append('tabla', 'caja_chica');
        datos.append('nombre_clave', 'id_caja_chica');
        datos.append('valor', this.value);

        let respuesta = await Utilidades.validar('validar_clave_foranea', {
            tabla: 'caja_chica',
            nombre_clave: 'id_caja_chica',
            valor: this.value
        });

        if (respuesta.estatus) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            this.nextElementSibling.textContent = '';
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = 'La caja seleccionada no existe';
        }
    });

    // Descripción en modal de observación
    $("#descripcion_input").on("keypress", function(e) {
        Validaciones.keyPress(/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\s-]$/, e);
    });

    $("#descripcion_input").on("keyup", function() {
        Validaciones.keyUp(/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\s-]{0,100}$/,
            this, this.nextElementSibling,
            "Máximo 100 caracteres, solo letras/números/espacios/guiones");
    });
});

// ========== FUNCIONES DE VALIDACIÓN ADICIONALES ==========
function actualizarFondoRestante() {
    let fondo = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let etiqueta = document.getElementById("fondos_restante");

    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        etiqueta.textContent = document.getElementById("fondos_caja").textContent;
        return;
    }

    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio").value.replace(',', '.'));

    if (isNaN(monto)) return;

    let nuevoFondo = fondo - monto + diferencia;
    if (nuevoFondo < 0) {
        etiqueta.textContent = "Excedido";
    } else {
        etiqueta.textContent = nuevoFondo.toFixed(2) + " Bs. / " + (nuevoFondo / tasa_dolar).toFixed(2) + " $";
    }
}

async function validarEnvio(accion) {
    // Validar fecha
    if (!Validaciones.fecha(document.getElementById("fecha"), null, true)) {
        Utilidades.mensaje('error', 'Error', 'La fecha es inválida');
        return false;
    }

    // Validar monto
    let montoInput = document.getElementById("monto");
    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        Validaciones.mostrarError(montoInput, 'El monto es obligatorio');
        Utilidades.mensaje('error', 'Error', 'Debe ingresar un monto');
        return false;
    }

    if (!Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
        montoInput, montoInput.nextElementSibling.nextElementSibling,
        'Formato inválido')) {
        Utilidades.mensaje('error', 'Error', 'El monto tiene formato incorrecto');
        return false;
    }

    // Validar concepto
    if (!Validaciones.keyUp(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]{3,100}$/,
        document.getElementById("concepto"),
        document.getElementById("concepto").nextElementSibling,
        'Concepto inválido')) {
        Utilidades.mensaje('error', 'Error', 'El concepto debe tener entre 3 y 100 caracteres');
        return false;
    }

    // Validar que la caja exista
    let select = document.getElementById("mes_select");
    if (!Validaciones.select(select.id)) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar una caja');
        return false;
    }

    let valido = await verificarClaveForanea(select.value);
    if (!valido) {
        Utilidades.mensaje('error', 'Error', 'La caja seleccionada no existe');
        return false;
    }

    // Validar fondos suficientes
    if (verificarMontoExcedido()) {
        Utilidades.mensaje('error', 'Error', 'El monto supera el fondo disponible');
        return false;
    }

    return true;
}

async function validarEnvioReponerCaja() {
    let montoInput = document.getElementById("monto_reponer");
    if (!montoInput.value || parseFloat(montoInput.value) === 0) {
        Validaciones.mostrarError(montoInput, 'El monto es obligatorio');
        Utilidades.mensaje('error', 'Error', 'Debe ingresar un monto');
        return false;
    }

    if (!Validaciones.keyUp(/^\d{0,12}([.,]\d{0,2})?$/,
        montoInput, montoInput.nextElementSibling.nextElementSibling,
        'Formato inválido')) {
        Utilidades.mensaje('error', 'Error', 'El monto tiene formato incorrecto');
        return false;
    }

    let select = document.getElementById("mes_select");
    if (!Validaciones.select(select.id)) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar una caja');
        return false;
    }

    let valido = await verificarClaveForanea(select.value);
    if (!valido) {
        Utilidades.mensaje('error', 'Error', 'La caja seleccionada no existe');
        return false;
    }

    if (verificarReposicionInsuficiente()) {
        Utilidades.mensaje('error', 'Error', 'El monto no cubre todos los gastos pendientes');
        return false;
    }

    return true;
}

function verificarMontoExcedido() {
    let fondo = parseFloat(document.getElementById("fondos_caja").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio").value.replace(',', '.'));
    return monto > fondo;
}

function verificarReposicionInsuficiente() {
    let gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio_reponer").value.replace(',', '.'));
    return monto < gastado;
}

async function verificarReposicionExcedente() {
    let gastado = parseFloat(document.getElementById("fondos_gastados").textContent.split("Bs")[0]);
    let montoInput = document.getElementById("monto_reponer");
    let monto = (montoInput.getAttribute("monto") === "bs")
        ? parseFloat(montoInput.value.replace(',', '.'))
        : parseFloat(document.getElementById("monto_cambio_reponer").value.replace(',', '.'));
    return monto > gastado;
}

async function verificarClaveForanea(valor) {
    let respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: 'caja_chica',
        nombre_clave: 'id_caja_chica',
        valor: valor
    });
    return respuesta.estatus === true;
}

// Extensión de Validaciones para fecha (si no existe)
// if (!Validaciones.fecha) {
//     Validaciones.fecha = function(input, errorElement, mostrarMensaje = false) {
//         let valor = input.value;
//         let regex = /^\d{4}-\d{2}-\d{2}$/;
//         if (!regex.test(valor)) {
//             input.classList.add('is-invalid');
//             input.classList.remove('is-valid');
//             if (errorElement) errorElement.textContent = 'Formato debe ser YYYY-MM-DD';
//             return false;
//         }
//         let [anio, mes, dia] = valor.split('-').map(Number);
//         let fecha = new Date(anio, mes-1, dia);
//         if (fecha.getFullYear() !== anio || fecha.getMonth() !== mes-1 || fecha.getDate() !== dia) {
//             input.classList.add('is-invalid');
//             input.classList.remove('is-valid');
//             if (errorElement) errorElement.textContent = 'Fecha inválida';
//             return false;
//         }
//         if (anio < 2000) {
//             input.classList.add('is-invalid');
//             input.classList.remove('is-valid');
//             if (errorElement) errorElement.textContent = 'Año debe ser ≥ 2000';
//             return false;
//         }
//         input.classList.add('is-valid');
//         input.classList.remove('is-invalid');
//         if (errorElement) errorElement.textContent = '';
//         return true;
//     };
// }