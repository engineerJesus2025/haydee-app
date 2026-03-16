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

// Función para mostrar el detalle en el modal
function mostrarDetalle(rowData) {
    // 1. Usamos directamente las clases de Bootstrap para coincidir con tus botones
    const colores = { 
            'consultar': "badge-consultar", 
            'eliminar': "bg-danger", 
            'registrar': "bg-primary", 
            'modificar': "bg-success", 
            'iniciar sesion': "badge-iniciar-sesion", 
            'cerrar sesion': "bg-info text-dark",
            'descargar': "bg-warning text-dark",
            'respaldar': "bg-indigo text-white", // Requiere CSS personalizado si usas Bootstrap estándar
            'restaurar': "bg-teal text-white"    // Requiere CSS personalizado si usas Bootstrap estándar
        };
    
    const accionNormalizada = rowData.accion.toLowerCase();
    const claseColor = colores[accionNormalizada] || "bg-secondary";

    // Elementos del DOM
    const divConsulta = document.getElementById('detalle_consulta');
    const iconoConsulta = document.getElementById('icono_consulta');
    const mensajeConsulta = document.getElementById('mensaje_consulta');
    const divCambios = document.getElementById('detalle_cambios');

    // Llenar datos generales
    document.getElementById('detalle_usuario').textContent = rowData.nombre_usuario;
    document.getElementById('detalle_rol').textContent = rowData.nombre_rol;
    document.getElementById('detalle_fecha').textContent = FormatoFechas.formatear(rowData.fecha_hora, 'DD/MM/YYYY hh:mm:ss A');
    document.getElementById('detalle_modulo').textContent = rowData.nombre_modulo.split('_').join(' ');

    const accionBadge = document.getElementById('detalle_accion');
    accionBadge.textContent = rowData.accion;
    accionBadge.className = `badge ${claseColor}`;

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

    const formatoFecha = (cell) => FormatoFechas.formatoUsuario(cell.getValue());
    const formatoModulo = (cell) => cell.getValue().split("_").join(" ");
    
    // 3. Actualizamos los colores de los badges en la tabla principal
    const formatoAccion = (cell) => {
        const accion = cell.getValue();
        const colores = { 
            'consultar': "badge-consultar", 
            'eliminar': "bg-danger", 
            'registrar': "bg-primary", 
            'modificar': "bg-success", 
            'iniciar sesion': "badge-iniciar-sesion", 
            'cerrar sesion': "bg-info text-dark",
            'descargar': "bg-warning text-dark",
            'respaldar': "bg-indigo text-white", // Requiere CSS personalizado si usas Bootstrap estándar
            'restaurar': "bg-teal text-white"    // Requiere CSS personalizado si usas Bootstrap estándar
        };
        const clase = colores[accion.toLowerCase()] || "bg-secondary";
        return `<span class="badge ${clase}">${accion}</span>`;
    };

    const formatoBotones = (cell) => {
        return `
            <button class="btn btn-sm btn-outline-primary ver-detalle" title="Ver detalles">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver detalles</span>
            </button>
        `;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Usuario", field: "nombre_usuario", minWidth: 150, responsive: 0 },
        { title: "Fecha", field: "fecha_hora", formatter: formatoFecha, minWidth: 150 },
        { title: "Módulo", field: "nombre_modulo", formatter: formatoModulo, minWidth: 150 },
        { title: "Acción", field: "accion", formatter: formatoAccion, minWidth: 120, headerHozAlign: "center", hozAlign: "center" },
        {
            title: "DETALLES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 140, responsive: 0, download: false, headerHozAlign: "center",
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

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_bitacora.setFilter([filtros]);
        });
    }
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