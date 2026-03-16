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
        if (row.tabla_origen && row.id_registro_origen) {
            return `<a href="?pagina=${row.tabla_origen}&accion=inicio&buscar=${row.id_registro_origen}" 
                       class="btn btn-sm btn-outline-primary btn-ir-registro" 
                       data-id="${row.id_notificacion}" 
                       data-leido="${row.leido}">
                       <i class="bi bi-box-arrow-up-right me-1"></i> Ver registro
                    </a>`;
        }
        return `<span class="text-muted small">Sin enlace</span>`;
    };

    const columnas = [
        // El botón de colapso (+/-) jamás se oculta
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", responsive: 0 },
        
        // Título: Jamás se oculta (responsive: 0) y toma 1 parte del espacio libre
        { title: "Título", field: "titulo", formatter: formatoTitulo, minWidth: 180, widthGrow: 1, responsive: 0 },
        
        // Descripción: Toma 2 partes del espacio libre (se verá más ancha) pero se oculta si la pantalla es muy pequeña
        { title: "Descripción", field: "descripcion", minWidth: 200, widthGrow: 2, responsive: 1 },
        
        // Fecha y Estado: Son los primeros en ocultarse si falta espacio (responsive: 2)
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 110, responsive: 2 },
        { title: "Estado", field: "leido", formatter: formatoLeido, minWidth: 110, headerHozAlign: "center", hozAlign: "center", responsive: 2 },
        
        // Acción: JAMÁS se oculta (responsive: 0) para que el usuario siempre pueda hacer clic
        { title: "Acción", formatter: formatoBotones, headerSort: false, hozAlign: "center", minWidth: 140, responsive: 0 }
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
    contenedor.addEventListener('click', async (e) => {
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
                
                // Redirigimos
                window.location.href = btnIr.href;
            }
        }
    });
}