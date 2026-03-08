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
    verificarErroresURL();
});

// -------------------------------------------------------------------------
// Event Listeners (Interacción Usuario)
// -------------------------------------------------------------------------

// Seleccionar Base de Datos
select_db.addEventListener("change", (e) => {
    const valor = e.target.value;
    const divAcciones = document.getElementById('acciones_exportar');
    
    if (!valor) {
        divAcciones.style.display = 'none';
        return;
    }

    const valido = /^negocio|seguridad/.test(valor);
    validarInput(e.target, valido, "Selección no válida");

    if (valido) {
        divAcciones.style.display = 'flex'; // Usamos flex para mantener la alineación
        document.getElementById('db_input').value = valor;
        // Quitar cualquier "hidden" residual que pudiera haber quedado de versiones anteriores
        boton_exportar.removeAttribute('hidden');
        boton_descargar.removeAttribute('hidden');
    }
});

// Seleccionar Copia de Seguridad de la lista
select_copias.addEventListener("change", (e) => {
    const valor = e.target.value;
    const infoDiv = document.getElementById('info_seleccion');
    const btn = document.getElementById('boton_importar');
    
    if (!valor) {
        infoDiv.innerHTML = '';
        btn.style.display = 'none';
        return;
    }

    const esSeguridad = valor.includes("seguridad");
    const textoDB = esSeguridad ? 'Seguridad' : 'Negocio';
    const icono = esSeguridad ? 'bi-shield-check' : 'bi-building';
    
    // Mostramos la alerta con el diseño de "info"
    infoDiv.innerHTML = `
        <div class="alert alert-info border-0 shadow-sm py-2 mb-0 d-flex align-items-center">
            <i class="bi ${icono} fs-4 me-3"></i>
            <div>
                <strong>Archivo listo:</strong> Se restaurará en la base de datos de <u>${textoDB}</u>.
            </div>
        </div>`;
    
    document.getElementById('texto_boton_importar').textContent = `Restaurar datos de ${textoDB}`;
    boton_importar.style.display = 'inline-block';
    // Limpiar el otro input
    input_file.value = '';
});

