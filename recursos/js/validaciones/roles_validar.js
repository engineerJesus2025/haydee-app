// roles_validar.js
$(document).ready(function() {
    const nombreInput = $('#nombre');
    nombreInput.on('keypress', e => Validaciones.keyPress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/i, e));
    nombreInput.on('keyup', function() {
        Validaciones.campo(this, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/, 'Solo letras, entre 3 y 30 caracteres.');
    });

    nombreInput.on('blur', async function() {
        if ($(this).val() === nombre_anterior) return;
        if (!Validaciones.campo(this, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/, '')) return;

        const valido = await Validaciones.verificarExistencia(
            'nombre',
            { nombre: this.value },
            this,
            'Este nombre ya está registrado.'
        );
        if (valido) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        }
    });

    $("[name='permisos[]']").on('change', async function() {
        if (!this.checked) {
            this.classList.remove('is-invalid');
            const row = this.closest('.row');
            if (row && row.querySelector("[data-error='1']") == null) {
                row.lastElementChild.textContent = '';
            }
            return;
        }

        const datos = new FormData();
        datos.append('validar', 'validar_permisos_usuarios');
        datos.append('valor[]', this.value);

        const respuesta = await Utilidades.query(datos);
        if (respuesta?.estatus) {
            this.classList.remove('is-invalid');
            this.setAttribute('data-error', '0');
            const row = this.closest('.row');
            if (row && row.querySelector("[data-error='1']") == null) {
                row.lastElementChild.textContent = '';
            }
        } else {
            this.classList.add('is-invalid');
            this.setAttribute('data-error', '1');
            this.closest('.row').lastElementChild.textContent = 'Permiso inválido';
        }
    });

    $('#boton_formulario').on('click', async function(e) {
        e.preventDefault();
        const accion = this.dataset.id ? 'Editar' : 'Registrar';
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
                    accion === 'Editar' ? modificar() : registrar();
                }
            });
        }
    });
});

async function validarEnvio(accion) {
    const nombreInput = document.getElementById('nombre');
    if (!Validaciones.campo(nombreInput, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/, 'Nombre inválido')) {
        Utilidades.mensaje('error', 'Error', 'El nombre debe tener entre 3 y 30 letras.');
        return false;
    }

    const permisosSeleccionados = Array.from(document.querySelectorAll("[name='permisos[]']:checked"));
    if (permisosSeleccionados.length === 0) {
        Utilidades.mensaje('error', 'Error', 'Debe seleccionar al menos un permiso.');
        return false;
    }

    if (nombreInput.value !== nombre_anterior) {
        const valido = await Validaciones.verificarExistencia(
            'nombre',
            { nombre: nombreInput.value },
            nombreInput,
            'Este nombre ya está registrado.'
        );
        if (!valido) return false;
    }

    const idsPermisos = permisosSeleccionados.map(cb => cb.value);
    const datos = new FormData();
    datos.append('validar', 'validar_permisos_usuarios');
    idsPermisos.forEach(id => datos.append('valor[]', id));

    const respuesta = await Utilidades.query(datos);
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
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'Uno o más permisos no existen.');
        return false;
    }

    return true;
}