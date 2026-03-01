// apartamentos_ajax.js
let data_table_apartamentos;
let data_table_habitantes;
let id_apartamento_seleccionado;
let nro_apartamento_an;

const modalApartamento = new bootstrap.Modal("#modal_apartamentos");
const modalVistaPrevia = new bootstrap.Modal("#modal_vista_previa");
const modalHabitante = new bootstrap.Modal("#modal_habitantes");
const modalVistaPreviaHabitantes = new bootstrap.Modal("#modal_vista_previa_habitantes");

const formApartamento = document.querySelector("#form_apartamentos");
const formHabitantes = document.querySelector("#form_habitantes");
const btnFormulario = document.querySelector("#boton_formulario");
const btnFormularioHabitante = document.querySelector("#boton_formulario_habitantes");

// Permisos (vienen desde PHP)
window.permiso_modificar = document.querySelector("#permiso_modificar")?.value === "1";
window.permiso_eliminar = document.querySelector("#permiso_eliminar")?.value === "1";
window.permiso_modificar_habitantes = window.permiso_modificar; // o podrían venir separados
window.permiso_eliminar_habitantes = window.permiso_eliminar;

// ============================================
// APARTAMENTOS
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    consultarApartamentos();
});

async function consultarApartamentos() {
    const parametros = (data) => { data.operacion = 'consulta'; };
    const estructura = [
        { data: 'nro_apartamento', render: data => `Nro: ${data}` },
        { data: 'porcentaje_participacion', render: data => data + '%' },
        { data: 'gas', render: data => data == 1 ? 'TIENE' : 'NO TIENE' },
        { data: 'agua', render: data => data == 1 ? 'TIENE' : 'NO TIENE' },
        { data: 'alquilado', render: data => data == 1 ? 'SI' : 'NO' },
        {
            data: 'id_apartamento',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-primary btn-sm vista-previa" value="${id}"><i class="bi bi-people-fill"></i></button>
                    ${window.permiso_modificar ? `<button class="btn btn-success btn-sm modificar" value="${id}"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar ? `<button class="btn btn-danger btn-sm eliminar" value="${id}"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const configPost = (row, data) => {
        const option = new Option(data.nro_apartamento, data.id_apartamento);
        document.getElementById("apartamento_id").add(option);

        row.querySelector('.vista-previa')?.addEventListener('click', mostrarVistaPrevia);
        row.querySelector('.modificar')?.addEventListener('click', prepararEdicion);
        row.querySelector('.eliminar')?.addEventListener('click', (e) => {
            const id = e.currentTarget.value;
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e01d22',
                confirmButtonText: 'Eliminar'
            }).then(result => result.isConfirmed && eliminarApartamento(id));
        });
    };

    data_table_apartamentos = Utilidades.crearDataTable('tabla_apartamentos', estructura, parametros, configPost);
}

async function registrarApartamento() {
    const datos = new FormData(formApartamento);
    datos.set('operacion', 'registrar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modalApartamento.hide();
        data_table_apartamentos.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Apartamento registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararEdicion(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_apartamento', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el apartamento.');
        return;
    }

    const apto = respuesta.apartamento;
    formApartamento.querySelector('#nro_apartamento').value = apto.nro_apartamento;
    formApartamento.querySelector('#porcentaje_participacion').value = apto.porcentaje_participacion;
    formApartamento.querySelector('#gas').value = apto.gas;
    formApartamento.querySelector('#agua').value = apto.agua;
    formApartamento.querySelector('#alquilado').value = apto.alquilado;

    nro_apartamento_an = apto.nro_apartamento;

    document.getElementById('titulo_modal').textContent = 'Modificar Apartamento';
    btnFormulario.textContent = 'Guardar Cambios';
    btnFormulario.dataset.id = id;

    modalApartamento.show();
}

async function modificarApartamento() {
    const id = btnFormulario.dataset.id;
    const datos = new FormData(formApartamento);
    datos.set('id_apartamento', id);
    datos.set('operacion', 'modificar');

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modalApartamento.hide();
        data_table_apartamentos.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Apartamento actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminarApartamento(id) {
    const datos = new FormData();
    datos.append('id_apartamento', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        data_table_apartamentos.ajax.reload(null, false);
        Utilidades.mensaje('success', 'Éxito', 'Apartamento eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

document.getElementById('modal_apartamentos').addEventListener('hide.bs.modal', () => {
    formApartamento.reset();
    delete btnFormulario.dataset.id;
    document.getElementById('titulo_modal').textContent = 'Registrar Apartamento';
    btnFormulario.textContent = 'Guardar';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
});

// ============================================
// VISTA PREVIA DE APARTAMENTO Y HABITANTES
// ============================================
async function mostrarVistaPrevia(e) {
    const id = e.currentTarget.value;
    id_apartamento_seleccionado = id;

    // Cargar datos del apartamento actual
    const datosApto = new FormData();
    datosApto.append('id_apartamento', id);
    datosApto.append('operacion', 'consulta_especifica');
    const respApto = await Utilidades.query(datosApto);
    if (respApto?.estatus) {
        const apto = respApto.apartamento;
        document.getElementById('apt_nro').textContent = apto.nro_apartamento || 'N/A';
        document.getElementById('apt_porcentaje').textContent = apto.porcentaje_participacion || '0';
        document.getElementById('apt_gas').textContent = apto.gas == 1 ? 'Sí' : 'No';
        document.getElementById('apt_agua').textContent = apto.agua == 1 ? 'Sí' : 'No';
        document.getElementById('apt_alquilado').textContent = apto.alquilado == 1 ? 'Sí' : 'No';
    } else {
        // Si no se puede cargar, mostrar valores por defecto
        document.getElementById('apt_nro').textContent = 'Error';
        document.getElementById('apt_porcentaje').textContent = '-';
        document.getElementById('apt_gas').textContent = '?';
        document.getElementById('apt_agua').textContent = '?';
        document.getElementById('apt_alquilado').textContent = '?';
    }

    // Inicializar o recargar tabla de habitantes
    if (!data_table_habitantes) {
        initTablaHabitantes();
    } else {
        data_table_habitantes.ajax.reload();
    }

    document.getElementById("apartamento_id").value = id_apartamento_seleccionado;
    modalVistaPrevia.show();
}

function initTablaHabitantes() {
    const estructura = [
        { data: 'nombre' },
        { data: 'apellido' },
        { data: 'cedula' },
        { data: 'nro_apartamento', render: data => `Nro: ${data}` },
        { data: 'tipo_vinculo' },
        {
            data: 'id_habitante',
            render: id => `
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-primary btn-sm vista-previa-habitante" value="${id}" title="Detalles"><i class="bi bi-eye-fill"></i></button>
                    ${window.permiso_modificar_habitantes ? `<button class="btn btn-success btn-sm modificar-habitante" value="${id}" title="modificar" data-bs-toggle="modal" data-bs-target="#modal_habitantes"><i class="bi bi-pencil"></i></button>` : ''}
                    ${window.permiso_eliminar_habitantes ? `<button class="btn btn-danger btn-sm eliminar-habitante" value="${id}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            `
        }
    ];

    const parametros = (data) => {
        data.operacion = 'consultar_habitantes';
        data.id_apartamento = id_apartamento_seleccionado;
    };

    const configPost = (row, data) => {
        row.querySelector('.vista-previa-habitante')?.addEventListener('click', mostrarVistaPreviaHabitante);
        row.querySelector('.modificar-habitante')?.addEventListener('click', prepararEdicionHabitante);
        row.querySelector('.eliminar-habitante')?.addEventListener('click', (e) => {
            const id = e.currentTarget.value;
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e01d22',
                confirmButtonText: 'Eliminar'
            }).then(result => result.isConfirmed && eliminarHabitante(id));
        });
    };

    data_table_habitantes = Utilidades.crearDataTable('tabla_habitantes', estructura, parametros, configPost);
}

// ============================================
// HABITANTES (CRUD)
// ============================================
async function registrarHabitante() {
    const datos = new FormData(formHabitantes);
    datos.set('operacion', 'registrar_habitantes');
    datos.set('apartamento_id', id_apartamento_seleccionado);

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modalHabitante.hide();
        data_table_habitantes.ajax.reload();
        Utilidades.mensaje('success', 'Éxito', 'Habitante registrado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo registrar.');
    }
}

async function prepararEdicionHabitante(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'consulta_especifica_habitante');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el habitante.');
        return;
    }

    const data = respuesta.datos;
    formHabitantes.querySelector('#nombre').value = data.nombre;
    formHabitantes.querySelector('#apellido').value = data.apellido;
    formHabitantes.querySelector('#tipo_cedula').value = data.cedula.charAt(0);
    formHabitantes.querySelector('#cedula').value = data.cedula.slice(1);
    formHabitantes.querySelector('#telefono').value = data.telefono;
    formHabitantes.querySelector('#correo').value = data.correo;
    formHabitantes.querySelector('#fecha_nacimiento').value = data.fecha_nacimiento;
    formHabitantes.querySelector('#sexo').value = data.sexo;
    formHabitantes.querySelector('#apartamento_id').value = data.apartamento_id;
    formHabitantes.querySelector('#tipo_vinculo').value = data.tipo_vinculo;

    // Guardar valores originales para comparar en validaciones
    cedula_an = data.cedula;
    correo_an = data.correo;
    tipo_vinculo_an = data.tipo_vinculo;

    document.getElementById('titulo_modal_habitantes').textContent = 'Modificar Habitante';
    btnFormularioHabitante.textContent = 'Guardar Cambios';
    btnFormularioHabitante.dataset.id = id;
    formHabitantes.querySelector('#cedula').removeAttribute('disabled');
}