// Subir archivo manual
input_file.addEventListener("change", (e) => {
    const infoDiv = document.getElementById('info_seleccion');
    const archivo = e.target.files[0];

    if (!archivo) {
        boton_importar.style.display = 'none';
        infoDiv.innerHTML = '';
        return;
    }

    // Detección inteligente por nombre de archivo
    const nombre = archivo.name.toLowerCase();
    const esSeguridad = nombre.includes("seguridad");
    const textoDestino = esSeguridad ? "Seguridad" : "Negocio";
    const icono = esSeguridad ? "bi-shield-check" : "bi-building";

    select_copias.value = ''; // Limpiar el select de copias guardadas

    infoDiv.innerHTML = `
        <div class="alert alert-secondary border-0 shadow-sm py-2 mb-0 d-flex align-items-center">
            <i class="bi bi-file-earmark-arrow-up fs-4 me-3"></i>
            <div>
                <strong>Archivo local:</strong> ${archivo.name}<br>
                <small><i class="bi ${icono} me-1"></i> Destino detectado: Base de Datos de <b>${textoDestino}</b></small>
            </div>
        </div>`;

    document.getElementById('texto_boton_importar').textContent = `Importar datos de ${textoDestino}`;
    boton_importar.style.display = 'inline-block';
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
        Alertas.mostrar('error', 'Atención', 'Debe seleccionar una Copia de Seguridad o subir un archivo SQL.');
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

const iconoBoton = document.getElementById('icono_boton_importar');

boton_importar.addEventListener('mouseenter', () => {
    iconoBoton.classList.remove('bi-arrow-repeat');
    iconoBoton.classList.add('bi-exclamation-triangle', 'text-warning');
});

boton_importar.addEventListener('mouseleave', () => {
    iconoBoton.classList.remove('bi-exclamation-triangle', 'text-warning');
    iconoBoton.classList.add('bi-arrow-repeat');
});

// -------------------------------------------------------------------------
// Funciones de Lógica de Negocio (AJAX)
// -------------------------------------------------------------------------

async function obtenerCopiasGuardadas() {
    let datos = new FormData();
    datos.append('operacion', 'obtener_copias');

    const respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
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

    // Ordenar archivos por fecha descendente (más reciente primero)
    listaArchivos.sort().reverse();

    listaArchivos.forEach(fichero => {
        let option = document.createElement("option");
        
        // Texto formateado con el nuevo icono
        option.textContent = formatearNombreArchivo(fichero);
        option.value = fichero;
        
        fragment.appendChild(option);
    });

    select_copias.appendChild(fragment);
}

/**
 * Transforma el nombre técnico del archivo en un formato legible
 * Ejemplo: backup_haydee_db_2026-02-21-07-23-31.sql 
 * Resultado: 📦 Negocio | 📅 21/02/2026 | 🕒 07:23:31
 */
/**
 * Transforma el nombre técnico en un formato legible con iconos diferenciados
 */
function formatearNombreArchivo(fichero) {
    const esSeguridad = fichero.includes("seguridad");
    
    // Símbolos de color + Iconos de oficina/escudo
    const prefijo = esSeguridad ? "🔹 🛡️" : "🔹 🏢";
    const tipo = esSeguridad ? "SEGURIDAD" : "NEGOCIO";
    
    const match = fichero.match(/(\d{4})-(\d{2})-(\d{2})-(\d{2})-(\d{2})-(\d{2})/);
    
    if (match) {
        const [_, anio, mes, dia, hora, min, seg] = match;
        return `${prefijo} ${tipo} | 📅 ${dia}/${mes}/${anio} | 🕒 ${hora}:${min}:${seg}`;
    }
    return fichero;
}

async function generarCopiaSeguridad() {
    let datos = new FormData();
    datos.append("db", select_db.value);
    datos.append('operacion', 'generar_copia_seguridad');

    const respuesta = await Peticiones.enviar(datos);

    if (respuesta.estatus) {
        Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
        obtenerCopiasGuardadas(); // Actualizar lista
    } else {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
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

    const respuesta = await Peticiones.enviar(datos);
    manejarRespuestaImportacion(respuesta);
}

async function importarSQL() {
    let datos = new FormData();
    // Utilidades.query maneja FormData, así que soporta archivos perfectamente
    datos.append("fichero", input_file.files[0]);
    datos.append('operacion', 'importar_archivo_sql');

    const respuesta = await Peticiones.enviar(datos);
    manejarRespuestaImportacion(respuesta);
}

// -------------------------------------------------------------------------
// Funciones Auxiliares (Locales a este módulo)
// -------------------------------------------------------------------------

function manejarRespuestaImportacion(respuesta) {
    if (respuesta.estatus) {
        Alertas.mostrar('success', 'Restauración Completada', respuesta.mensaje);
        // Recargar la página para limpiar estado
        // setTimeout(() => window.location.reload(), 2000);
    } else {
        Alertas.mostrar('error', 'Fallo en Restauración', respuesta.mensaje);
    }
}

function validarSeleccionDB() {
    if (select_db.value === "") {
        Alertas.mostrar('error', 'Atención', 'Debe seleccionar una Base de Datos.');
        return false;
    }
    const valido = /^negocio|seguridad/.test(select_db.value);
    if (!valido) {
        validarInput(select_db, false, "La base de datos no es válida");
        Alertas.mostrar('error', 'Error', 'La base de datos seleccionada no existe');
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
        Alertas.mostrar('error', 'Error', 'Ocurrió un error al intentar descargar el archivo.');
        
        // Limpiar URL sin recargar
        const currentURL = new URL(window.location.href);
        currentURL.searchParams.delete('e');
        window.history.replaceState({}, '', currentURL.toString());
    }
}