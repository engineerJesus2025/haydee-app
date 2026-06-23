/**
 * solicitud_gasto_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Peticiones.js, Alertas.js
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const inputMonto = document.querySelector('#monto_estimado');
    const inputDesc = document.querySelector('#descripcion_necesidad');
    const inputNombre = document.querySelector('#nombre_solicitante');
    const inputFecha = document.querySelector('#fecha_reporte');
    const selectPrioridad = document.querySelector('#prioridad');
    const selectMes = document.querySelector('#selector_mes');
    const selectAnio = document.querySelector('#selector_anio');

    // Validaciones en tiempo real
    inputMonto.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto));
    inputMonto.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.monto, 'Monto inválido'));

    inputDesc.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasAlfanumerico));
    inputDesc.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.descripcion, 'Descripción inválida'));

    inputNombre.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
    inputNombre.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.nombrePersona, 'Nombre inválido'));

    inputFecha.addEventListener('change', function() {
        EstadoInputs.limpiar(this);
        EstadoInputs.marcarExito(this); // Suponiendo que el navegador ya valida la fecha en el input type="date"
    });

    selectPrioridad.addEventListener('change', function() {
        Validador.evaluarInput(this, Patrones.prioridad, 'Seleccione una prioridad');
    });

    // Validaciones contra servidor (Presupuesto existente)
    selectMes.addEventListener('change', async function() {
        if (!this.value) return EstadoInputs.marcarError(this, 'Seleccione un mes');
        
        const valido = await Validador.verificarExistenciaEnServidor(
            'validar_mes', 
            { fecha: this.value }, 
            this, 
            'El mes no tiene presupuesto'
        );
        if (valido && typeof buscarPresupuesto === 'function') buscarPresupuesto();
    });

    selectAnio.addEventListener('change', async function() {
        if (!this.value) return EstadoInputs.marcarError(this, 'Seleccione un año');
        
        const valido = await Validador.verificarExistenciaEnServidor(
            'validar_anio', 
            { fecha: this.value }, 
            this, 
            'El año no tiene presupuesto'
        );
        if (valido && typeof buscarPresupuesto === 'function') buscarPresupuesto();
    });

    // Envío
    document.querySelector('#boton_formulario').addEventListener('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'modificar' : 'Registrar';
        if (await validarEnvio(accion)) {
            Alertas.confirmarAccion(
                "Confirmar Operación",
                `¿Está seguro que desea ${accion.toLowerCase()} esta solicitud?`,
                "question", 
                () => {
                    accion === 'modificar' ? modificar() : registrar();
                    if (result.isConfirmed) envio(accion);
                }
            );
        }
    });
});

async function validarEnvio(accion) {
    const elMes = document.querySelector('#selector_mes');
    const elAnio = document.querySelector('#selector_anio');
    const elMonto = document.querySelector('#monto_estimado');

    // 1. Validaciones de formato visual
    const vMes = Validador.evaluarInput(elMes, Patrones.digitos, 'Seleccione mes');
    const vAnio = Validador.evaluarInput(elAnio, Patrones.digitos, 'Seleccione año');
    const vNombre = Validador.evaluarInput(document.querySelector('#nombre_solicitante'), Patrones.nombrePersona, 'Nombre inválido');
    const vDesc = Validador.evaluarInput(document.querySelector('#descripcion_necesidad'), Patrones.descripcion, 'Descripción inválida');
    const vMonto = Validador.evaluarInput(elMonto, Patrones.monto, 'Monto inválido');
    const vPrioridad = Validador.evaluarInput(document.querySelector('#prioridad'), Patrones.prioridad, 'Seleccione prioridad');
    
    const elFecha = document.querySelector('#fecha_reporte');
    if (!elFecha.value) EstadoInputs.marcarError(elFecha, 'Seleccione fecha');

    if (!vMes || !vAnio || !vNombre || !vDesc || !vMonto || !vPrioridad || !elFecha.value) {
        Alertas.mostrar('error', 'Error', 'Por favor complete todos los campos correctamente.');
        return false;
    }

    // 2. Verificar existencia de mes y año en BD
    const mesValido = await Validador.verificarExistenciaEnServidor('validar_mes', { fecha: elMes.value }, elMes, 'El mes no tiene presupuesto');
    if (!mesValido) return false;

    const anioValido = await Validador.verificarExistenciaEnServidor('validar_anio', { fecha: elAnio.value }, elAnio, 'El año no tiene presupuesto');
    if (!anioValido) return false;

    // 3. Validar presupuesto disponible
    const montoNum = parseFloat(elMonto.value.replace(',', '.'));
    const elDisponible = document.querySelector('#presupuesto_disponible');
    const disponible = elDisponible ? parseFloat(elDisponible.textContent.replace('Bs. ', '').replace(',', '')) : 0;
    
    const montoOriginal = parseFloat(elMonto.dataset.original || 0);
    const disponibleReal = accion === 'modificar' ? disponible + montoOriginal : disponible;

    if (isNaN(disponibleReal) || isNaN(montoNum)) {
        Alertas.mostrar('error', 'Error', 'No se pudo verificar el presupuesto.');
        return false;
    }
    
    if (montoNum > disponibleReal) {
        Alertas.mostrar('error', 'Monto excedido', `El monto supera el disponible (Bs. ${disponibleReal.toFixed(2)}).`);
        return false;
    }

    return true;
}