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

// Referencias a las pestañas
const tabServidor = document.getElementById('servidor-tab');
const tabPC = document.getElementById('pc-tab');

// Limpiar selecciones al cambiar de pestaña
if (tabServidor && tabPC) {
    tabServidor.addEventListener('shown.bs.tab', () => {
        input_file.value = ''; // Limpia el input de la PC
        document.getElementById('info_seleccion').innerHTML = '';
        boton_importar.style.display = 'none';
    });

    tabPC.addEventListener('shown.bs.tab', () => {
        select_copias.value = ''; // Limpia el select del servidor
        document.getElementById('info_seleccion').innerHTML = '';
        boton_importar.style.display = 'none';
    });
}

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
    const textoBoton = document.getElementById('texto_boton_importar');
    
    if (!valor) {
        infoDiv.innerHTML = '';
        btn.style.display = 'none';
        return;
    }

    const valorMin = valor.toLowerCase();
    const esSeguridad = valorMin.includes("seguridad");
    const esAutomatico = valorMin.includes("automatico") || valorMin.includes("auto");
    
    const valido = /^backup_.*_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_(MANUAL|AUTOMATICO)\.sql$/i.test(valorMin);
    
    validarInput(e.target, valido, "Archivo no válido");
    
    if (valido) {
        const textoDB = esSeguridad ? 'Seguridad' : 'Negocio';
        const icono = esSeguridad ? 'bi-shield-check' : 'bi-building';
        const tipoRespaldo = esAutomatico ? 'Automática 🤖' : 'Manual 👤';

        infoDiv.innerHTML = `
            <div class="alert alert-info border-0 shadow-sm py-2 mb-0 d-flex align-items-center">
                <i class="bi ${icono} fs-4 me-3"></i>
                <div>
                    <strong>Archivo listo (${tipoRespaldo}):</strong><br>
                    <small>Se restaurará en la base de datos de <u>${textoDB}</u>.</small>
                </div>
            </div>`;

        // Actualizar el span dentro del botón (preservando tu icono de advertencia)
        if(textoBoton) textoBoton.textContent = `Restaurar ${textoDB}`;
        btn.style.display = 'inline-block';
        
        input_file.value = ''; 
    } else {
        btn.style.display = 'none';
    }
});

// Subir archivo manual
input_file.addEventListener("change", (e) => {
    const infoDiv = document.getElementById('info_seleccion');
    const btn = document.getElementById('boton_importar');
    const textoBoton = document.getElementById('texto_boton_importar');
    const archivo = e.target.files[0];

    if (!archivo) {
        btn.style.display = 'none';
        infoDiv.innerHTML = '';
        return;
    }

    const nombre = archivo.name.toLowerCase();
    const esSeguridad = nombre.includes("seguridad");
    const esAutomatico = nombre.includes("AUTOMATICO") || nombre.includes("auto");
    const textoDestino = esSeguridad ? "Seguridad" : "Negocio";
    const icono = esSeguridad ? "bi-shield-check" : "bi-building";
    const tipoRespaldo = esAutomatico ? 'Automática' : 'Manual o Externa';

    select_copias.value = ''; 

    infoDiv.innerHTML = `
        <div class="alert alert-secondary border-0 shadow-sm py-2 mb-0 d-flex align-items-center">
            <i class="bi bi-file-earmark-arrow-up fs-4 me-3"></i>
            <div>
                <strong>Archivo local (${tipoRespaldo}):</strong> ${archivo.name}<br>
                <small><i class="bi ${icono} me-1"></i> Destino detectado: <b>${textoDestino}</b></small>
            </div>
        </div>`;

    if(textoBoton) textoBoton.textContent = `Importar en ${textoDestino}`;
    btn.style.display = 'inline-block';
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
    iconoBoton.classList.add('bi-exclamation-triangle');
});

boton_importar.addEventListener('mouseleave', () => {
    iconoBoton.classList.remove('bi-exclamation-triangle');
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
    listaArchivos.sort((a, b) => {
        // Extraemos la parte de la fecha YYYY-MM-DD_HH-mm-ss de cada nombre
        const regexFecha = /\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/;
        const fechaA = a.match(regexFecha) ? a.match(regexFecha)[0] : "";
        const fechaB = b.match(regexFecha) ? b.match(regexFecha)[0] : "";

        // Comparamos de forma descendente (B vs A) para que el más nuevo esté arriba
        return fechaB.localeCompare(fechaA);
    });

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
function formatearNombreArchivo(fichero) {
    const nombreMin = fichero.toLowerCase();
    const esSeguridad = nombreMin.includes("seguridad");
    const esAuto = nombreMin.includes("automatico");

    // Extraer fecha y hora: Busca YYYY-MM-DD_HH-mm-ss
    const match = fichero.match(/(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/);
    
    if (match) {
        const fecha = match[1].split('-').reverse().join('/'); // DD/MM/YYYY
        const hora = match[2].replace(/-/g, ':'); // HH:mm:ss
        
        const iconoDB = esSeguridad ? "🛡️" : "🏢";
        const etiquetaDB = esSeguridad ? "SEGURIDAD" : "NEGOCIO";
        const origen = esAuto ? "🤖 AUTO" : "👤 MANUAL";

        return `🔹 ${iconoDB} ${etiquetaDB} [${origen}] | 📅 ${fecha} | 🕒 ${hora}`;
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
    const archivo = input_file.files[0];
    
    // Detectamos a qué base de datos pertenece basándonos en el nombre del archivo
    const db = (archivo.name.toLowerCase().includes("seguridad")) ? 'seguridad' : 'negocio';

    // Agregamos el archivo y la base de datos a los datos a enviar
    datos.append("fichero", archivo); 
    datos.append("db", db);
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
        if(input.nextElementSibling) input.nextElementSibling.textContent = "";
    } else {
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
 * Limpia la URL si hubo error de descarga y muestra una alerta elegante
 */
function verificarErroresURL() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Si detectamos el parámetro 'e' de error
    if (urlParams.get('e')) {
        // Obtenemos el mensaje dinámico o ponemos uno por defecto
        const mensajeError = urlParams.get('msg') || 'Ocurrió un error al intentar descargar el archivo.';
        
        // Mostramos el SweetAlert (usamos warning porque es un error de usuario, no de sistema)
        Alertas.mostrar('warning', 'Descarga no disponible', mensajeError);
        
        // Limpiamos la URL silenciosamente sin recargar la página
        const currentURL = new URL(window.location.href);
        currentURL.searchParams.delete('e');
        currentURL.searchParams.delete('msg');
        window.history.replaceState({}, '', currentURL);
    }
}