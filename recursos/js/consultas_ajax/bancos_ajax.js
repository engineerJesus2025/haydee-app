/**
 * bancos_ajax.js
 * Gestión de Bancos - Peticiones AJAX
 * Dependencias: utilidades.js
 */

let id_modificar, numero_cuenta_an;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#modal_banco");
let formulario_usar = document.querySelector("#form_banco");
let tabla_bancos;

// Inicializar la tabla al cargar
consultar();

// Resetear modal al cerrarlo
document.querySelector("#modal_banco").addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Banco";
    
    // Limpiar clases de validación
    formulario_usar.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    formulario_usar.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    formulario_usar.querySelectorAll('.w-100').forEach(el => el.textContent = "");
    
    // Deshabilitar RIF hasta que se seleccione tipo de documento
    formulario_usar.querySelector("#rif").setAttribute("disabled", true);
    
    numero_cuenta_an = null;
});

// Ajustar columnas de DataTable al colapsar menú lateral
document.getElementById('header-toggle')?.addEventListener("click", () => {
    setTimeout(() => tabla_bancos?.columns.adjust().draw(), 450);
});

function envio(operacion) {	
    if (operacion === "modificar") {
        modificar(boton_formulario.getAttribute("id_modificar"));
    } else if (operacion === "Registrar") {
        registrar();
    } else {
        Utilidades.mensaje('error', 'Atención', 'Ha ocurrido un error durante la operación, inténtelo nuevamente');
    }
}

/**
 * Crea el HTML de los botones de acción (modificar/eliminar) para cada fila
 */
function crearBotones(id) {
    let div = document.createElement("div");
    let html = `<div class="row justify-content-evenly">
                    <button type="button" class="btn btn-success btn-sm col-lg-3 col-4 modificar" data-bs-toggle="modal" data-bs-target="#modal_banco" title="modificar" value="${id}">
                        <i class="bi bi-pencil-square"></i>
                    </button>`;
    if (permiso_eliminar == 1) {
        html += `<button type="button" class="btn btn-danger btn-sm col-lg-3 col-4 eliminar" title="Eliminar" value="${id}">
                    <i class="bi bi-trash"></i>
                </button>`;
    }
    html += `</div>`;
    div.innerHTML = html;
    return div;
}

/**
 * Inicializa DataTable con los datos de bancos
 */
async function consultar() {
    const columnas = [
        { data: "nombre_banco" },
        { data: "codigo" },
        { data: "numero_cuenta" },
        { data: "telefono_afiliado" },
        { data: "rif" },
        { 
            data: null,
            render: (row) => crearBotones(row.id_banco).innerHTML
        }
    ];

    const parametrosConsulta = (data) => {
        data.operacion = 'consulta';
    };

    const configuracionFila = (row, data) => {
        row.id = `fila-${data.id_banco}`;
        // Asignar evento modificar
        row.querySelector(".modificar")?.addEventListener('click', preparar_formulario);
        // Asignar evento eliminar
        row.querySelector(".eliminar")?.addEventListener('click', eventoEliminar);
    };

    tabla_bancos = Utilidades.crearDataTable('tabla_banco', columnas, parametrosConsulta, configuracionFila);
}

/**
 * Prepara el formulario con los datos del banco a modificar
 */
async function preparar_formulario(e) {
    let datos = new FormData();
    let id = e.target.value || e.target.parentElement.value; 
    
    datos.append("id_banco", id);
    datos.append('operacion', 'consulta_especifica');

    let respuesta = await Utilidades.query(datos);	
    
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    let data = respuesta.datos; // { id_banco, nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif }

    // Llenar formulario
    formulario_usar.querySelector("#nombre_banco").value = data.nombre_banco;
    formulario_usar.querySelector("#codigo").value = data.codigo;
    formulario_usar.querySelector("#numero_cuenta").value = data.numero_cuenta;
    formulario_usar.querySelector("#telefono_afiliado").value = data.telefono_afiliado;
    // Separar tipo de documento y número
    let tipoDoc = data.rif.charAt(0);
    let numeroRif = data.rif.slice(1);
    formulario_usar.querySelector("#tipo_documento").value = tipoDoc;
    formulario_usar.querySelector("#rif").value = numeroRif;
    formulario_usar.querySelector("#rif").removeAttribute("disabled");

    if (permiso_modificar != 1) {
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_banco);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById('titulo_modal').textContent = "Modificar Banco";

    id_modificar = id;
    numero_cuenta_an = data.numero_cuenta;
}

/**
 * Registra un nuevo banco
 */
async function registrar() {
    let datos = new FormData(formulario_usar);
    // Construir RIF completo
    let tipo = datos.get('tipo_documento');
    let rifNum = datos.get('rif');
    datos.set('rif', tipo + rifNum);
    datos.append('operacion', 'registrar');
    
    let respuesta = await Utilidades.query(datos);

    modal.hide();
    formulario_usar.reset();

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_bancos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

/**
 * Actualiza un banco existente
 */
async function modificar(id) {	
    let datos = new FormData(formulario_usar);
    let tipo = datos.get('tipo_documento');
    let rifNum = datos.get('rif');
    datos.set('rif', tipo + rifNum);
    datos.append("id_banco", id);
    datos.append('operacion', 'modificar');

    let respuesta = await Utilidades.query(datos);

    formulario_usar.reset();
    modal.hide();

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");	
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Banco";

    tabla_bancos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'El registro se ha modificado exitosamente');
}

/**
 * Manejador del clic en botón eliminar (con confirmación)
 */
function eventoEliminar(e) {
    let id = e.target.value || e.target.parentElement.value;

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea eliminar este banco?",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        icon: "warning"
    }).then((resultado) => {
        if (resultado.isConfirmed) eliminar(id);				
    });
}

/**
 * Elimina un banco
 */
async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_banco", id);
    datos.append('operacion', 'eliminar');

    let respuesta = await Utilidades.query(datos);
    
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_bancos.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'El registro ha sido eliminado correctamente');
}