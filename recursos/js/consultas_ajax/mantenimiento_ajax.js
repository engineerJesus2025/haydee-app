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
        const tipoRespaldo = esAutomatico ? 'Automática 🤖' : 'Manual 👤';

        // Ahora solo pasamos 2 argumentos (la BD y el tipo)
        renderizarTarjetaDestino(textoDB, tipoRespaldo);

        if(textoBoton) textoBoton.textContent = `Restaurar ${textoDB}`;
    
        input_file.value = ''; 
    } else {
        btn.style.display = 'none';
    }
});

// Subir archivo manual
input_file.addEventListener("change", (e) => {
    const btn = document.getElementById('boton_importar');
    const textoBoton = document.getElementById('texto_boton_importar');
    const archivo = e.target.files[0];

    if (!archivo) {
        btn.style.display = 'none';
        document.getElementById('info_seleccion').innerHTML = '';
        return;
    }

    const nombre = archivo.name.toLowerCase();
    let dbDetectada = "Desconocido";

    // Lógica de detección mejorada
    if (nombre.includes("seguridad")) {
        dbDetectada = "Seguridad";
    } else if (nombre.includes("negocio") || nombre.includes("haydee")) {
        dbDetectada = "Negocio";
    }

    const esAutomatico = nombre.includes("automatico") || nombre.includes("auto");
    const tipoRespaldo = esAutomatico ? 'Automática' : 'Manual o Externa';

    select_copias.value = ''; 

    // Renderizamos la tarjeta
    renderizarTarjetaDestino(dbDetectada, tipoRespaldo, archivo.name);

    // Ajustamos el texto del botón SOLO si conocemos el destino
    if (dbDetectada !== "Desconocido") {
        if(textoBoton) textoBoton.textContent = `Importar en ${dbDetectada}`;
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
    
    // Usamos nuestra nueva función validadora
    const db = determinarDBDestino(fichero);

    datos.append("fichero", fichero);
    datos.append("db", db);
    datos.append('operacion', 'importar_copia_seguridad');

    const respuesta = await Peticiones.enviar(datos);
    manejarRespuestaImportacion(respuesta);
}

async function importarSQL() {
    let datos = new FormData();
    const archivo = input_file.files[0];
    
    // Usamos nuestra nueva función validadora
    const db = determinarDBDestino(archivo.name);

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

/**
 * Determina a qué base de datos se debe importar.
 * Prioriza el Select de Emergencia si existe y tiene un valor.
 */
function determinarDBDestino(nombreArchivo) {
    const selectEmergencia = document.getElementById('select_db_emergencia');
    
    // Si el select existe y el usuario eligió una opción, usamos esa
    if (selectEmergencia && selectEmergencia.value) {
        return selectEmergencia.value;
    }
    
    // Si no (porque se detectó automáticamente), usamos la lógica del nombre
    return nombreArchivo.toLowerCase().includes("seguridad") ? 'seguridad' : 'negocio';
}

/**
 * Renderiza la tarjeta con lógica de selección de emergencia si el destino es desconocido
 */
function renderizarTarjetaDestino(dbDetectada, tipoRespaldo, nombreArchivo = null) {
    const infoDiv = document.getElementById('info_seleccion');
    const btn = document.getElementById('boton_importar');
    const esDesconocido = dbDetectada === "Desconocido";
    
    let claseColorSoft = esDesconocido ? 'badge-soft-warning' : (dbDetectada === 'Seguridad' ? 'badge-soft-info' : 'badge-soft-success');
    let icono = esDesconocido ? 'bi-question-circle-fill' : (dbDetectada === 'Seguridad' ? 'bi-shield-check' : 'bi-building');

    infoDiv.innerHTML = `
        <div class="vp-status-card p-3 rounded-4 d-flex flex-column gap-3 mb-0 transicion_entrada">
            <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                <div class="vp-icon-box ${claseColorSoft} d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm border-0">
                    <i class="bi ${icono} fs-4"></i>
                </div>
                
                <div class="flex-grow-1 text-center text-sm-start w-100">
                    <h6 class="mb-1 fw-bold text-body">${nombreArchivo ? 'Archivo local' : 'Archivo del servidor'}</h6>
                    <span class="text-muted small text-break">${nombreArchivo || tipoRespaldo}</span>
                </div>
                
                <div class="flex-shrink-0">
                    <span id="badge_destino_final" class="${claseColorSoft} rounded-pill fw-bold shadow-sm d-inline-flex align-items-center px-3 py-2" style="font-size: 0.85rem;">
                        <i class="bi bi-database-down me-2"></i> Destino: ${dbDetectada}
                    </span>
                </div>
            </div>

            ${esDesconocido ? `
                <div class="mt-2 p-3 bg-white bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                    <label class="form-label small fw-bold text-warning"><i class="bi bi-exclamation-triangle me-1"></i> El sistema no reconoce el destino. Por favor, selecciónelo manualmente:</label>
                    <select class="form-select form-select-sm" id="select_db_emergencia">
                        <option value="" selected hidden disabled>-- Seleccionar base de datos --</option>
                        <option value="negocio">Edificio Haydee (Negocio)</option>
                        <option value="seguridad">Módulo de Seguridad</option>
                    </select>
                </div>
            ` : ''}
        </div>`;

    // Lógica de habilitación de botón
    if (esDesconocido) {
        btn.style.display = 'none'; // Oculto hasta que elija en el select de emergencia
        
        // Listener para el select de emergencia
        const selectEmergencia = document.getElementById('select_db_emergencia');
        selectEmergencia.addEventListener('change', (e) => {
            const dbManual = e.target.value;
            const textoBoton = document.getElementById('texto_boton_importar');
            
            // Actualizar interfaz visual
            document.getElementById('badge_destino_final').className = dbManual === 'seguridad' ? 'badge-soft-info rounded-pill fw-bold px-3 py-2' : 'badge-soft-success rounded-pill fw-bold px-3 py-2';
            
            if(textoBoton) textoBoton.textContent = `Importar en ${dbManual.charAt(0).toUpperCase() + dbManual.slice(1)}`;
            btn.style.display = 'inline-block';
        });
    } else {
        btn.style.display = 'inline-block';
    }
}