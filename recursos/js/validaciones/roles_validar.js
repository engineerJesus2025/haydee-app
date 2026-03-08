/**
 * roles_validar.js
 * Dependencias: Validador.js, Patrones.js, EstadoInputs.js, Peticiones.js, Alertas.js
 */
document.addEventListener("DOMContentLoaded", function() {
    
    const inputNombre = document.querySelector('#nombre');
    const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");

    // Validación Nombre
    if (inputNombre) {
        inputNombre.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.textoCorto, 'Solo letras, entre 3 y 30 caracteres.'));

        inputNombre.addEventListener('blur', async function() {
            if (this.value === nombre_anterior) return;
            if (!Patrones.textoCorto.test(this.value)) return;

            await Validador.verificarDuplicadoEnServidor(
                'nombre',
                { nombre: this.value },
                this,
                'Este nombre ya está registrado.'
            );
        });
    }

    // Validación dinámica de permisos seleccionados
    checkboxesPermisos.forEach(cb => {
        cb.addEventListener('change', async function() {
            const row = this.closest('.row');
            
            if (!this.checked) {
                this.classList.remove('is-invalid');
                this.removeAttribute('data-error');
                // Si no hay más errores en la fila, limpiar el texto de error
                if (row && !row.querySelector("[data-error='1']")) {
                    row.lastElementChild.textContent = '';
                }
                return;
            }

            const datos = new FormData();
            datos.append('validar', 'validar_permisos_usuarios');
            datos.append('valor[]', this.value);

            const respuesta = await Peticiones.enviar(datos, "", false);
            
            if (respuesta?.estatus) {
                this.classList.remove('is-invalid');
                this.setAttribute('data-error', '0');
                if (row && !row.querySelector("[data-error='1']")) {
                    row.lastElementChild.textContent = '';
                }
            } else {
                this.classList.add('is-invalid');
                this.setAttribute('data-error', '1');
                if (row) row.lastElementChild.textContent = 'Permiso inválido';
            }
        });
    });

    // Envío del formulario
    const btnFormulario = document.querySelector('#boton_formulario');
    if (btnFormulario) {
        btnFormulario.addEventListener('click', async function(e) {
            e.preventDefault();
            const accion = this.dataset.id ? 'modificar' : 'Registrar';
            
            if (await validarEnvio(accion)) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Desea ${accion} este rol?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1b8a40',
                    confirmButtonText: 'Sí, ' + accion
                }).then(result => {
                    if (result.isConfirmed) {
                        accion === 'modificar' ? modificar() : registrar();
                    }
                });
            }
        });
    }
});

async function validarEnvio(accion) {
    const inputNombre = document.querySelector('#nombre');
    
    if (!Validador.evaluarInput(inputNombre, Patrones.textoCorto, 'Nombre inválido')) {
        Alertas.mostrar('error', 'Error', 'El nombre debe tener entre 3 y 30 letras.');
        return false;
    }

    const permisosSeleccionados = Array.from(document.querySelectorAll("[name='permisos[]']:checked"));
    if (permisosSeleccionados.length === 0) {
        Alertas.mostrar('error', 'Error', 'Debe seleccionar al menos un permiso.');
        return false;
    }

    if (inputNombre.value !== nombre_anterior) {
        const valido = await Validador.verificarDuplicadoEnServidor(
            'nombre',
            { nombre: inputNombre.value },
            inputNombre,
            'Este nombre ya está registrado.'
        );
        if (!valido) return false;
    }

    // Validación final masiva de permisos en el backend
    const datos = new FormData();
    datos.append('validar', 'validar_permisos_usuarios');
    permisosSeleccionados.forEach(cb => datos.append('valor[]', cb.value));

    const respuesta = await Peticiones.enviar(datos, "", false);
    
    if (!respuesta?.estatus) {
        if (respuesta.ids_no_encontrados) {
            respuesta.ids_no_encontrados.forEach(id => {
                const cb = document.querySelector(`[name='permisos[]'][value='${id}']`);
                if (cb) {
                    cb.classList.add('is-invalid');
                    cb.setAttribute('data-error', '1');
                }
            });
        }
        Alertas.mostrar('error', 'Error', respuesta?.mensaje || 'Uno o más permisos no existen.');
        return false;
    }

    return true;
}