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
let modal = new bootstrap.Modal("#modal_cartelera", { focus: false });
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
 * Inicializa DataTable con los datos de cartelera
 */
function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());
    const formatoPrioridad = (cell) => {
        const p = cell.getValue();
        const mapa = { "1": { texto: "Alta", color: "success" }, "2": { texto: "Media", color: "warning" }, "3": { texto: "Baja", color: "danger" } };
        const conf = mapa[p] || { texto: "Desconocida", color: "secondary" };
        return `<span class="badge bg-${conf.color}">${conf.texto}</span>`;
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_cartelera;
        let html = `<div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-id="${id}" title="Vista previa"><i class="bi bi-eye-fill"></i></button>
            <button type="button" class="btn btn-success btn-sm modificar" data-id="${id}" data-bs-toggle="modal" data-bs-target="#modal_cartelera" title="Modificar"><i class="bi bi-pencil-square"></i></button>`;
        if (permiso_eliminar == 1) {
            html += `<button type="button" class="btn btn-danger btn-sm eliminar" data-id="${id}" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "FECHA", field: "fecha", formatter: formatoFecha, minWidth: 100, responsive: 0 },
        { title: "TÍTULO", field: "titulo", minWidth: 150 },
        { title: "AUTOR", field: "nombre_usuario", minWidth: 120 },
        { title: "PRIORIDAD", field: "prioridad", formatter: formatoPrioridad, minWidth: 100 },
        {
            title: "ACCIONES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 140, responsive: 0, download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { target: btn };
                
                if (btn.classList.contains('modificar')) modificar_formulario(mockEvent);
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(mockEvent);
                if (btn.classList.contains('eliminar')) {
                    const id = btn.getAttribute('data-id');
                    Swal.fire({
                        title: "¿Estás seguro?", text: "¿Desea eliminar esta publicación?", showCancelButton: true, confirmButtonText: "Eliminar", cancelButtonText: "Cancelar", confirmButtonColor: "#e01d22", icon: "warning"
                    }).then((r) => { if (r.isConfirmed) eliminar(id); });
                }
            }
        }
    ];

    tabla_cartelera = Utilidades.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } });
    
    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas.filter(col => col.field).map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_cartelera.setFilter([filtros]);
        });
    }
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
    document.getElementById("vista_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha);
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

    tabla_cartelera.replaceData();
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

    tabla_cartelera.replaceData();
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

    tabla_cartelera.replaceData();
    Utilidades.mensaje('success', 'Éxito', 'La publicación ha sido eliminada correctamente');
}

// ============================================
// DELEGACIÓN DE EVENTOS EN LA TABLA
// ============================================
function obtenerPrioridadTexto(prioridad) {
    const mapa = {
        "1": { texto: "Alta", color: "success" },
        "2": { texto: "Media", color: "warning" },
        "3": { texto: "Baja", color: "danger" }
    };
    const p = mapa[prioridad] || { texto: "Desconocida", color: "secondary" };
    return `<span class="badge bg-${p.color}">${p.texto}</span>`;
}

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

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - CARTELERA VIRTUAL
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // El micro-retraso infalible para anclar la burbuja con precisión
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // 1. CONFIGURACIÓN DE LA VISTA PRINCIPAL
    const configPrincipal = {
        showProgress: true,
        animate: true,
        smoothScroll: false, 
        allowKeyboardControl: false,
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        
        onHighlightStarted: (element) => {
            if (element) {
                element.scrollIntoView({ behavior: 'instant', block: 'center' });
                alinearBurbuja();
            }
        },

        steps: [
            { element: '.page-header', popover: { title: 'Cartelera Virtual', description: 'Bienvenido. Aquí puedes publicar avisos, noticias y comunicados importantes para todos los residentes del condominio.', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_cartelera"]', popover: { title: 'Nueva Publicación', description: 'Haz clic aquí para crear un nuevo aviso o subir un afiche informativo a la cartelera.', side: "bottom", align: 'start' } },
            { element: '#tabla_cartelera_virtual_wrapper', popover: { title: 'Lista de Publicaciones', description: 'Aquí verás todos los comunicados. Puedes ver cómo lucen (Vista previa), editarlos o eliminarlos.', side: "top", align: 'center' } }
        ]
    };

    // 2. CONFIGURACIÓN DEL MODAL DE CARTELERA
    const configModalCartelera = {
        showProgress: true,
        animate: true, 
        smoothScroll: false, 
        allowKeyboardControl: false, 
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        
        onHighlightStarted: (element) => {
            if (element) {
                element.scrollIntoView({ behavior: 'instant', block: 'center' });
                alinearBurbuja();
            }
        },

        steps: [
            { element: '#titulo', popover: { title: 'Título', description: 'Escribe un título llamativo y claro para tu comunicado.', side: 'bottom', align: 'start' } },
            { element: '#descripcion', popover: { title: 'Descripción', description: 'Redacta el contenido detallado de tu publicación aquí.', side: 'bottom', align: 'start' } },
            { element: '#fecha', popover: { title: 'Fecha', description: 'Indica la fecha correspondiente al comunicado.', side: 'top', align: 'start' } },
            { element: '#imagen', popover: { title: 'Imagen (Opcional)', description: 'Puedes adjuntar una foto o imagen para que la publicación sea mucho más visual.', side: 'top', align: 'start' } },
            { element: '#prioridad', popover: { title: 'Prioridad', description: 'Clasifica la urgencia del aviso (Alta, Media o Baja) para llamar la atención rápidamente.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Haz clic aquí para publicar tu aviso en la cartelera virtual.', side: 'top', align: 'center' } }
        ]
    };

    // 3. LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalCarteleraHTML = document.getElementById('modal_cartelera');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalCarteleraHTML && modalCarteleraHTML.classList.contains('show')) {
                tourActivo = driver(configModalCartelera);
                tourActivo.drive();
            } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver(configPrincipal);
                tourActivo.drive();
            }
        });
    }

    // Limpiar al cerrar el modal
    if (modalCarteleraHTML) {
        modalCarteleraHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});