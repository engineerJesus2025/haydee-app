/**
 * bitacora_ajax.js
 * Gestión de Bitácora - Peticiones AJAX
 * Dependencias: utilidades.js, formatoFechas.js
 */

let tabla_bitacora;
let modal_carga = new bootstrap.Modal("#modal_carga");
let modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalleBitacora'));

window.addEventListener('DOMContentLoaded', () => {
    eventosCargaDataTable('tabla_bitacora', modal_carga);
    consultar();
});

document.getElementById('header-toggle')?.addEventListener("click", () => {
    setTimeout(() => {
        tabla_bitacora?.columns.adjust().draw();
    }, 450);
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
    // Información general
    document.getElementById('detalle_usuario').textContent = rowData.nombre_usuario;
    document.getElementById('detalle_rol').textContent = rowData.nombre_rol;
    document.getElementById('detalle_fecha').textContent = FormatoFechas.formatear(rowData.fecha_hora, 'DD/MM/YYYY hh:mm:ss A');
    document.getElementById('detalle_modulo').textContent = rowData.nombre_modulo.split('_').join(' ');
    document.getElementById('detalle_accion').textContent = rowData.accion;
    document.getElementById('detalle_accion').setAttribute('class',`badge ${definirColorAccion(rowData.accion)}`);

    // Determinar si es consulta
    if (rowData.accion.toLowerCase() === 'consultar' || rowData.accion.toLowerCase() === 'iniciar sesion' || rowData.accion.toLowerCase() === 'cerrar sesion') {
        document.getElementById('detalle_consulta').classList.remove('d-none');
        document.getElementById('detalle_cambios').classList.add('d-none');

        if (rowData.accion.toLowerCase() === 'consultar') document.getElementById('mensaje_consulta').textContent = `Se consultaron todos los registros del módulo ${rowData.nombre_modulo.split('_').join(' ')}.`;
        else if(rowData.accion.toLowerCase() === 'iniciar sesion') document.getElementById('mensaje_consulta').textContent = `Incio de sesión exitoso.`;
        else if(rowData.accion.toLowerCase() === 'cerrar sesion') document.getElementById('mensaje_consulta').textContent = `Cierre de sesión exitoso.`;
    } 
    else {
        document.getElementById('detalle_consulta').classList.add('d-none');
        document.getElementById('detalle_cambios').classList.remove('d-none');

        // Mostrar valores anteriores y nuevos (si existen)
        let anteriores = rowData.valores_anteriores ? JSON.parse(rowData.valores_anteriores) : {};
        let nuevos = rowData.valores_nuevos ? JSON.parse(rowData.valores_nuevos) : {};

        if (rowData.accion.toLowerCase() === 'registrar') {
            document.getElementById('anteriores-tab').style.display = 'none';
            document.getElementById('nuevos-tab').style.display = 'block';
            document.getElementById('nuevos-tab').click(); // activar pestaña nuevos
        } else if (rowData.accion.toLowerCase() === 'eliminar') {
            document.getElementById('anteriores-tab').style.display = 'block';
            document.getElementById('nuevos-tab').style.display = 'none';
            document.getElementById('anteriores-tab').click(); // activar anteriores
        } else {
            // editar, ambas visibles
            document.getElementById('anteriores-tab').style.display = 'block';
            document.getElementById('nuevos-tab').style.display = 'block';
        }

        document.getElementById('valores_anteriores').innerHTML = objetoALista(anteriores);
        document.getElementById('valores_nuevos').innerHTML = objetoALista(nuevos);
    }

    modalDetalle.show();
}

/**
 * Configura eventos para mostrar el modal de carga durante las peticiones de DataTable
 */
function eventosCargaDataTable(id_tabla, modal) {
    const tiempoMinimoCarga = 700;
    let inicioPeticion;
    let temporizadorModal;
    let modalVisible = false;

    $('#' + id_tabla).on("preXhr.dt", function (e, settings, data) {
        inicioPeticion = new Date().getTime();
        temporizadorModal = setTimeout(() => {
            modal.show();
            modalVisible = true;
        }, 200);
    });

    $('#' + id_tabla).on("xhr.dt", function (e, settings, json, xhr) {
        clearTimeout(temporizadorModal);
        const finPeticion = new Date().getTime();
        const tiempoTranscurrido = finPeticion - inicioPeticion;

        if (modalVisible && tiempoTranscurrido < tiempoMinimoCarga) {
            const tiempoEspera = tiempoMinimoCarga - tiempoTranscurrido;
            setTimeout(() => {
                modal.hide();
                modalVisible = false;
            }, tiempoEspera);
        } else if (modalVisible) {
            modal.hide();
            modalVisible = false;
        }
    });
}

/**
 * Define el estilo de la badge según la acción
 */
function definirColorAccion(nombre_accion) {
    const colores = {
        'consultar': "badge-consultar",
        'eliminar': "badge-eliminar",
        'registrar': "badge-registrar",
        'modificar': "badge-modificar",
        'iniciar sesion': "badge-iniciar-sesion",
        'cerrar sesion': "badge-cerrar-sesion"
    };
    return colores[nombre_accion] || "badge bg-secondary";
}

/**
 * Inicializa DataTable con los registros de bitácora
 */
function consultar() {
    const columnas = [
        { data: "nombre_usuario" },
        {
            data: "fecha_hora",
            render: (data) => FormatoFechas.formatoUsuario(data)
        },
        {
            data: "nombre_modulo",
            render: (data) => data.split("_").join(" ")
        },
        {
            data: "accion",
            render: (data) => `<span class="badge ${definirColorAccion(data)}">${data}</span>`
        },
        {
            data: null,
            render: (row) => `
                <button class="btn btn-sm btn-outline-primary ver-detalle" data-id="${row.id_bitacora}">
                    <i class="bi bi-eye"></i> Ver detalles
                </button>
            `,
            orderable: false
        }
    ];

    const parametrosConsulta = (data) => {
        data.operacion = 'consulta';
    };

    const configuracionFila = (row, data) => {
        row.querySelector('.ver-detalle')?.addEventListener('click', () => mostrarDetalle(data));
    };

    tabla_bitacora = Utilidades.crearDataTable(
        'tabla_bitacora',
        columnas,
        parametrosConsulta,
        configuracionFila
    );
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