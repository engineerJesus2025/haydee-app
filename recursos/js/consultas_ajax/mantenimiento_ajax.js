/**
 * Script para la gestión de Mantenimiento (Respaldo y Restauración)
 * Dependencias: utilidades.js (Objeto Utilidades), SweetAlert2
 */

// Elementos del DOM
const boton_exportar = document.getElementById('boton_exportar');
const boton_descargar = document.getElementById('boton_descargar');
const boton_importar = document.getElementById('boton_importar');
const select_db = document.getElementById('select_db');
const select_copias = document.getElementById('select_copias');
const input_file = document.getElementById('input_file_importar');

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    obtenerCopiasGuardadas();
    
    // Estado inicial de botones
    alternarVisibilidad(boton_exportar.parentElement, false);
    alternarVisibilidad(boton_descargar.parentElement, false);
    
    // Verificar si venimos de un error de descarga (parametro 'e' en url)
    verificarErroresURL();
});

// -------------------------------------------------------------------------
// Event Listeners (Interacción Usuario)
// -------------------------------------------------------------------------

// Seleccionar Base de Datos
select_db.addEventListener("change", (e) => {
    const valor = e.target.value;
    if (!valor) return;

    const valido = /^negocio|seguridad/.test(valor);
    validarInput(e.target, valido, "La base de datos seleccionada no existe");

    // Mostrar botones si es válido
    alternarVisibilidad(boton_exportar.parentElement, valido);
    alternarVisibilidad(boton_descargar.parentElement, valido);
    alternarVisibilidad(document.getElementById('o'), valido);
});

// Seleccionar Copia de Seguridad de la lista
select_copias.addEventListener("change", (e) => {
    const valor = e.target.value;
    if (!valor) return;

    // Regex para validar el nombre del archivo generado por el sistema
    const valido = /^backup(_seguridad)?_haydee_db_\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.sql$/.test(valor);
    
    validarInput(e.target, valido, "La copia de seguridad seleccionada no existe");
    alternarVisibilidad(boton_importar, valido);
    
    if (valido) {
        input_file.value = ''; // Limpiar input file para evitar ambigüedad
    }
});

// Subir archivo manual
input_file.addEventListener("change", (e) => {
    if (e.target.value !== '') {
        select_copias.value = ''; // Limpiar select
        select_copias.classList.remove('is-valid', 'is-invalid'); // Resetear estilos
        boton_importar.removeAttribute("hidden");
    }
});

// Botón Exportar (Generar Backup)
boton_exportar.addEventListener("click", async () => {
    if (!validarSeleccionDB()) return;

    const confirmacion = await Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea generar una nueva copia de seguridad?",
        showCancelButton: true,
        confirmButtonText: "Sí, Exportar",
        confirmButtonColor: "#1b8a40",
        cancelButtonText: "Cancelar",
        icon: "warning"
    });

    if (confirmacion.isConfirmed) {
        generarCopiaSeguridad();
    }
});

// Botón Descargar (Submit Formulario tradicional)
boton_descargar.addEventListener("click", (e) => {
    e.preventDefault();
    if (!validarSeleccionDB()) return;

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea descargar el archivo SQL?",
        showCancelButton: true,
        confirmButtonText: "Sí, Descargar",
        confirmButtonColor: "#1b8a40",
        cancelButtonText: "Cancelar",
        icon: "info"
    }).then((result) => {
        if (result.isConfirmed) {
            // Asignar valor al input oculto y enviar form
            document.getElementById('db_input').value = select_db.value;
            e.target.closest("form").submit();
        }
    });
});

// Botón Importar (Restaurar)
boton_importar.addEventListener("click", async () => {
    const hayCopiaSeleccionada = select_copias.value !== '';
    const hayArchivoSubido = input_file.value !== '';

    if (!hayCopiaSeleccionada && !hayArchivoSubido) {
        Utilidades.mensaje('error', 'Atención', 'Debe seleccionar una Copia de Seguridad o subir un archivo SQL.');
        return;
    }

    // Advertencia fuerte por ser acción destructiva
    const confirmacion = await Swal.fire({
        title: "¡Advertencia de Seguridad!",
        text: "¿Está seguro que desea restaurar la base de datos? Esta acción ELIMINARÁ todos los datos actuales y los reemplazará por la copia. No se puede deshacer.",
        showCancelButton: true,
        confirmButtonText: "Sí, Restaurar",
        confirmButtonColor: "#d33", // Rojo peligro
        cancelButtonText: "Cancelar",
        icon: "warning"
    });

    if (confirmacion.isConfirmed) {
        if (hayArchivoSubido) {
            importarSQL();
        } else {
            importarCopiaSeguridad();
        }
    }
});

