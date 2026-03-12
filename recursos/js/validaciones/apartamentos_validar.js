/**
 * apartamentos_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Alertas.js, Peticiones.js, FormatoFechas.js
 */

// Variables globales para comparar valores originales (deben venir de tu vista/AJAX)
let nro_apartamento_an = null;
let cedula_an = null;
let correo_an = null;
let tipo_vinculo_an = null;

document.addEventListener("DOMContentLoaded", function() {

    // ============================================
    // VALIDACIONES DE APARTAMENTOS (Tiempo Real)
    // ============================================
    const inputNroApto = document.getElementById('nro_apartamento');
    if (inputNroApto) {
        inputNroApto.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasApartamento));
        inputNroApto.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.nroApartamento, 'Número inválido (máx 3 dígitos)'));
        inputNroApto.addEventListener('blur', async function() {
        // Usamos la variable global correcta de tu ajax
        const original = typeof nro_apartamento_an !== 'undefined' ? nro_apartamento_an : null;
        
        if (this.value === original || this.value === '') return;
        
        if (Patrones.nroApartamento.test(this.value)) {
            // Capturamos el ID del apartamento si estamos modificando
            const btn = document.getElementById('boton_formulario');
            const idApto = btn.getAttribute('id_modificar') || '';

            await Validador.verificarDuplicadoEnServidor(
                'nro_apartamento', 
                { 
                    nro_apartamento: this.value,
                    id_apartamento: idApto // <-- ¡Aquí está la magia! Le pasamos el ID
                }, 
                this, 
                'Este número ya está registrado'
            );
        }
    });
    }

    const inputPorcentaje = document.getElementById('porcentaje_participacion');
    if (inputPorcentaje) {
        inputPorcentaje.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasPorcentaje));
        inputPorcentaje.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.porcentaje, 'Porcentaje inválido (ej. 12.5)'));
    }

    // Selects básicos de apartamento
    ['gas', 'agua', 'alquilado'].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', function() {
                Validador.evaluarInput(this, /^[12]$/, 'Seleccione una opción válida');
            });
        }
    });

    // ============================================
    // VALIDACIONES DE HABITANTES (Tiempo Real)
    // ============================================
    const configHabitantes = [
        { id: 'nombre', patron: Patrones.textoCorto, msj: 'Solo letras, entre 3 y 30 caracteres', teclas: Patrones.teclasLetras },
        { id: 'apellido', patron: Patrones.textoCorto, msj: 'Solo letras, entre 3 y 30 caracteres', teclas: Patrones.teclasLetras },
        { id: 'cedula', patron: Patrones.cedula, msj: 'Cédula inválida (7-8 dígitos)', teclas: Patrones.teclasNumeros },
        { id: 'telefono', patron: Patrones.telefono, msj: 'Teléfono inválido (11 dígitos)', teclas: Patrones.teclasNumeros },
        { id: 'correo', patron: Patrones.correo, msj: 'Correo inválido', teclas: Patrones.teclasCorreo }
    ];

    configHabitantes.forEach(campo => {
        const input = document.getElementById(campo.id);
        if (input) {
            input.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, campo.teclas));
            input.addEventListener('keyup', e => Validador.evaluarInput(e.target, campo.patron, campo.msj));
        }
    });

    const inputFechaNac = document.getElementById('fecha_nacimiento');
    if (inputFechaNac) {
        inputFechaNac.addEventListener('keyup', function() { 
            Validador.evaluarFecha(this, 'Fecha inválida', { maxHoy: true }); 
        });
        inputFechaNac.addEventListener('change', function() { 
            Validador.evaluarFecha(this, 'Fecha inválida', { maxHoy: true }); 
        });
    }

    const selectTipoCedula = document.getElementById('tipo_cedula');
    const inputCedula = document.getElementById('cedula');
    if (selectTipoCedula && inputCedula) {
        selectTipoCedula.addEventListener('change', function() {
            if (!this.value) {
                EstadoInputs.marcarError(this, 'Seleccione un tipo de cédula');
                inputCedula.disabled = true;
            } else {
                EstadoInputs.marcarExito(this);
                inputCedula.disabled = false;
                inputCedula.value = '';
                EstadoInputs.limpiar(inputCedula);
            }
        });

        // Validar duplicado de Cédula al salir del input
        inputCedula.addEventListener('blur', async function() {
            if (!Patrones.cedula.test(this.value)) return;
            const cedulaCompleta = selectTipoCedula.value + this.value;
            if (cedulaCompleta === cedula_an) return;
            await Validador.verificarDuplicadoEnServidor('cedula', { cedula: cedulaCompleta }, this, 'Esta cédula ya está registrada');
        });
    }

    const inputCorreo = document.getElementById('correo');
    if (inputCorreo) {
        inputCorreo.addEventListener('blur', async function() {
            if (this.value === correo_an || !Patrones.correo.test(this.value)) return;
            await Validador.verificarDuplicadoEnServidor('correo', { correo: this.value }, this, 'Este correo ya está registrado');
        });
    }

    // Selects básicos de habitante
    ['sexo', 'tipo_vinculo'].forEach(id => {
        const select = document.getElementById(id);
        if (select) select.addEventListener('change', function() { Validador.evaluarSelect(this.id); });
    });

    const selectVinculo = document.getElementById('tipo_vinculo');
    const aptoId = document.getElementById('apartamento_id');
    
    if (selectVinculo && aptoId) {
        selectVinculo.addEventListener('change', async function() {
            if (!this.value || !aptoId.value) return;
            
            const formData = new FormData();
            formData.append('validar', 'tipo_vinculo');
            formData.append('tipo_vinculo', this.value);
            formData.append('apartamento_id', aptoId.value);

            const respuesta = await Peticiones.enviar(formData, "", false);
            if (respuesta.existe) {
                EstadoInputs.marcarError(this, 'Este apartamento ya tiene un propietario asignado.');
            } else {
                EstadoInputs.marcarExito(this);
            }
        });
    }

    if (aptoId) {
        aptoId.addEventListener('change', async function() {
            if (!this.value) return;
            await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'apartamentos', nombre_clave: 'id_apartamento', valor: this.value }, this, 'El apartamento no existe');
        });
    }

    // ============================================
    // ENVÍO DE FORMULARIOS
    // ============================================

    // Formulario Apartamento
    const btnFormApto = document.getElementById('boton_formulario');
    if (btnFormApto) {
        btnFormApto.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.id ? 'modificar' : 'Registrar';
            
            if (await validarEnvioApartamento(accion)) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Desea ${accion} este apartamento?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1b8a40',
                    confirmButtonText: 'Sí, ' + accion
                }).then(result => {
                    if (result.isConfirmed) accion === 'modificar' ? modificarApartamento() : registrarApartamento();
                });
            }
        });
    }

    // Formulario Habitante
    const btnFormHabitante = document.getElementById('boton_formulario_habitantes');
    if (btnFormHabitante) {
        btnFormHabitante.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.id ? 'modificar' : 'Registrar';
            
            if (await validarEnvioHabitante(accion)) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Desea ${accion} este habitante?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1b8a40',
                    confirmButtonText: 'Sí, ' + accion
                }).then(result => {
                    if (result.isConfirmed) accion === 'modificar' ? modificarHabitante() : registrarHabitante();
                });
            }
        });
    }
});

