/**
 * permisos_ajax.js
 * Gestión de Permisos - Peticiones AJAX
 */

let tablaPermisos;
let id_modificar = null;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

const modalPermiso = new bootstrap.Modal(document.getElementById("modal_permiso"));
const formulario = document.getElementById("form_permiso");
const botonFormulario = document.getElementById("boton_formulario");

document.addEventListener('DOMContentLoaded', () => {
    consultarPermisos();

    document.getElementById("modal_permiso")?.addEventListener("hide.bs.modal", resetModal);

    document.querySelector("#tabla_permisos tbody")?.addEventListener("click", manejarClickEnTabla);
});

async function consultarPermisos() {
    const columnas = [
        { data: "id_permiso" },
        { data: "accion" },
        {
            data: null,
            render: row => crearBotones(row.id_permiso).innerHTML
        }
    ];

    const parametros = (data) => { data.operacion = 'consultar'; };
    const postCreacion = (row, data) => {
        row.id = `fila-${data.id_permiso}`;
        row.lastElementChild.setAttribute('class','row justify-content-around');
    };

    tablaPermisos = Utilidades.crearDataTable('tabla_permisos', columnas, parametros, postCreacion);
}

function crearBotones(id) {
    let div = document.createElement('div');
    div.className = 'row justify-content-evenly';
    let html = `
        <button type="button" class="btn btn-success btn-sm col-3 modificar" title="modificar" value="${id}" data-bs-toggle="modal" data-bs-target="#modal_permiso">
            <i class="bi bi-pencil-square"></i>
        </button>`;
    if (permiso_eliminar == 1) {
        html += `
        <button type="button" class="btn btn-danger btn-sm col-3 eliminar" title="Eliminar" value="${id}">
            <i class="bi bi-trash"></i>
        </button>`;
    }
    div.innerHTML = html;
    return div;
}

function manejarClickEnTabla(e) {
    const boton = e.target.closest('button');
    if (!boton) return;
    const id = boton.value;

    if (boton.classList.contains('modificar')) {
        prepararEdicion(id);
    } else if (boton.classList.contains('eliminar')) {
        confirmarEliminar(id);
    }
}

async function prepararEdicion(id) {
    const datos = new FormData();
    datos.append('id_permiso', id);
    datos.append('operacion', 'consultar_unico');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const permiso = respuesta.datos;
    document.getElementById('id_permiso').value = permiso.id_permiso;
    document.getElementById('accion').value = permiso.accion;

    if (permiso_modificar != 1) {
        botonFormulario.setAttribute('hidden', true);
    }

    botonFormulario.setAttribute('modificar', true);
    botonFormulario.setAttribute('id_modificar', permiso.id_permiso);
    botonFormulario.textContent = 'Guardar Cambios';
    document.getElementById('titulo_modal').textContent = 'Modificar Permiso';
    id_modificar = permiso.id_permiso;
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalPermiso.hide();
    tablaPermisos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Permiso registrado correctamente');
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_permiso', id);
    formData.append('operacion', 'modificar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalPermiso.hide();
    tablaPermisos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Permiso modificado correctamente');
}

botonFormulario?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = botonFormulario.hasAttribute('modificar');
    const accion = esEdicion ? 'modificar' : 'Registrar';

    if (await validarFormulario()) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Desea ${accion.toLowerCase()} este permiso?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1b8a40',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                if (esEdicion) {
                    modificar(botonFormulario.getAttribute('id_modificar'));
                } else {
                    registrar();
                }
            }
        });
    }
});

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e01d22',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) eliminar(id);
    });
}

async function eliminar(id) {
    const datos = new FormData();
    datos.append('id_permiso', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tablaPermisos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Permiso eliminado correctamente');
}

function resetModal() {
    formulario.reset();
    botonFormulario.removeAttribute('modificar');
    botonFormulario.removeAttribute('id_modificar');
    botonFormulario.textContent = 'Guardar';
    document.getElementById('titulo_modal').textContent = 'Registrar Permiso';
    id_modificar = null;
    document.getElementById('id_permiso').value = '';
}

async function validarFormulario() {
    const accion = document.getElementById('accion');
    if (!Validaciones.keyUp(/^[A-Za-z_]{3,50}$/, accion, accion.nextElementSibling, 'Acción inválida (solo letras y guión bajo)')) {
        Utilidades.mensaje('error', 'Error', 'El nombre de la acción no es válido');
        return false;
    }
    return true;
}