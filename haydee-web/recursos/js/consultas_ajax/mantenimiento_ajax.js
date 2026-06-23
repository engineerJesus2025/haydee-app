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
        divAcciones.style.display = 'flex'; 
        document.getElementById('db_input').value = valor;
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
        validarInput(e.target, true); 
        return;
    }

    const valorMin = valor.toLowerCase();
    const esSeguridad = valorMin.includes("seguridad");
    const esAutomatico = valorMin.includes("automatico") || valorMin.includes("auto");
    const valido = /^backup_.*_(MANUAL|AUTOMATICO)\.sql(\.gz)?$/i.test(valorMin);
    
    validarInput(e.target, valido, "Archivo no válido o corrupto");
    
    if (valido) {
        const textoDB = esSeguridad ? 'Seguridad' : 'Negocio';
        const tipoRespaldo = esAutomatico ? 'Automática 🤖' : 'Manual 👤';

        renderizarTarjetaDestino(textoDB, tipoRespaldo);

        if(textoBoton) textoBoton.textContent = `Restaurar ${textoDB}`;
        input_file.value = ''; 
    } else {
        infoDiv.innerHTML = '';
        btn.style.display = 'none';
    }
});

// Subir archivo manual (PC)
input_file.addEventListener("change", (e) => {
    const btn = document.getElementById('boton_importar');
    const textoBoton = document.getElementById('texto_boton_importar');
    const infoDiv = document.getElementById('info_seleccion');
    const archivo = e.target.files[0];

    if (!archivo) {
        btn.style.display = 'none';
        infoDiv.innerHTML = '';
        validarInput(e.target, true);
        return;
    }

    const nombre = archivo.name.toLowerCase();
    const extensionValida = /(\.sql|\.sql\.gz|\.gz)$/i.test(nombre);
    
    validarInput(e.target, extensionValida, "Solo se admiten archivos .sql o .gz");

    if (!extensionValida) {
        infoDiv.innerHTML = '';
        btn.style.display = 'none';
        return;
    }

    let dbDetectada = "Desconocido";
    if (nombre.includes("seguridad")) {
        dbDetectada = "Seguridad";
    } else if (nombre.includes("negocio") || nombre.includes("haydee")) {
        dbDetectada = "Negocio";
    }

    const esAutomatico = nombre.includes("automatico") || nombre.includes("auto");
    const tipoRespaldo = esAutomatico ? 'Automática' : 'Manual o Externa';

    select_copias.value = ''; 

    renderizarTarjetaDestino(dbDetectada, tipoRespaldo, archivo.name);

    if (dbDetectada !== "Desconocido" && textoBoton) {
        textoBoton.textContent = `Importar en ${dbDetectada}`;
    }
});

// Botón Exportar (Generar Backup) - Mejorado con pedirConfirmacion
boton_exportar.addEventListener("click", async () => {
    if (!validarSeleccionDB()) return;

    Alertas.pedirConfirmacion(
        "¿Generar Copia de Seguridad?",
        `Se creará un respaldo estructurado de la base de datos: <strong>${select_db.value.toUpperCase()}</strong>.`,
        "question",
        "Sí, generar respaldo",
        "Cancelar"
    ).then((result) => {
        if (result.isConfirmed) {
            generarCopiaSeguridad();
        }
    });
});

// Botón Descargar - Mejorado con pedirConfirmacion
boton_descargar.addEventListener("click", (e) => {
    e.preventDefault();
    if (!validarSeleccionDB()) return;

    Alertas.pedirConfirmacion(
        "¿Descargar Respaldo Directo?",
        `El sistema compilará y descargará un archivo SQL inmediato de la base de datos: <strong>${select_db.value.toUpperCase()}</strong>.`,
        "question",
        "Sí, descargar",
        "Cancelar"
    ).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('db_input').value = select_db.value;
            e.target.closest("form").submit();
        }
    });
});

// Botón Importar (Restaurar) - ¡Solución del BUG del método inexistente!
boton_importar.addEventListener("click", async () => {
    const hayCopiaSeleccionada = select_copias.value !== '';
    const hayArchivoSubido = input_file.value !== '';

    if (!hayCopiaSeleccionada && !hayArchivoSubido) {
        Alertas.mostrar('error', 'Atención', 'Debe seleccionar una Copia de Seguridad o subir un archivo SQL.');
        return;
    }

    Alertas.pedirConfirmacion(
        "¡Advertencia de Seguridad!",
        "<span class='text-danger fw-bold'>Acción crítica de restauración</span>. Esta acción ELIMINARÁ todos los datos actuales de la base de datos destino y los reemplazará por la copia. No se puede revertir.",
        "warning",
        "Sí, restaurar sistema",
        "Cancelar"
    ).then((result) => {
        if (result.isConfirmed) {
            // Enrutamiento dinámico según el origen activo del archivo
            if (hayCopiaSeleccionada) {
                importarCopiaSeguridad();
            } else if (hayArchivoSubido) {
                importarSQL();
            }
        }
    });
});

const iconoBoton = document.getElementById('icono_boton_importar');
if (iconoBoton) {
    boton_importar.addEventListener('mouseenter', () => {
        iconoBoton.classList.replace('bi-arrow-repeat', 'bi-exclamation-triangle');
    });
    boton_importar.addEventListener('mouseleave', () => {
        iconoBoton.classList.replace('bi-exclamation-triangle', 'bi-arrow-repeat');
    });
}

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
    select_copias.innerHTML = ''; 

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

    listaArchivos.sort((a, b) => {
        const regexFecha = /\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/;
        const fechaA = a.match(regexFecha) ? a.match(regexFecha)[0] : "";
        const fechaB = b.match(regexFecha) ? b.match(regexFecha)[0] : "";
        return fechaB.localeCompare(fechaA);
    });

    listaArchivos.forEach(fichero => {
        let option = document.createElement("option");
        option.textContent = formatearNombreArchivo(fichero);
        option.value = fichero;
        fragment.appendChild(option);
    });

    select_copias.appendChild(fragment);
}

