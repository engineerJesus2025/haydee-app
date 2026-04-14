/**
 * bitacora_ajax.js
 * Gestión de Bitácora - Peticiones AJAX
 * Dependencias: utilidades.js, formatoFechas.js
 */

let tabla_bitacora;
let modal_carga = new bootstrap.Modal("#modal_carga");
let modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalleBitacora'));

window.addEventListener('DOMContentLoaded', () => {
    consultar();
});

// Función para formatear JSON de manera legible
function formatearJSON(jsonString) {
    if (!jsonString || jsonString === '{}') return 'No hay datos';
    try {
        let obj = JSON.parse(jsonString);
        return JSON.stringify(obj, null, 2);
    } catch (e) {
        return jsonString; // Si no es JSON válido, mostrarlo como texto
    }
}
// Formato de Acción
function formatoAccion(cell) {
    let accion;
    if (typeof cell.getValue === "function") {
        accion = (cell.getValue()).toLowerCase();
    }
    else {
     accion = (cell).toLowerCase();
    }
    
    let color = "secondary";
    let icono = "bi-activity";

    // Asignación semántica de colores e íconos
    switch (accion) {
        case 'consultar':      color = "info";    icono = "bi-search"; break;
        case 'registrar':      color = "primary"; icono = "bi-plus-circle-fill"; break;
        case 'modificar':      color = "success"; icono = "bi-pencil-fill"; break;
        case 'eliminar':       color = "danger";  icono = "bi-trash-fill"; break;
        case 'iniciar sesion': color = "iniciar-sesion"; icono = "bi-box-arrow-in-right"; break;
        case 'cerrar sesion':  color = "secondary"; icono = "bi-box-arrow-left"; break;
        case 'descargar':      color = "warning";    icono = "bi-download"; break;
        case 'respaldar':      color = "indigo"; icono = "bi-database-down"; break;
        case 'restaurar':      color = "teal"; icono = "bi-database-up"; break;
    }

    // Capitalizar la primera letra ("Iniciar sesion", "Registrar")
    const texto = accion.charAt(0).toUpperCase() + accion.slice(1);

    let claseTextoBorder;
    if (["primary", "success", "danger", "teal"].includes(color)) {
        claseTextoBorder = `text-${color} border border-${color}`;
    }
    else if (["warning", "info", "secondary"].includes(color)) {
        claseTextoBorder = `text-dark border border-${color}`;
    }
    else if (["iniciar-sesion", "indigo"].includes(color)) {
        claseTextoBorder = `border border-dark`;
    }

    return `<span class="badge bg-${color} bg-opacity-10 ${claseTextoBorder} px-3 py-2 shadow-sm text-nowrap" style="font-size: .85rem;">
                <i class="bi ${icono} me-1"></i> ${texto}
            </span>`;
};

