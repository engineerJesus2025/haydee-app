/**
 * cartelera_virtual_ajax.js
 * Gestión de Cartelera Virtual - Peticiones AJAX
 * Dependencias: utilidades.js, formatoFechas.js
 */

let id_modificar;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;
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
        const config = obtenerConfigPrioridad(cell.getValue());
        
        return `<span class="badge bg-${config.color} bg-opacity-10 ${config.claseTextoBorder} px-3 py-2 shadow-sm text-nowrap" style="font-size: .85rem;">
                    <i class="bi ${config.icono} me-1"></i>
                    ${config.texto}
                </span>`;
    };

    const formatoBotones = (cell) => {
        const id = cell.getData().id_cartelera;
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-id="${id}" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-id="${id}" data-tooltip="true" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
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
        { title: "Prioridad", field: "prioridad", formatter: formatoPrioridad, minWidth: 130,},
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
    
    Tablas.inicializarBuscadorGlobal(tabla_cartelera, "busqueda_global", columnas);
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

        if (permisoModificar != 1) {
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
    // Textos básicos
    document.getElementById("vista_titulo").textContent = data.titulo;
    document.getElementById("vista_descripcion").textContent = data.descripcion;
    document.getElementById("vista_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha);
    document.getElementById("vista_autor").textContent = data.nombre_usuario;

    // Prioridad (Con Soft Badges centralizados)
    const config = obtenerConfigPrioridad(data.prioridad);
    let vista_prioridad = document.getElementById("vista_prioridad");
    
    // Limpiamos clases previas e inyectamos el HTML del Soft Badge
    vista_prioridad.className = ""; 
    vista_prioridad.innerHTML = `
        <span class="badge bg-${config.color} bg-opacity-10 ${config.claseTextoBorder} fs-6 px-3 py-2 shadow-sm text-nowrap">
            <i class="bi ${config.icono} me-1"></i>
            ${config.texto}
        </span>
    `;

    // Lógica de la Imagen (Basada en tus archivos)
    let imagen = document.getElementById("vista_imagen");
    let mensaje_error = document.getElementById("mensaje_error_imagen");
    let contenedor_imagen = document.getElementById("contenedor_imagen"); // El nuevo div que envuelve la imagen

    if (data.imagen) {
        // Si hay imagen, construimos la ruta y mostramos los contenedores
        imagen.src = "recursos/img/cartelera_virtual/" + data.imagen;
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
/**
 * Procesa la prioridad de un aviso y devuelve su configuración visual (Soft Badge)
 * @param {string|number} prioridad - Nivel de prioridad (1, 2, 3)
 * @returns {object} Configuración visual
 */
function obtenerConfigPrioridad(prioridad) {
    const p = String(prioridad);
    let color = "secondary";
    let icono = "bi-bookmark-fill";
    let texto = "Desconocida";
    let claseTextoBorder = "text-secondary border border-secondary";

    if (p === "1") {
        color = "danger";
        icono = "bi-exclamation-triangle-fill";
        texto = "Urgente";
        claseTextoBorder = "text-danger border border-danger";
    } else if (p === "2") {
        color = "warning";
        icono = "bi-star-fill";
        texto = "Importante";
        claseTextoBorder = "text-dark border border-warning"; // Texto oscuro para el amarillo
    } else if (p === "3") {
        color = "success";
        icono = "bi-info-circle-fill";
        texto = "Informativo";
        claseTextoBorder = "text-success border border-success";
    }

    return { color, claseTextoBorder, icono, texto };
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
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Cartelera Virtual', description: 'Bienvenido. Aquí puedes publicar avisos, noticias y comunicados importantes para todos los residentes del condominio.', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_cartelera"]', popover: { title: 'Nueva Publicación', description: 'Haz clic aquí para crear un nuevo aviso o subir un afiche informativo a la cartelera.', side: "bottom", align: 'start' } },
            { element: '#tabla_cartelera_virtual', popover: { title: 'Lista de Publicaciones', description: 'Aquí verás todos los comunicados. Puedes ver cómo lucen (Vista previa), editarlos o eliminarlos.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#titulo', popover: { title: 'Título', description: 'Escribe un título llamativo y claro para tu comunicado.', side: 'bottom', align: 'start' } },
            { element: '#descripcion', popover: { title: 'Descripción', description: 'Redacta el contenido detallado de tu publicación aquí.', side: 'bottom', align: 'start' } },
            { element: '#imagen', popover: { title: 'Imagen (Opcional)', description: 'Puedes adjuntar una foto o imagen para que la publicación sea mucho más visual.', side: 'top', align: 'start' } },
            { element: '#prioridad', popover: { title: 'Prioridad', description: 'Clasifica la urgencia del aviso (Alta, Media o Baja) para llamar la atención rápidamente.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Haz clic aquí para publicar tu aviso en la cartelera virtual.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_cartelera',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