// ============================================
// FUNCIONES DE VALIDACIÓN FINALES
// ============================================

async function validarEnvioApartamento(accion) {
    let esValido = true;
    const inputNro = document.getElementById('nro_apartamento');

    if (!Validador.evaluarInput(inputNro, Patrones.nroApartamento, 'Número inválido')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('porcentaje_participacion'), Patrones.porcentaje, 'Porcentaje inválido')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('gas'), /^[12]$/, 'Seleccione una opción')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('agua'), /^[12]$/, 'Seleccione una opción')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('alquilado'), /^[12]$/, 'Seleccione una opción')) esValido = false;

    if (!esValido) {
        Alertas.mostrar('error', 'Error', 'Verifique los campos marcados en rojo.');
        return false;
    }

    if (nro_apartamento_an !== inputNro.value) {
        const duplicado = await Validador.verificarDuplicadoEnServidor('nro_apartamento', { nro_apartamento: inputNro.value }, inputNro, 'Este número ya está registrado.');
        if (!duplicado) return false;
    }

    return true;
}

async function validarEnvioHabitante(accion) {
    let esValido = true;

    // Evaluamos TODOS los campos para pintarlos si están mal
    if (!Validador.evaluarSelect('tipo_cedula')) esValido = false;
    const inputCedula = document.getElementById('cedula');
    if (!Validador.evaluarInput(inputCedula, Patrones.cedula, 'Cédula inválida')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('nombre'), Patrones.textoCorto, 'Nombre inválido')) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('apellido'), Patrones.textoCorto, 'Apellido inválido')) esValido = false;
    if (!Validador.evaluarFecha(document.getElementById('fecha_nacimiento'), 'Fecha inválida', { maxHoy: true })) esValido = false;
    if (!Validador.evaluarInput(document.getElementById('telefono'), Patrones.telefono, 'Teléfono inválido')) esValido = false;
    
    const inputCorreo = document.getElementById('correo');
    if (!Validador.evaluarInput(inputCorreo, Patrones.correo, 'Correo inválido')) esValido = false;
    
    if (!Validador.evaluarSelect('sexo')) esValido = false;
    
    const selectVinculo = document.getElementById('tipo_vinculo');
    if (!Validador.evaluarSelect('tipo_vinculo')) esValido = false;

    if (!esValido) {
        Alertas.mostrar('error', 'Error', 'Por favor, revise los campos marcados en rojo.');
        return false;
    }

    // Validaciones lógicas y de BD
    const selectTipoCed = document.getElementById('tipo_cedula');
    const cedulaCompletaActual = selectTipoCed.value + inputCedula.value;
    const inputApto = document.getElementById('apartamento_id');

    if (accion === 'Registrar' || cedulaCompletaActual !== cedula_an) {
        const dupCedula = await Validador.verificarDuplicadoEnServidor('cedula', { cedula: cedulaCompletaActual }, inputCedula, 'Cédula duplicada');
        if (!dupCedula) return false;
    }

    if (accion === 'Registrar' || inputCorreo.value !== correo_an) {
        const dupCorreo = await Validador.verificarDuplicadoEnServidor('correo', { correo: inputCorreo.value }, inputCorreo, 'Correo duplicado');
        if (!dupCorreo) return false;
    }

    if (accion === 'Registrar' || selectVinculo.value !== tipo_vinculo_an) {
        const formData = new FormData();
        formData.append('validar', 'tipo_vinculo');
        formData.append('tipo_vinculo', selectVinculo.value);
        formData.append('apartamento_id', inputApto.value);
        
        const respVinculo = await Peticiones.enviar(formData, "", false);
        if (respVinculo.existe) {
            EstadoInputs.marcarError(selectVinculo, 'Este apartamento ya tiene propietario');
            Alertas.mostrar('error', 'Error', 'Este apartamento ya tiene un propietario asignado.');
            return false;
        }
    }

    const aptoValido = await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'apartamentos', nombre_clave: 'id_apartamento', valor: inputApto.value }, inputApto, 'Apartamento no existe');
    if (!aptoValido) return false;

    if (typeof FormatoFechas !== 'undefined') {
        const edad = FormatoFechas.calcularEdad(document.getElementById('fecha_nacimiento').value);
        if (edad < 18) {
            EstadoInputs.marcarError(document.getElementById('fecha_nacimiento'), 'Debe ser mayor de edad');
            Alertas.mostrar('error', 'Error', 'El habitante debe ser mayor de 18 años.');
            return false;
        }
    }

    return true;
}