async function modificarHabitante() {
    const id = btnFormularioHabitante.dataset.id;
    const datos = new FormData(formHabitantes);
    datos.set('id_habitante', id);
    datos.set('operacion', 'modificar_habitantes');
    datos.set('apartamento_id', id_apartamento_seleccionado);

    const respuesta = await Utilidades.query(datos, true);
    if (respuesta?.estatus) {
        modalHabitante.hide();
        data_table_habitantes.ajax.reload();
        Utilidades.mensaje('success', 'Éxito', 'Habitante actualizado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo actualizar.');
    }
}

async function eliminarHabitante(id) {
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'eliminar_habitantes');

    const respuesta = await Utilidades.query(datos);
    if (respuesta?.estatus) {
        data_table_habitantes.ajax.reload();
        Utilidades.mensaje('success', 'Éxito', 'Habitante eliminado correctamente.');
    } else {
        Utilidades.mensaje('error', 'Error', respuesta?.mensaje || 'No se pudo eliminar.');
    }
}

async function mostrarVistaPreviaHabitante(e) {
    const id = e.currentTarget.value;
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'consulta_especifica_habitante');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta?.estatus) {
        Utilidades.mensaje('error', 'Error', 'No se pudo cargar el detalle del habitante.');
        return;
    }

    const data = respuesta.datos;
    // Datos personales
    document.getElementById('vista_nombre').textContent = data.nombre || '';
    document.getElementById('vista_apellido').textContent = data.apellido || '';
    document.getElementById('vista_cedula').textContent = data.cedula || '';
    document.getElementById('vista_telefono').textContent = data.telefono || '';
    document.getElementById('vista_correo').textContent = data.correo || '';
    document.getElementById('vista_fecha_nacimiento').textContent = FormatoFechas.formatoDMA(data.fecha_nacimiento);
    document.getElementById('vista_sexo').textContent = data.sexo || '';
    // Datos del apartamento
    document.getElementById('vista_apartamento_nro').textContent = data.nro_apartamento ? `Nro ${data.nro_apartamento}` : 'No asignado';
    document.getElementById('vista_apartamento_porcentaje').textContent = data.porcentaje_participacion || '0';
    document.getElementById('vista_apartamento_gas').textContent = data.gas == 1 ? 'Sí' : 'No';
    document.getElementById('vista_apartamento_agua').textContent = data.agua == 1 ? 'Sí' : 'No';
    document.getElementById('vista_apartamento_alquilado').textContent = data.alquilado == 1 ? 'Sí' : 'No';

    modalVistaPreviaHabitantes.show();
}

