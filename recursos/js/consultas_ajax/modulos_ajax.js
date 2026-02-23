/**
 * modulos_ajax.js
 * Gestión de Módulos - Peticiones AJAX
 */

let tablaModulos;
let id_modificar = null;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_editar = document.querySelector("#permiso_editar")?.value;

const modalModulo = new bootstrap.Modal(document.getElementById("modal_modulo"));
const formulario = document.getElementById("form_modulo");
const botonFormulario = document.getElementById("boton_formulario");

document.addEventListener('DOMContentLoaded', () => {
    consultarModulos();

    document.getElementById("modal_modulo")?.addEventListener("hidden.bs.modal", resetModal);

    document.querySelector("#tabla_modulos tbody")?.addEventListener("click", manejarClickEnTabla);
});

async function consultarModulos() {
    const columnas = [
        { data: "id_modulo" },
        { data: "nombre" },
        {
            data: null,
            render: row => crearBotones(row.id_modulo).innerHTML
        }
    ];

    const parametros = (data) => { data.operacion = 'consultar'; };
    const postCreacion = (row, data) => {
        row.id = `fila-${data.id_modulo}`;
        row.lastElementChild.setAttribute('class','row justify-content-around');
    };

    tablaModulos = Utilidades.crearDataTable('tabla_modulos', columnas, parametros, postCreacion);
}

function crearBotones(id) {
    let div = document.createElement('div');
    div.className = 'row justify-content-evenly';
    let html = `
        <button type="button" class="btn btn-success btn-sm col-3 editar" title="Editar" value="${id}" data-bs-toggle="modal" data-bs-target="#modal_modulo">
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

    if (boton.classList.contains('editar')) {
        prepararEdicion(id);
    } else if (boton.classList.contains('eliminar')) {
        confirmarEliminar(id);
    }
}

async function prepararEdicion(id) {
    const datos = new FormData();
    datos.append('id_modulo', id);
    datos.append('operacion', 'consultar_unico');

    const respuesta = await Utilidades.query(datos, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const modulo = respuesta.datos;
    document.getElementById('id_modulo').value = modulo.id_modulo;
    document.getElementById('nombre').value = modulo.nombre;

    if (permiso_editar != 1) {
        botonFormulario.setAttribute('hidden', true);
    }

    botonFormulario.setAttribute('modificar', true);
    botonFormulario.setAttribute('id_modificar', modulo.id_modulo);
    botonFormulario.textContent = 'Guardar Cambios';
    document.getElementById('titulo_modal').textContent = 'Modificar Módulo';
    id_modificar = modulo.id_modulo;
}

async function registrar() {
    const formData = new FormData(formulario);
    formData.append('operacion', 'registrar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalModulo.hide();
    tablaModulos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Módulo registrado correctamente');
}

async function modificar(id) {
    const formData = new FormData(formulario);
    formData.append('id_modulo', id);
    formData.append('operacion', 'editar');

    const respuesta = await Utilidades.query(formData, true);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    modalModulo.hide();
    tablaModulos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Módulo modificado correctamente');
}

botonFormulario?.addEventListener('click', async (e) => {
    e.preventDefault();
    const esEdicion = botonFormulario.hasAttribute('modificar');
    const accion = esEdicion ? 'Editar' : 'Registrar';

    if (await validarFormulario()) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Desea ${accion.toLowerCase()} este módulo?`,
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
    datos.append('id_modulo', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Utilidades.query(datos);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tablaModulos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'Módulo eliminado correctamente');
}

function resetModal() {
    formulario.reset();
    botonFormulario.removeAttribute('modificar');
    botonFormulario.removeAttribute('id_modificar');
    botonFormulario.textContent = 'Guardar';
    document.getElementById('titulo_modal').textContent = 'Registrar Módulo';
    id_modificar = null;
    document.getElementById('id_modulo').value = '';
}

async function validarFormulario() {
    const nombre = document.getElementById('nombre');
    if (!Validaciones.keyUp(/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{3,50}$/, nombre, nombre.nextElementSibling, 'Nombre inválido (mínimo 3 letras)')) {
        Utilidades.mensaje('error', 'Error', 'El nombre del módulo no es válido');
        return false;
    }
    return true;
}