function formatearNombreArchivo(fichero) {
    const nombreMin = fichero.toLowerCase();
    const esSeguridad = nombreMin.includes("seguridad");
    const esAuto = nombreMin.includes("automatico");
    const esComprimido = nombreMin.endsWith(".gz");

    const match = fichero.match(/(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/);
    if (match) {
        const fecha = match[1].split('-').reverse().join('/'); 
        const hora = match[2].replace(/-/g, ':'); 
        
        const iconoDB = esSeguridad ? "🛡️" : "🏢";
        const etiquetaDB = esSeguridad ? "SEGURIDAD" : "NEGOCIO";
        const origen = esAuto ? "🤖 AUTO" : "👤 MANUAL";
        const etiquetaCompresion = esComprimido ? "🗜️ GZ" : "📄 SQL";

        return `🔹 ${iconoDB} ${etiquetaDB} [${origen}] | 📅 ${fecha} | 🕒 ${hora} | ${etiquetaCompresion}`;
    }
    return fichero;
}

async function generarCopiaSeguridad() {
    // Alertas.mostrarCargando("Generando Respaldo", "Volcando estructuras y registros de la base de datos...");
    
    let datos = new FormData();
    datos.append("db", select_db.value);
    datos.append('operacion', 'generar_copia_seguridad');

    const respuesta = await Peticiones.enviar(datos);
    // Alertas.cerrar();

    if (respuesta.estatus) {
        Alertas.mostrar('success', '¡Respaldo Creado!', respuesta.mensaje);
        obtenerCopiasGuardadas(); 
    } else {
        Alertas.mostrar('error', 'Error de Volcado', respuesta.mensaje);
    }
}

async function importarCopiaSeguridad() {
    // Alertas.mostrarCargando("Restaurando Sistema", "Procesando sentencias SQL del servidor, por favor espere...");
    
    let datos = new FormData();
    const fichero = select_copias.value;
    const db = determinarDBDestino(fichero);

    datos.append("fichero", fichero);
    datos.append("db", db);
    datos.append('operacion', 'importar_copia_seguridad');

    const respuesta = await Peticiones.enviar(datos);
    // Alertas.cerrar();
    manejarRespuestaImportacion(respuesta);
}

async function importarSQL() {
    // Alertas.mostrarCargando("Subiendo e Importando", "Leyendo archivo e inyectando registros en la base de datos...");
    
    let datos = new FormData();
    const archivo = input_file.files[0];
    const db = determinarDBDestino(archivo.name);

    datos.append("fichero", archivo); 
    datos.append("db", db);
    datos.append('operacion', 'importar_archivo_sql');

    const respuesta = await Peticiones.enviar(datos);
    // Alertas.cerrar();
    manejarRespuestaImportacion(respuesta);
}

function manejarRespuestaImportacion(respuesta) {
    if (respuesta.estatus) {
        // Bloqueo con acción obligatoria para mantener la sanidad del estado de la app
        Alertas.mostrarConAccion(
            'success', 
            'Restauración Completada', 
            respuesta.mensaje + " El sistema necesita recargarse para sincronizar los nuevos datos de forma segura.",
            "Entendido",
            () => { window.location.reload(); }
        );
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

function validarInput(input, esValido, mensajeError = "") {
    if (esValido) {
        input.classList.remove('is-invalid');
        if(input.nextElementSibling) input.nextElementSibling.textContent = "";
    } else {
        input.classList.add('is-invalid');
        if(input.nextElementSibling) input.nextElementSibling.textContent = mensajeError;
    }
}

function verificarErroresURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('e')) {
        const mensajeError = urlParams.get('msg') || 'Ocurrió un error al intentar descargar el archivo.';
        Alertas.mostrar('warning', 'Descarga no disponible', mensajeError);
        
        const currentURL = new URL(window.location.href);
        currentURL.searchParams.delete('e');
        currentURL.searchParams.delete('msg');
        window.history.replaceState({}, '', currentURL);
    }
}

function determinarDBDestino(nombreArchivo) {
    const selectEmergencia = document.getElementById('select_db_emergencia');
    if (selectEmergencia && selectEmergencia.value) {
        return selectEmergencia.value;
    }
    return nombreArchivo.toLowerCase().includes("seguridad") ? 'seguridad' : 'negocio';
}

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

    if (esDesconocido) {
        btn.style.display = 'none'; 
        
        const selectEmergencia = document.getElementById('select_db_emergencia');
        selectEmergencia.addEventListener('change', (e) => {
            const dbManual = e.target.value;
            const textoBoton = document.getElementById('texto_boton_importar');
            
            document.getElementById('badge_destino_final').className = dbManual === 'seguridad' ? 'badge-soft-info rounded-pill fw-bold px-3 py-2' : 'badge-soft-success rounded-pill fw-bold px-3 py-2';
            
            if(textoBoton) textoBoton.textContent = `Importar en ${dbManual.charAt(0).toUpperCase() + dbManual.slice(1)}`;
            btn.style.display = 'inline-block';
        });
    } else {
        btn.style.display = 'inline-block';
    }
}