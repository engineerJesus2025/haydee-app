let tabla_anio_fiscal;
let id_modificar;

const modal = new bootstrap.Modal("#modal_anio_fiscal");
const form = document.querySelector("#form_anio_fiscal");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.prepararFormulario = prepararFormulario;

document.addEventListener('DOMContentLoaded', consultar);

document.getElementById('header-toggle')?.addEventListener('click', () => {
    setTimeout(() => {
        tabla_anio_fiscal?.columns.adjust().draw();
    }, 450);
});

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const parametros = (data) => { data.operacion = 'consultar_anios_fiscales'; };
    const estructura = [
        {
            data: 'estado',
            render: estado => {
                const span = document.createElement('span');
                span.className = estado === 'Cerrada' ? 'badge bg-secondary' : 'badge bg-primary';
                span.textContent = estado;
                return span.outerHTML;
            }
        },
        {
            data: 'fecha_inicio',
            render: fecha => FormatoFechas.formatoDMA(fecha)
        },
        {
            data: null,
            render: row => row.estado === 'Cerrada' ? FormatoFechas.formatoDMA(row.fecha_cierre) : 'Aún sin cerrar'
        },
        { data: 'descripcion' },
        {
            data: 'id_anio_fiscal',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    ${window.permiso_modificar ? `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configPost = (row, data) => {
        row.setAttribute('id', `fila-${data.id_anio_fiscal}`);
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

    tabla_anio_fiscal = Utilidades.crearDataTable(
        'tabla_anio_fiscal',
        estructura,
        parametros,
        configPost
    );
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;

    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el año fiscal.');
        return;
    }

    const data = respuesta.datos;

    form.querySelector('#fecha_inicio').value = data.fecha_inicio;
    form.querySelector('#fecha_cierre').value = data.fecha_cierre;
    form.querySelector('#estado').value = data.estado;
    form.querySelector('#descripcion').value = data.descripcion;

    document.getElementById('titulo_modal').textContent = 'Modificar Año Fiscal';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    // Habilitar campos deshabilitados en registro
    form.querySelector('#fecha_cierre').removeAttribute('disabled');
    form.querySelector('#estado').removeAttribute('disabled');

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const datos = new FormData(form);
    datos.set('id_anio_fiscal', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_anio_fiscal', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        tabla_anio_fiscal.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Año fiscal eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_anio_fiscal').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('titulo_modal').textContent = 'Registrar Año Fiscal';
    form.querySelector('#boton_formulario').textContent = 'Guardar';
    delete form.querySelector('#boton_formulario').dataset.id;

    // Deshabilitar campos de cierre y estado en registro
    form.querySelector('#fecha_cierre').setAttribute('disabled', '');
    form.querySelector('#estado').setAttribute('disabled', '');
});