// Eventos del modal de habitantes
document.getElementById('modal_habitantes').addEventListener('hide.bs.modal', () => {
    formHabitantes.reset();
    formHabitantes.querySelector('#cedula').setAttribute('disabled', '');
    delete btnFormularioHabitante.dataset.id;
    document.getElementById('titulo_modal_habitantes').textContent = 'Registrar Habitante';
    btnFormularioHabitante.textContent = 'Guardar';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    // Resetear variables globales
    cedula_an = null;
    correo_an = null;
    tipo_vinculo_an = null;
});

// Ajustar DataTable cuando se abre el modal de habitantes
document.getElementById('modal_vista_previa')?.addEventListener('shown.bs.modal', () => {
    if (data_table_habitantes) {
        data_table_habitantes.columns.adjust().draw();
    }
});

// Exponer funciones para el validador (si existe, ya no me acuerdo)
window.registrarApartamento = registrarApartamento;
window.modificarApartamento = modificarApartamento;
window.eliminarApartamento = eliminarApartamento;
window.prepararEdicion = prepararEdicion;
window.registrarHabitante = registrarHabitante;
window.modificarHabitante = modificarHabitante;
window.eliminarHabitante = eliminarHabitante;
window.prepararEdicionHabitante = prepararEdicionHabitante;