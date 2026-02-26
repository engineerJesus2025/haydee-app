/**
 * cartelera_virtual_ajax.js
 * Gestión de Cartelera Virtual - Peticiones AJAX
 * Dependencias: utilidades.js, formatoFechas.js
 */

let id_modificar;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;
let nombre_usuario = document.querySelector("#nombre_usuario")?.value || "Desconocido";

let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal("#modal_cartelera");
let modalVistaPrevia = new bootstrap.Modal("#modal_vista_previa");
let formulario_usar = document.querySelector("#form_cartelera");
let tabla_cartelera;

// Inicializar la tabla al cargar
consultar();

// Resetear modal al cerrarlo
document.querySelector("#modal_cartelera").addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById("titulo_modal").textContent = "Registrar Publicación";
    
    // Limpiar mensajes de error
    formulario_usar.querySelectorAll(".w-100").forEach(el => el.textContent = "");
    document.querySelector("#nombre_imagen_cargada").textContent = "";
    document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
    document.querySelector("#boton_eliminar_imagen").removeAttribute("data-nombre");

    const inputOculto = formulario_usar.querySelector("input[name='eliminar_imagen']");
    if (inputOculto) inputOculto.remove();

    document.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
});

// Ajustar columnas de DataTable al colapsar menú lateral
document.getElementById('header-toggle')?.addEventListener("click", () => {
    setTimeout(() => tabla_cartelera?.columns.adjust().draw(), 450);
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
 * Devuelve el HTML del badge según la prioridad
 */
function obtenerPrioridadTexto(prioridad) {
    const mapa = {
        "1": { texto: "Alta", color: "success" },
        "2": { texto: "Media", color: "warning" },
        "3": { texto: "Baja", color: "danger" }
    };
    const p = mapa[prioridad] || { texto: "Desconocida", color: "secondary" };
    return `<span class="badge bg-${p.color}">${p.texto}</span>`;
}

/**
 * Formatea fecha YYYY-MM-DD a DD/MM/YYYY
 */
function formatearFecha(fechaStr) {
    if (!fechaStr) return "N/A";
    const partes = fechaStr.split("-");
    return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : fechaStr;
}

/**
 * Crea el HTML de los botones de acción (vista previa, modificar, eliminar)
 */
function crearBotones(id) {
    let html = `<div class="row justify-content-evenly">
                    <button type="button" class="btn btn-primary btn-sm col-3 vista-previa" data-id="${id}" title="Vista previa">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm col-3 modificar" data-id="${id}" data-bs-toggle="modal" data-bs-target="#modal_cartelera" title="modificar">
                        <i class="bi bi-pencil-square"></i>
                    </button>`;
    if (permiso_eliminar == 1) {
        html += `<button type="button" class="btn btn-danger btn-sm col-3 eliminar" data-id="${id}" title="Eliminar">
                    <i class="bi bi-trash3-fill"></i>
                </button>`;
    }
    html += `</div>`;
    return html;
}

/**
 * Inicializa DataTable con los datos de cartelera
 */
function consultar() {
    const columnas = [
        { 
            data: "fecha",
            render: (data) => formatearFecha(data)
        },
        { data: "titulo" },
        { data: "nombre_usuario" },
        { 
            data: "prioridad",
            render: (data) => obtenerPrioridadTexto(data)
        },
        {
            data: null,
            render: (row) => crearBotones(row.id_cartelera)
        }
    ];

    const parametrosConsulta = (data) => {
        data.operacion = 'consulta';
    };

    const configuracionFila = (row, data) => {
        row.id = `fila-${data.id_cartelera}`;
        // Los eventos se asignarán mediante delegación en el tbody (más abajo)
    };

    tabla_cartelera = Utilidades.crearDataTable(
        'tabla_cartelera_virtual',
        columnas,
        parametrosConsulta,
        configuracionFila
    );
}

/**
 * Prepara el formulario con los datos de la publicación a modificar
 */
async function modificar_formulario(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("data-id");

    const datos = new FormData();
    datos.append("id_cartelera", id);
    datos.append("operacion", "consulta_especifica");

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje || 'No se pudieron cargar los datos');
        return;
    }

    const data = respuesta.datos;

    // Llenar formulario
    formulario_usar.querySelector("#titulo").value = data.titulo;
    formulario_usar.querySelector("#descripcion").value = data.descripcion;
    formulario_usar.querySelector("#fecha").value = data.fecha;
    formulario_usar.querySelector("#prioridad").value = data.prioridad;

    // Mostrar información de la imagen
    const nombreImagenSpan = document.querySelector("#nombre_imagen_cargada");
    const botonEliminarImagen = document.querySelector("#boton_eliminar_imagen");

    if (data.imagen && data.imagen !== "") {
        let nombre_archivo = data.imagen.split("/").pop();
        nombreImagenSpan.textContent = `Imagen cargada: ${nombre_archivo}`;
        botonEliminarImagen.classList.remove("d-none");
        botonEliminarImagen.setAttribute("data-nombre", nombre_archivo);
    } else {
        nombreImagenSpan.textContent = "No hay imagen cargada.";
        botonEliminarImagen.classList.add("d-none");
        botonEliminarImagen.removeAttribute("data-nombre");
    }

    if (permiso_modificar != 1) {
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_cartelera);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById("titulo_modal").textContent = "Modificar Publicación";
    id_modificar = id;
}