// -------------------------------------------------------------------------
// Funciones de Lógica de Negocio (AJAX)
// -------------------------------------------------------------------------

async function obtenerCopiasGuardadas() {
    let datos = new FormData();
    datos.append('operacion', 'obtener_copias');

    // Usamos Utilidades.query
    const respuesta = await Utilidades.query(datos);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
        return;
    }

    const listaArchivos = respuesta.datos || []; 

    select_copias.innerHTML = ''; // Limpiar select

    // Opción por defecto
    let defaultOption = document.createElement("option");
    defaultOption.textContent = (listaArchivos.length === 0) ? "No hay copias guardadas" : "Seleccione la Copia de Seguridad";
    defaultOption.value = '';
    defaultOption.selected = true;
    if (listaArchivos.length > 0) defaultOption.hidden = true;
    select_copias.appendChild(defaultOption);

    if (listaArchivos.length === 0) {
        select_copias.disabled = true;
        return;
    }

    select_copias.disabled = false;
    let fragment = document.createDocumentFragment();
    
    listaArchivos.forEach(fichero => {
        let option = document.createElement("option");
        option.textContent = fichero;
        option.value = fichero;
        fragment.appendChild(option);
    });

    select_copias.appendChild(fragment);
}

async function generarCopiaSeguridad() {
    let datos = new FormData();
    datos.append("db", select_db.value);
    datos.append('operacion', 'generar_copia_seguridad');

    const respuesta = await Utilidades.query(datos);

    if (respuesta.estatus) {
        Utilidades.mensaje('success', 'Éxito', respuesta.mensaje);
        obtenerCopiasGuardadas(); // Actualizar lista
    } else {
        Utilidades.mensaje('error', 'Error', respuesta.mensaje);
    }
}

async function importarCopiaSeguridad() {
    let datos = new FormData();
    const fichero = select_copias.value;
    // Inferencia simple de la DB basada en el nombre del archivo
    const db = (fichero.includes("seguridad")) ? 'seguridad' : 'negocio';

    datos.append("fichero", fichero);
    datos.append("db", db);
    datos.append('operacion', 'importar_copia_seguridad');

    const respuesta = await Utilidades.query(datos);
    manejarRespuestaImportacion(respuesta);
}

async function importarSQL() {
    let datos = new FormData();
    // Utilidades.query maneja FormData, así que soporta archivos perfectamente
    datos.append("fichero", input_file.files[0]);
    datos.append('operacion', 'importar_archivo_sql');

    const respuesta = await Utilidades.query(datos);
    manejarRespuestaImportacion(respuesta);
}

// -------------------------------------------------------------------------
// Funciones Auxiliares (Locales a este módulo)
// -------------------------------------------------------------------------

function manejarRespuestaImportacion(respuesta) {
    if (respuesta.estatus) {
        Utilidades.mensaje('success', 'Restauración Completada', respuesta.mensaje);
        // Recargar la página para limpiar estado
        // setTimeout(() => window.location.reload(), 2000);
    } else {
        Utilidades.mensaje('error', 'Fallo en Restauración', respuesta.mensaje);
    }
}

function validarSeleccionDB() {
    if (select_db.value === "") {
        Utilidades.mensaje('error', 'Atención', 'Debe seleccionar una Base de Datos.');
        return false;
    }
    const valido = /^negocio|seguridad/.test(select_db.value);
    if (!valido) {
        validarInput(select_db, false, "La base de datos no es válida");
        Utilidades.mensaje('error', 'Error', 'La base de datos seleccionada no existe');
        return false;
    }
    validarInput(select_db, true);
    return true;
}

/**
 * Aplica clases de validación de Bootstrap y mensaje de error
 */
function validarInput(input, esValido, mensajeError = "") {
    if (esValido) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if(input.nextElementSibling) input.nextElementSibling.textContent = "";
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if(input.nextElementSibling) input.nextElementSibling.textContent = mensajeError;
    }
}

function alternarVisibilidad(elemento, mostrar) {
    if (!elemento) return;
    if (mostrar) elemento.removeAttribute("hidden");
    else elemento.setAttribute("hidden", "");
}

/**
 * Limpia la URL si hubo error de descarga anteriormente
 */
function verificarErroresURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('e')) {
        Utilidades.mensaje('error', 'Error', 'Ocurrió un error al intentar descargar el archivo.');
        
        // Limpiar URL sin recargar
        const currentURL = new URL(window.location.href);
        currentURL.searchParams.delete('e');
        window.history.replaceState({}, '', currentURL.toString());
    }
}