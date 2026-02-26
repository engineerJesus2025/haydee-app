let data_table;
let id_modificar;

const modal = new bootstrap.Modal("#modal_proveedores");
const form = document.querySelector("#form_proveedores");

// Exponer funciones para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const parametros = (data) => { data.operacion = 'consulta'; };
    const estructura = [
        { data: 'nombre_proveedor' },
        { data: 'servicio' },
        { data: 'rif' },
        { data: 'direccion' },
        {
            data: 'id_proveedor',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    ${window.permiso_modificar ? `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configuracion = (row, data) => {
        row.querySelector('.modificar')?.addEventListener('click', prepararFormulario);
        row.querySelector('.eliminar')?.addEventListener('click', (e) => {
            const id = e.currentTarget.value;
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e01d22',
                confirmButtonText: 'Eliminar'
            }).then(result => result.isConfirmed && eliminar(id));
        });
    };

    data_table = Utilidades.crearDataTable('tabla_proveedores', estructura, parametros, configuracion);
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('operacion', 'registrar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Proveedor registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'consultar_proveedor');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el proveedor.');
        return;
    }

    const data = respuesta.datos;

    form.querySelector('#nombre_proveedor').value = data.nombre_proveedor;
    form.querySelector('#servicio').value = data.servicio;
    const tipoDoc = data.rif.charAt(0);
    const rifNum = data.rif.slice(1);
    form.querySelector('#tipo_documento').value = tipoDoc;
    form.querySelector('#rif').value = rifNum;
    form.querySelector('#direccion').value = data.direccion;

    document.getElementById('titulo_modal').textContent = 'Modificar Proveedor';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    const tipoDoc = datos.get('tipo_documento');
    const rifNum = datos.get('rif');
    datos.set('rif', tipoDoc + rifNum);
    datos.set('id_proveedor', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Proveedor actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_proveedor', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Proveedor eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_proveedores').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Proveedor';
    form.querySelector('#boton_formulario').textContent = 'Registrar';
    delete form.querySelector('#boton_formulario').dataset.id;
});