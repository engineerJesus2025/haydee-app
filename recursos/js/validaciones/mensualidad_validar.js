document.addEventListener("DOMContentLoaded", function() {
    const selectFecha = document.getElementById('mes_select_asignar');
    const inputPorcentaje = document.getElementById('porcentaje_demora');
    const inputDiaLimite = document.getElementById('dia_limite');
    const boton = document.getElementById('boton_formulario');

    if (selectFecha) {
        selectFecha.addEventListener('change', async function() {
            const fecha = this.selectedOptions[0]?.id;
            if (!fecha) return;

            if (!/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(fecha)) {
                EstadoInputs.marcarError(this, 'Formato de fecha inválido');
                return;
            }

            const formData = new FormData();
            formData.append('validar', 'validar_fecha_presupuesto');
            formData.append('fecha', fecha);

            const respuesta = await Peticiones.enviar(formData, "", false);
            if (respuesta.estatus) {
                EstadoInputs.marcarExito(this);
            } else {
                EstadoInputs.marcarError(this, 'La fecha seleccionada no tiene presupuestos asociados');
            }
        });
    }

    if (inputPorcentaje) {
        inputPorcentaje.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasPorcentaje));
        inputPorcentaje.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.porcentaje, 'Porcentaje inválido (ej: 5 o 5.5)'));
    }

    if (inputDiaLimite) {
        inputDiaLimite.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasNumeros));
        inputDiaLimite.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.diaMes, 'Día límite inválido (1-31)'));
    }

    if (boton) {
        boton.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.op || "Registrar";
            
            if (await validarFormularioCompleto()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} esta mensualidad?`,
                    "question", 
                    () => {
                        accion === "Registrar" ? registrarMensualidad() : modificarMensualidad();
                    }
                );
            }
        });
    }
});

async function validarFormularioCompleto() {
    const select = document.getElementById('mes_select_asignar');
    if (!select.value) {
        Alertas.mostrar('error', 'Error', 'Debe seleccionar una fecha');
        return false;
    }
    
    // Verificación final del presupuesto
    const formData = new FormData();
    formData.append('validar', 'validar_fecha_presupuesto');
    formData.append('fecha', select.selectedOptions[0].id);
    const validacionFecha = await Peticiones.enviar(formData, "", false);

    if (!validacionFecha.estatus) {
        Alertas.mostrar('error', 'Error', 'La fecha seleccionada no es válida');
        return false;
    }

    const inputPorcentaje = document.getElementById('porcentaje_demora');
    if (inputPorcentaje.value && !Patrones.porcentaje.test(inputPorcentaje.value)) {
        Alertas.mostrar('error', 'Error', 'Porcentaje de interés inválido');
        return false;
    }

    const inputDiaLimite = document.getElementById('dia_limite');
    if (inputDiaLimite.value && !Patrones.diaMes.test(inputDiaLimite.value)) {
        Alertas.mostrar('error', 'Error', 'Día límite inválido (debe ser 1-31)');
        return false;
    }

    return true;
}