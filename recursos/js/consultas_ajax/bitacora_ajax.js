/**
 * bitacora_ajax.js
 * Gestión de Bitácora - Peticiones AJAX
 * Dependencias: utilidades.js, formatoFechas.js
 */

let tabla_bitacora;
let modal_carga = new bootstrap.Modal("#modal_carga");

window.addEventListener('DOMContentLoaded', () => {
    eventosCargaDataTable('tabla_bitacora', modal_carga);
    consultar();
});

document.getElementById('header-toggle')?.addEventListener("click", () => {
    setTimeout(() => {
        tabla_bitacora?.columns.adjust().draw();
    }, 450);
});

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
        'consultar': "badge bg-info text-dark",
        'eliminar': "badge bg-danger",
        'registrar': "badge bg-primary",
        'modificar': "badge bg-success",
        'iniciar sesion': "badge bg-warning text-dark",
        'cerrar sesion': "badge bg-secondary"
    };
    return colores[nombre_accion] || "badge bg-secondary";
}

/**
 * Inicializa DataTable con los registros de bitácora
 */
function consultar() {
    const columnas = [
        { data: "nombre_usuario" },
        { data: "nombre_rol" },
        {
            data: "fecha_hora",
            render: (data) => FormatoFechas.formatear(data, 'hh:mm:ss A DD-MM-YYYY')
        },
        {
            data: "nombre_modulo",
            render: (data) => data.split("_").join(" ")
        },
        {
            data: "accion",
            render: (data) => `<span class="${definirColorAccion(data)}">${data}</span>`
        },
        { data: "registro_alterado" }
    ];

    const parametrosConsulta = (data) => {
        data.operacion = 'consulta';
    };

    const configuracionFila = (row) => {
        Array.from(row.children).forEach(td => td.classList.add('align-middle'));
    };

    tabla_bitacora = Utilidades.crearDataTable(
        'tabla_bitacora',
        columnas,
        parametrosConsulta,
        configuracionFila
    );
}