/**
 * Muestra la vista previa de una publicación
 */
async function mostrarVistaPrevia(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("data-id");

    const datos = new FormData();
    datos.append("id_cartelera", id);
    datos.append("operacion", "consulta_especifica");

    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje || 'No se pudieron cargar los datos');
        return;
    }

    const data = respuesta.datos;

    document.getElementById("vista_titulo").textContent = data.titulo;
    document.getElementById("vista_descripcion").textContent = data.descripcion;
    document.getElementById("vista_fecha").textContent = formatearFecha(data.fecha);
    document.getElementById("vista_prioridad").innerHTML = obtenerPrioridadTexto(data.prioridad);
    document.getElementById("vista_autor").textContent = data.nombre_usuario;

    // Resetear mensaje de error
    document.getElementById("vista_imagen").style.display = "block";
    document.getElementById("mensaje_error_imagen").classList.add("d-none");

    const imagen = (data.imagen && data.imagen !== "")
        ? `recursos/img/cartelera/${data.imagen}`
        : "";
    document.getElementById("vista_imagen").setAttribute("src", imagen);

    modalVistaPrevia.show();
}

/**
 * Registra una nueva publicación
 */
async function registrar() {
    let datos = new FormData(formulario_usar);
    datos.append("operacion", "registrar");

    let respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje || 'Error al registrar');
        return;
    }

    modal.hide();
    formulario_usar.reset();

    tabla_cartelera.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'La publicación se ha registrado correctamente');
}

/**
 * Actualiza una publicación existente
 */
async function modificar(id) {
    let datos = new FormData(formulario_usar);
    datos.append("id_cartelera", id);
    datos.append("operacion", "modificar");

    let respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje || 'Error al modificar');
        // No cerrar el modal para que el usuario pueda corregir
        return;
    }

    formulario_usar.reset();
    modal.hide();

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById("titulo_modal").textContent = "Registrar Publicación";

    tabla_cartelera.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'La publicación se ha modificado correctamente');
}

/**
 * Elimina una publicación
 */
async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_cartelera", id);
    datos.append("operacion", "eliminar");

    let respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje || 'Error al eliminar');
        return;
    }

    tabla_cartelera.ajax.reload(null, false);
    Utilidades.mensaje('success', 'Éxito', 'La publicación ha sido eliminada correctamente');
}

// ============================================
// DELEGACIÓN DE EVENTOS EN LA TABLA
// ============================================
document.querySelector("#tabla_cartelera_virtual tbody").addEventListener('click', function(e) {
    const boton = e.target.closest('button');
    if (!boton) return;

    if (boton.classList.contains('modificar')) {
        modificar_formulario(e);
    } else if (boton.classList.contains('vista-previa')) {
        mostrarVistaPrevia(e);
    } else if (boton.classList.contains('eliminar')) {
        const id = boton.getAttribute('data-id');
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Desea eliminar esta publicación?",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#e01d22",
            icon: "warning"
        }).then((resultado) => {
            if (resultado.isConfirmed) eliminar(id);
        });
    }
});

// ============================================
// BOTÓN ELIMINAR IMAGEN EN EL FORMULARIO DE EDICIÓN
// ============================================
document.querySelector("#boton_eliminar_imagen").addEventListener("click", function () {
    Swal.fire({
        title: "¿Eliminar imagen?",
        text: "La imagen cargada será eliminada de esta publicación.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            // Agregar un campo hidden al formulario para indicar que se debe eliminar la imagen
            const hiddenEliminar = document.createElement("input");
            hiddenEliminar.type = "hidden";
            hiddenEliminar.name = "eliminar_imagen";
            hiddenEliminar.value = "1";
            formulario_usar.appendChild(hiddenEliminar);

            document.querySelector("#nombre_imagen_cargada").textContent = "Imagen eliminada.";
            document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
        }
    });
});