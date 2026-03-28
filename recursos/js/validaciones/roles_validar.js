/**
 * roles_validar.js
 */
document.addEventListener("DOMContentLoaded", function() {
    
    const inputNombre = document.querySelector('#nombre');
    const checkboxesPermisos = document.querySelectorAll("[name='permisos[]']");
    const botonFormulario = document.getElementById("boton_formulario");

    // ============================================================
    // VALIDACIÓN EN TIEMPO REAL
    // ============================================================
    if (inputNombre) {
        inputNombre.addEventListener('keypress', e => Validador.bloquearTeclasInvalidas(e, Patrones.teclasLetras));
        inputNombre.addEventListener('keyup', e => Validador.evaluarInput(e.target, Patrones.textoCorto, 'Solo letras, entre 3 y 30 caracteres.'));

        inputNombre.addEventListener('change', async function() {
            if (typeof nombre_anterior !== 'undefined' && this.value.trim() === nombre_anterior.trim()) {
                EstadoInputs.marcarExito(this);
                return;
            }

            if (!Patrones.textoCorto.test(this.value)) return;

            const idRolActual = typeof id_modificar !== 'undefined' ? id_modificar : '';
            await Validador.verificarDatoUnico(
                'nombre',
                { nombre: this.value, id_rol: idRolActual },
                this,
                'Este nombre de rol ya está registrado.'
            );
        });
    }

    checkboxesPermisos.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                const contenedor = document.getElementById('tabla_permisos');
                if (contenedor) contenedor.classList.remove('border', 'border-danger');
            }
        });
    });

    // ============================================================
    // VALIDACIÓN AL ENVIAR EL FORMULARIO
    // ============================================================
    if (botonFormulario) {
        botonFormulario.addEventListener("click", function(e) {
            e.preventDefault();

            const esEdicion = this.hasAttribute('modificar');
            const accion = esEdicion ? 'modificar' : 'Registrar';
            if (validarEnvio(accion)) {
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

    function validarEnvio() {
        if (!Validador.evaluarInput(inputNombre, Patrones.textoCorto, 'Debe ingresar un nombre válido (3 a 30 letras).')) {
            Alertas.mostrar('error', 'Error', 'Nombre de rol inválido.');
            return false;
        }

        const permisosSeleccionados = Array.from(document.querySelectorAll("[name='permisos[]']:checked"));
        if (permisosSeleccionados.length === 0) {
            Alertas.mostrar('error', 'Faltan Permisos', 'Debe asignar al menos un permiso al rol.');
            const contenedor = document.getElementById('tabla_permisos');
            if (contenedor) contenedor.classList.add('border', 'border-danger');
            return false;
        }

        if (inputNombre.classList.contains('is-invalid')) {
            Alertas.mostrar('error', 'Error', 'El nombre del rol ya existe.');
            return false;
        }

        return true;
    }
});