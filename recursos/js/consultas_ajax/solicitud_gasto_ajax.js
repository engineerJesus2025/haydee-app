let data_table;
let id_modificar;
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar")) || 1;

const modal = new bootstrap.Modal("#modal_solicitud_gasto");
const form = document.querySelector("#form_solicitud_gasto");

// Exponer funciones necesarias para el validador
window.registrar = registrar;
window.modificar = modificar;
window.buscarPresupuesto = buscarPresupuesto;

document.addEventListener('DOMContentLoaded', () => {
    consultar();
    cargarMesesYAniosConPresupuesto();
});

// ============================================
// CONSULTA Y DATATABLE
// ============================================
async function consultar() {
    const parametros = (data) => { data.operacion = 'consulta'; };
    const estructura = [
        { data: 'fecha_reporte', render: FormatoFechas.formatoDMA },
        { data: 'descripcion_necesidad' },
        { data: 'nombre_solicitante' },
        { 
            data: 'monto_estimado',
            render: data => `Bs. ${parseFloat(data).toFixed(2)}` 
        },
        { data: 'estado' },
        { 
            data: 'prioridad',
            render: prioridad => {
                const map = { '1': 'success', '2': 'warning', '3': 'danger' };
                return `<span class="badge bg-${map[prioridad] || 'secondary'}">${prioridad}</span>`;
            }
        },
        {
            data: 'id_solicitud',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    ${window.permiso_editar ? `<button class="btn btn-success btn-sm editar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configPost = (row, data) => {
        row.querySelector('.editar')?.addEventListener('click', prepararFormulario);
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

    data_table = Utilidades.crearDataTable('tabla_solicitud_gasto', estructura, parametros, configPost);
}

// ============================================
// OPERACIONES CRUD
// ============================================
async function registrar() {
    const datos = new FormData(form);
    datos.set('operacion', 'registrar');

    const disponible = await consultarPresupuestoDisponible(datos.get('presupuesto_id'));
    if (disponible === null) return;
    if (parseFloat(datos.get('monto_estimado')) > disponible) {
        Utilidades.mensaje('error', 'Presupuesto insuficiente', `Solo hay Bs. ${disponible.toFixed(2)} disponibles.`);
        return;
    }
    datos.set('estado', 'Pendiente');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Solicitud registrada.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararFormulario(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar la solicitud.');
        return;
    }

    const data = respuesta.datos;
    console.log(data)
    await cargarMesesYAniosConPresupuesto(); // asegurar selects

    form.querySelector('#selector_mes').value = data.mes;
    form.querySelector('#selector_anio').value = data.anio;
    await buscarPresupuesto(); // para llenar info

    form.querySelector('#fecha').value = data.fecha_reporte;
    form.querySelector('#descripcion').value = data.descripcion_necesidad;
    form.querySelector('#nombre').value = data.nombre_solicitante;
    form.querySelector('#monto_estimado').value = data.monto_estimado;
    form.querySelector('#monto_estimado').dataset.original = data.monto_estimado;
    form.querySelector('#prioridad').value = data.prioridad;

    document.getElementById('presupuesto_total').textContent = 
        `Bs. ${parseFloat(data.monto_presupuesto_total).toFixed(2) || '-'}`;
    

    form.querySelector('#presupuesto_id').value = data.presupuesto_id;

    document.getElementById('titulo_modal').textContent = 'Modificar Solicitud';
    form.querySelector('#boton_formulario').textContent = 'Guardar Cambios';
    form.querySelector('#boton_formulario').dataset.id = id;

    modal.show();
}

async function modificar() {
    const id = form.querySelector('#boton_formulario').dataset.id;
    const montoNuevo = parseFloat(form.querySelector('#monto_estimado').value);
    const montoOriginal = parseFloat(form.querySelector('#monto_estimado').dataset.original) || 0;
    const presupuestoId = form.querySelector('#presupuesto_id').value;

    const disponible = await consultarPresupuestoDisponible(presupuestoId);
    if (disponible === null) return;
    const disponibleReal = disponible + montoOriginal;
    if (montoNuevo > disponibleReal) {
        Utilidades.mensaje('error', 'Presupuesto insuficiente', `Solo hay Bs. ${disponibleReal.toFixed(2)} disponibles.`);
        return;
    }

    const datos = new FormData(form);
    datos.set('id_solicitud', id);
    datos.set('operacion', 'modificar');
    datos.set('estado', 'Pendiente');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modal.hide();
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Solicitud actualizada.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_solicitud', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        data_table.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Solicitud eliminada.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
async function cargarMesesYAniosConPresupuesto() {
    const datos = new FormData();
    datos.append('operacion', 'meses_anios_con_presupuesto');
    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudieron cargar los períodos.');
        return;
    }

    const selectorMes = document.getElementById('selector_mes');
    const selectorAnio = document.getElementById('selector_anio');
    selectorMes.innerHTML = '<option value="" hidden>Seleccione mes</option>';
    selectorAnio.innerHTML = '<option value="" hidden>Seleccione año</option>';

    const meses = [...new Set(respuesta.data.map(p => p.mes))].sort((a,b)=>a-b);
    const anios = [...new Set(respuesta.data.map(p => p.anio))].sort((a,b)=>b-a);

    meses.forEach(mes => {
        const op = document.createElement('option');
        op.value = mes;
        op.textContent = FormatoFechas.nombreMes(mes);
        selectorMes.appendChild(op);
    });

    anios.forEach(anio => {
        const op = document.createElement('option');
        op.value = anio;
        op.textContent = anio;
        selectorAnio.appendChild(op);
    });
}

async function buscarPresupuesto() {
    const mes = document.getElementById('selector_mes').value;
    const anio = document.getElementById('selector_anio').value;
    if (!mes || !anio) return;

    const datos = new FormData();
    datos.append('operacion', 'buscar_presupuesto_por_mes_anio');
    datos.append('mes', mes);
    datos.append('anio', anio);
    const respuesta = await Utilidades.query(datos);

    if (respuesta?.estatus) {
        document.getElementById('presupuesto_total').textContent = 
            `Bs. ${parseFloat(respuesta.monto_presupuesto_total).toFixed(2)}`;
        document.getElementById('presupuesto_disponible').textContent = 
            `Bs. ${parseFloat(respuesta.disponible).toFixed(2)}`;
        document.getElementById('presupuesto_id').value = respuesta.id_presupuesto;
        document.getElementById('info_presupuesto').style.display = 'block';
        document.getElementById('campos_formulario_completo').style.display = 'block';
    } else {
        Utilidades.mensaje('error', 'Atención', respuesta?.mensaje || 'No hay presupuesto para ese período.');
        document.getElementById('info_presupuesto').style.display = 'none';
        document.getElementById('campos_formulario_completo').style.display = 'none';
        document.getElementById('presupuesto_id').value = '';
    }
}

async function consultarPresupuestoDisponible(presupuestoId) {
    const datos = new FormData();
    datos.append('operacion', 'consultar_presupuesto');
    datos.append('presupuesto_id', presupuestoId);
    const respuesta = await Utilidades.query(datos);
    return respuesta?.estatus ? parseFloat(respuesta.disponible) : null;
}

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.getElementById('modal_solicitud_gasto').addEventListener('hide.bs.modal', () => {
    form.reset();
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('info_presupuesto').style.display = 'none';
    document.getElementById('campos_formulario_completo').style.display = 'none';
    document.getElementById('presupuesto_id').value = '';
    document.getElementById('titulo_modal').textContent = 'Registrar Solicitud';
    form.querySelector('#boton_formulario').textContent = 'Registrar';
    delete form.querySelector('#boton_formulario').dataset.id;
});

document.getElementById('selector_mes').addEventListener('change', buscarPresupuesto);
document.getElementById('selector_anio').addEventListener('change', buscarPresupuesto);