// Función para mostrar el detalle en el modal
function mostrarDetalle(rowData) {
    const accionNormalizada = rowData.accion.toLowerCase();

    // Elementos del DOM
    const divConsulta = document.getElementById('detalle_consulta');
    const iconoConsulta = document.getElementById('icono_consulta');
    const mensajeConsulta = document.getElementById('mensaje_consulta');
    const divCambios = document.getElementById('detalle_cambios');

    let contenedorImg = document.getElementById("contenedor_imagen_bitacora");
    let imgElement = document.getElementById("imagen_bitacora");
    let msgErrorImg = document.getElementById("mensaje_error_img_bitacora");

    // Resetear el estado de la imagen
    contenedorImg.classList.add("d-none");
    imgElement.style.display = "inline-block";
    imgElement.src = "";
    msgErrorImg.classList.add("d-none");

    let nombreImagenDetectada = null;

    // Buscar imagen en valores nuevos (Registros y Modificaciones)
    try {
        if (rowData.valores_nuevos) {
            let objNuevos = JSON.parse(rowData.valores_nuevos);
            if (objNuevos.imagen) nombreImagenDetectada = objNuevos.imagen;
        }
    } catch (e) {}

    // Si no hay en nuevos, buscar en anteriores (Eliminaciones)
    if (!nombreImagenDetectada) {
        try {
            if (rowData.valores_anteriores) {
                let objAnt = JSON.parse(rowData.valores_anteriores);
                if (objAnt.imagen) nombreImagenDetectada = objAnt.imagen;
            }
        } catch (e) {}
    }

    // Si se encontró un nombre de imagen, armamos la ruta
    if (nombreImagenDetectada) {
        // Normalizamos el nombre del módulo para que coincida con la carpeta (ej: "CARTELERA_VIRTUAL" -> "cartelera_virtual")
        let carpetaModulo = rowData.nombre_modulo.toLowerCase().split("gestionar_")[1];
        
        // Asignamos la ruta y mostramos el contenedor
        imgElement.src = `recursos/img/${carpetaModulo}/${nombreImagenDetectada}`;
        contenedorImg.classList.remove("d-none");
    }

    // Llenar datos generales
    document.getElementById('detalle_usuario').textContent = rowData.nombre_usuario;
    document.getElementById('detalle_rol').textContent = rowData.nombre_rol;
    document.getElementById('detalle_fecha').textContent = FormatoFechas.formatear(rowData.fecha_hora, 'DD/MM/YYYY hh:mm:ss A');
    document.getElementById('detalle_modulo').textContent = rowData.nombre_modulo.split('_').join(' ');

    document.getElementById('detalle_accion').innerHTML = formatoAccion(rowData.accion);

    const accionesDeSoloMensaje = ['consultar', 'iniciar sesion', 'cerrar sesion', 'descargar', 'respaldar', 'restaurar'];

    if (accionesDeSoloMensaje.includes(accionNormalizada)) {
        divConsulta.classList.remove('d-none');
        divCambios.classList.add('d-none');

        if (accionNormalizada === 'descargar') {
            // --- CASO DESCARGAR: Quitamos el estilo de alerta ---
            divConsulta.classList.remove('alert', 'alert-info', 'align-items-center');
            iconoConsulta.classList.add('d-none'); // Ocultamos el icono de info azul

            let detalles = {};
            try { detalles = JSON.parse(rowData.valores_nuevos || '{}'); } catch(e) {}

            let contenidoHtml = `
                <div class="border rounded-3 p-4 bg-light shadow-sm">
                    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                            <i class="bi bi-file-earmark-text-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">DETALLES DEL DOCUMENTO</h6>
                            <small class="text-muted">Información del reporte generado</small>
                        </div>
                    </div>
                    <div class="row g-4">
            `;

            for (let [llave, valor] of Object.entries(detalles)) {
                let etiqueta = llave.replace(/_/g, ' ').toUpperCase();
                contenidoHtml += `
                    <div class="col-sm-6">
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">${etiqueta}</p>
                        <p class="mb-0 fw-bold text-dark">${valor || 'N/A'}</p>
                    </div>
                `;
            }
            contenidoHtml += `</div></div>`;
            mensajeConsulta.innerHTML = contenidoHtml;

        } else {
            // --- CASO CONSULTAR/OTROS: Restauramos el estilo de alerta ---
            divConsulta.classList.add('alert', 'alert-info', 'align-items-center');
            iconoConsulta.classList.remove('d-none');

            const mensajes = {
                'consultar': `Se visualizaron los registros del módulo.`,
                'iniciar sesion': `Acceso al sistema concedido.`,
                'cerrar sesion': `Sesión finalizada correctamente.`,
                'respaldar': `Copia de seguridad generada con éxito.`,
                'restaurar': `Restauración de base de datos completada.`
            };
            mensajeConsulta.innerHTML = mensajes[accionNormalizada] || 'Acción realizada correctamente.';
        }

    } else {
        // Lógica para Registrar (Azul), Modificar (Verde), Eliminar (Rojo)
        divConsulta.classList.add('d-none');
        divCambios.classList.remove('d-none');

        let anteriores = rowData.valores_anteriores ? JSON.parse(rowData.valores_anteriores) : {};
        let nuevos = rowData.valores_nuevos ? JSON.parse(rowData.valores_nuevos) : {};

        if (accionNormalizada === 'registrar') {
            document.getElementById('anteriores-tab').parentElement.style.display = 'none';
            document.getElementById('nuevos-tab').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab').click(); 
        } else if (accionNormalizada === 'eliminar') {
            document.getElementById('anteriores-tab').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab').parentElement.style.display = 'none';
            document.getElementById('anteriores-tab').click(); 
        } else { 
            document.getElementById('anteriores-tab').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab').click();
        }

        document.getElementById('valores_anteriores').innerHTML = objetoALista(anteriores);
        document.getElementById('valores_nuevos').innerHTML = objetoALista(nuevos);
    }

    modalDetalle.show();    
}

/**
 * Inicializa DataTable con los registros de bitácora
 */
function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // 1. Formato de Usuario (Capitalizado + Ícono)
    const formatoUsuario = (cell) => {
        let nombre = cell.getValue() || "";
        nombre = nombre.charAt(0).toUpperCase() + nombre.slice(1).toLowerCase();
        return `<div class="d-flex align-items-center fw-bold text-dark">
                    <i class="bi bi-person-circle text-primary me-2 fs-5"></i> ${nombre}
                </div>`;
    };

    // 2. Formato de Módulo (Limpieza de guiones bajos y Capitalización)
    const formatoModulo = (cell) => {
        let modulo = cell.getValue() || "";
        // Convierte "GESTIONAR_ROLES" en "Gestionar Roles"
        modulo = modulo.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        return `<span class="text-muted fw-semibold">
                    <i class="bi bi-box-seam me-2 opacity-50"></i>${modulo}
                </span>`;
    };

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());

    const formatoBotones = (cell) => {
        return `
            <button class="btn btn-sm btn-primary ver-detalle" title="Ver detalles">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver detalles</span>
            </button>
        `;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Usuario", field: "nombre_usuario", formatter: formatoUsuario, minWidth: 150, responsive: 0 },
        { title: "Acción", field: "accion", formatter: formatoAccion, minWidth: 150, headerHozAlign: "center", hozAlign: "center" },
        { title: "Fecha", field: "fecha_hora", formatter: formatoFecha, minWidth: 160 },
        { title: "Módulo", field: "nombre_modulo", formatter: formatoModulo, minWidth: 220, widthGrow: 2, },
        {
            title: "DETALLES", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 130, 
            responsive: 0, download: false, headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                if (btn.classList.contains('ver-detalle')) {
                    mostrarDetalle(cell.getData());
                }
            }
        }
    ];

    tabla_bitacora = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consulta' } });

    Tablas.inicializarBuscadorGlobal(tabla_bitacora, "busqueda_global", columnas);
}

function objetoALista(obj) {
    if (!obj || Object.keys(obj).length === 0) {
        return '<p class="text-muted">No hay datos</p>';
    }
    let html = '<ul class="list-group">';
    for (let [key, value] of Object.entries(obj)) {
        // Formatear el valor si es objeto o array
        let valorMostrar = value;
        if (typeof value === 'object' && value !== null) {
            valorMostrar = JSON.stringify(value);
        }
        html += `<li class="list-group-item"><strong>${key}:</strong> ${valorMostrar}</li>`;
    }
    html += '</ul>';
    return html;
}