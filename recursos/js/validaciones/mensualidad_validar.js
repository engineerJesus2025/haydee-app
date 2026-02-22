/**
 * mensualidad_validar.js
 * Validaciones para el módulo de Mensualidad
 * Dependencias: validaciones.js, utilidades.js
 */

$(document).ready(function() {
    const selectFecha = document.getElementById('mes_select_asignar');
    const porcentaje = document.getElementById('porcentaje_demora');
    const diaLimite = document.getElementById('dia_limite');
    const boton = document.getElementById('boton_formulario');

    // Validación del select de fecha
    $(selectFecha).on('change', async function() {
        const fecha = this.selectedOptions[0]?.id;
        if (!fecha) return;

        if (!/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(fecha)) {
            Validaciones.mostrarError(this, 'Formato de fecha inválido');
            return;
        }

        // Verificar existencia en el backend
        const valido = await verificarFechaPresupuesto(fecha);
        if (valido) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            if (this.nextElementSibling) this.nextElementSibling.textContent = '';
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            if (this.nextElementSibling) this.nextElementSibling.textContent = 'La fecha seleccionada no tiene presupuestos asociados';
        }
    });

    // Validación de porcentaje de interés (opcional, entre 0 y 100)
    $(porcentaje).on('keypress', function(e) {
        Validaciones.keyPress(/^[\d.]$/, e);
    });
    $(porcentaje).on('keyup', function() {
        Validaciones.keyUp(/^\d{0,2}(\.\d{0,2})?$/, this, this.nextElementSibling, 'Porcentaje inválido (ej: 5 o 5.5)');
    });

    // Validación de día límite (1-31)
    $(diaLimite).on('keypress', function(e) {
        Validaciones.keyPress(/^[0-9]$/, e);
    });
    $(diaLimite).on('keyup', function() {
        Validaciones.keyUp(/^([1-9]|[12]\d|3[01])$/, this, this.nextElementSibling, 'Día límite inválido (1-31)');
    });

    // Validación al enviar
    $(boton).on('click', async function(e) {
        e.preventDefault();
        const accion = botonFormulario.dataset.op || "Registrar";
        if (await validarFormularioCompleto()) {
           Swal.fire({
            title: "¿Estás seguro?",
            text: `¿Desea ${accion.toLowerCase()} esta mensualidad?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#1b8a40",
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    if (accion === "Registrar") registrarMensualidad();
                    else editarMensualidad();
                }
            }); 

            return true;
        }
        return false;
    });
});

async function verificarFechaPresupuesto(fecha) {
    const respuesta = await Utilidades.validar('validar_fecha_presupuesto', { fecha });
    return respuesta.estatus;
}

async function validarFormularioCompleto() {
    const select = document.getElementById('mes_select_asignar');
    if (!select.value) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar una fecha');
        return false;
    }
    if (!await verificarFechaPresupuesto(select.selectedOptions[0].id)) {
        Utilidades.mensaje('error', 'Error', 'La fecha seleccionada no es válida');
        return false;
    }

    const porcentaje = document.getElementById('porcentaje_demora');
    if (porcentaje.value && !/^\d{0,2}(\.\d{0,2})?$/.test(porcentaje.value)) {
        Utilidades.mensaje('error', 'Error', 'Porcentaje de interés inválido');
        return false;
    }

    const diaLimite = document.getElementById('dia_limite');
    if (diaLimite.value && !/^([1-9]|[12]\d|3[01])$/.test(diaLimite.value)) {
        Utilidades.mensaje('error', 'Error', 'Día límite inválido (debe ser 1-31)');
        return false;
    }

    // Verificar que cada fila tenga al menos un checkbox marcado
    const filas = document.querySelectorAll('#tabla_mensualidad_asignar tbody tr');
    let filasSinCheck = [];
    filas.forEach(fila => {
        const checks = fila.querySelectorAll('input[type="checkbox"]:checked');
        if (checks.length === 0) {
            // Obtener el número de apartamento (primera celda)
            const nroApto = fila.cells[0]?.textContent.trim() || fila.id;
            filasSinCheck.push(nroApto);
        }
    });
    if (filasSinCheck.length > 0) {
        let mensaje = 'Los siguientes apartamentos no tienen ningún concepto marcado: ' + filasSinCheck.join(', ');
        Utilidades.mensaje('error', 'Error', mensaje);
        return false;
    }

    return true;
}