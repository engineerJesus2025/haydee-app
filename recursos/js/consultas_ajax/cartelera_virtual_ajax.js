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
        Alertas.mostrar('error', 'Atención', 'Ha ocurrido un error durante la operación, inténtelo nuevamente');
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
        const mapa = { "1": { texto: "Urgente", color: "danger" }, "2": { texto: "Importante", color: "warning text-dark" }, "3": { texto: "Informativo", color: "success" } };
        const conf = mapa[p] || { texto: "Desconocida", color: "secondary" };
        return `<span class="badge bg-${conf.color}">${conf.texto}</span>`;
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_cartelera;
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-id="${id}" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (window.permiso_modificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-id="${id}" data-tooltip="true" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (window.permiso_eliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" data-id="${id}" data-tooltip="true" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Prioridad", field: "prioridad", formatter: formatoPrioridad, minWidth: 130},
        { title: "Título", field: "titulo", minWidth: 150 },
        { title: "Fecha", field: "fecha", formatter: formatoFecha, minWidth: 130, responsive: 0 },
        {
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, responsive: 0, 
            download: false, headerHozAlign: "center", widthGrow: 2,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { target: btn };
                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }
                if (btn.classList.contains('modificar')) modificar_formulario(mockEvent);
                if (btn.classList.contains('eliminar')) {
                    const id = btn.getAttribute('data-id');
                    Swal.fire({
                        title: "¿Estás seguro?", text: "¿Desea eliminar esta publicación?", showCancelButton: true, confirmButtonText: "Eliminar", cancelButtonText: "Cancelar", confirmButtonColor: "#e01d22", icon: "warning"
                    }).then((r) => { if (r.isConfirmed) eliminar(id); });
                }
            }
        }
    ];

    tabla_cartelera = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } });
    
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
    datos.append("operacion", "consultar_cartelera");

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;

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

        modal.show();
    });

}

// Función para mostrar los detalles de la publicación (cartelera_virtual_ajax.js)
function mostrarVistaPrevia(data) {
    // 1. Textos básicos
    document.getElementById("vista_titulo").textContent = data.titulo;
    document.getElementById("vista_descripcion").textContent = data.descripcion;
    document.getElementById("vista_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha);
    document.getElementById("vista_autor").textContent = data.nombre_usuario;

    // 2. Prioridad (Colores de Bootstrap)
    let vista_prioridad = document.getElementById("vista_prioridad");
    if (data.prioridad === "1") {
        vista_prioridad.className = "badge bg-danger fs-6 px-3 py-2 shadow-sm text-nowrap";
        vista_prioridad.textContent = "Urgente";
    } else if (data.prioridad === "2") {
        vista_prioridad.className = "badge bg-warning text-dark fs-6 px-3 py-2 shadow-sm text-nowrap";
        vista_prioridad.textContent = "Importante";
    } else {
        vista_prioridad.className = "badge bg-success fs-6 px-3 py-2 shadow-sm text-nowrap";
        vista_prioridad.textContent = "Informativo";
    }

    // 3. Lógica de la Imagen (Basada en tus archivos)
    let imagen = document.getElementById("vista_imagen");
    let mensaje_error = document.getElementById("mensaje_error_imagen");
    let contenedor_imagen = document.getElementById("contenedor_imagen"); // El nuevo div que envuelve la imagen

    if (data.imagen) {
        // Si hay imagen, construimos la ruta y mostramos los contenedores
        imagen.src = "recursos/img/cartelera/" + data.imagen;
        imagen.style.display = "inline-block";
        contenedor_imagen.style.display = "block"; // Mostramos el bloque completo
        mensaje_error.classList.add("d-none");
    } else {
        // Si no hay imagen, limpiamos el src y ocultamos todo el bloque para que no quede un espacio en blanco
        imagen.src = "";
        imagen.style.display = "none";
        contenedor_imagen.style.display = "none";
        mensaje_error.classList.add("d-none");
    }

    // Mostramos el modal
    modalVistaPrevia.show();
}

/**
 * Registra una nueva publicación
 */
async function registrar() {
    let datos = new FormData(formulario_usar);
    datos.append("operacion", "registrar_cartelera");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_cartelera.replaceData();
    });
}

/**
 * Actualiza una publicación existente
 */
async function modificar(id) {
    let datos = new FormData(formulario_usar);
    datos.append("id_cartelera", id);
    datos.append("operacion", "modificar_cartelera");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_cartelera.replaceData();
    });
}

/**
 * Elimina una publicación
 */
async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_cartelera", id);
    datos.append("operacion", "eliminar_cartelera");

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_cartelera.replaceData();
    });
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
            { element: '#tabla_cartelera_virtual', popover: { title: 'Lista de Publicaciones', description: 'Aquí verás todos los comunicados. Puedes ver cómo lucen (Vista previa), editarlos o eliminarlos.', side: "top", align: 'center' } }
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