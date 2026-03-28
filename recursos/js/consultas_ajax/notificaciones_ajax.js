/**
 * notificaciones_ajax.js
 */
let tabla_notificaciones;

document.addEventListener('DOMContentLoaded', consultar);

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // Formateador visual para destacar las no leídas
    const formatoTitulo = (cell) => {
        const row = cell.getData();
        const titulo = cell.getValue();
        // Agrega un puntito rojo al inicio si no está leída
        return row.leido == 0 
            ? `<span class="bg-danger rounded-circle d-inline-block me-2 shadow-sm" style="width: 8px; height: 8px;"></span><strong>${titulo}</strong>` 
            : `<span class="d-inline-block me-2" style="width: 8px;"></span>${titulo}`; // Espacio en blanco para alinear con las leídas
    };

    const formatoLeido = (cell) => {
        const leido = cell.getValue();
        return `<span class="${leido == 1 ? 'badge bg-light text-secondary border' : 'badge bg-warning text-dark'}">${leido == 1 ? 'Leída' : 'No leída'}</span>`;
    };

    // Botón para redirigir al módulo origen
    const formatoBotones = (cell) => {
        const row = cell.getData();
        const id = row.id_notificacion;
        
        let btnIr = '';
        if (row.tabla_origen && row.id_registro_origen) {
            let ruta = `?pagina=${row.tabla_origen}&buscar=${row.id_registro_origen}`;
            btnIr = `<button type="button" class="btn btn-outline-primary btn-sm btn-ir-registro" data-id="${id}" data-leido="${row.leido}" data-href="${ruta}" title="Ir al registro">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span class="d-none d-lg-inline ms-2">Ir al Registro</span>
                     </button>`;
        }

        return `<div class="d-flex justify-content-center flex-wrap gap-2">
                    <button type="button" class="btn btn-primary btn-sm vista-previa" value="${id}" title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                        <span class="d-none d-lg-inline ms-2">Ver</span>
                    </button>
                    ${btnIr}
                </div>`;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", responsive: 0 },
        { title: "Título", field: "titulo", formatter: formatoTitulo, minWidth: 200, widthGrow: 3, responsive: 0 },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 110,},
        { title: "Estado", field: "leido", formatter: formatoLeido, minWidth: 110, headerHozAlign: "center", hozAlign: "center", },
        
        { title: "Acción", formatter: formatoBotones, headerSort: false, hozAlign: "center", minWidth: 140, responsive: 0, widthGrow: 2,  }
    ];

    tabla_notificaciones = Tablas.cargarTabulador(contenedor.id, "", columnas, { 
        parametrosExtra: { operacion: 'consultar' } 
    });

    // Filtro de búsqueda inteligente
    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim().toLowerCase();
            
            if (valor === "") {
                tabla_notificaciones.clearFilter();
                return;
            }

            tabla_notificaciones.setFilter(function(data) {
                let fechaFormateada = FormatoFechas.formatoUsuario(data.fecha).toLowerCase();
                let estadoTexto = data.leido == 1 ? "leída leida" : "no leída nueva";
                
                return String(data.titulo).toLowerCase().includes(valor) || 
                       String(data.descripcion).toLowerCase().includes(valor) || 
                       fechaFormateada.includes(valor) || 
                       estadoTexto.includes(valor);
            });
        });
    }

    // Interceptar el clic en "Ver registro" para marcar como leída antes de saltar
    // Interceptar los clics en los botones de la tabla
    contenedor.addEventListener('click', async (e) => {
        
        // --- Lógica del botón "Ver" (Vista Previa) ---
        const btnVer = e.target.closest('.vista-previa');
        if (btnVer) {
            const idNotif = btnVer.value;
            
            // CORRECCIÓN: Buscamos el dato en el arreglo general de la tabla, 
            // evitando el error de getRow() no definido.
            const rowData = tabla_notificaciones.getData().find(row => row.id_notificacion == idNotif);
            
            if (rowData) {
                mostrarVistaPrevia(rowData);
            } else {
                console.error("No se encontraron los datos de la notificación.");
            }
            return; // Detenemos la ejecución aquí
        }

        // --- Lógica del botón "Ir" (Redirección) ---
        const btnIr = e.target.closest('.btn-ir-registro');
        if (btnIr) {
            const isLeido = btnIr.getAttribute('data-leido') == 1;
            
            // Si no está leída, avisamos al servidor antes de redirigir
            if (!isLeido) {
                e.preventDefault(); 
                const idNotif = btnIr.getAttribute('data-id');
                
                const datos = new FormData();
                datos.append('operacion', 'marcar_como_leido');
                datos.append('id', idNotif);
                
                // Petición silenciosa (sin loader)
                await Peticiones.enviar(datos, '?pagina=notificaciones&accion=marcar_como_leido', false);
                
                // Redirigimos usando el data-href que ya tiene la variable buscar
                window.location.href = btnIr.getAttribute('data-href');
            } else {
                window.location.href = btnIr.getAttribute('data-href');
            }
        }
    });
}

function mostrarVistaPrevia(data) {
    // Título
    document.getElementById("vp_titulo").textContent = data.titulo || 'Sin Título';

    // Estado (Leído / No Leído con colores)
    const estadoEl = document.getElementById("vp_estado");
    if (data.leido == 1) {
        estadoEl.textContent = 'Notificación Leída';
        estadoEl.className = "badge bg-success fs-6 px-3 py-1 mt-2 shadow-sm";
    } else {
        estadoEl.textContent = 'Nueva (No Leída)';
        estadoEl.className = "badge bg-danger fs-6 px-3 py-1 mt-2 shadow-sm";
    }

    // Tipo de Evento (Normalizamos reemplazando guiones bajos)
    const tipoEvento = data.tipo_evento ? data.tipo_evento.replace(/_/g, ' ') : 'General';
    document.getElementById("vp_tipo_evento").textContent = tipoEvento;

    // Fecha
    if (window.FormatoFechas && typeof FormatoFechas.formatoUsuario === "function") {
        document.getElementById("vp_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha);
    } else {
        document.getElementById("vp_fecha").textContent = data.fecha || '---';
    }

    // Descripción
    document.getElementById("vp_descripcion").textContent = data.descripcion || 'Sin descripción detallada.';

    // Mostrar el Modal
    // Aseguramos que la instancia del modal exista
    let modalDetalles = bootstrap.Modal.getInstance(document.getElementById("modal_detalles"));
    if (!modalDetalles) {
        modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
    }
    modalDetalles.show();
}