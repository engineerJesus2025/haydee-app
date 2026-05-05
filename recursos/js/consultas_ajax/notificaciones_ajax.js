/**
 * notificaciones_ajax.js
 */
let tabla_notificaciones;

document.addEventListener('DOMContentLoaded', consultar);

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // Formateador visual para destacar las no leídas
    // Formateador visual para el Título (Capitalizado y con jerarquía visual)
    const formatoTitulo = (cell) => {
        const row = cell.getData();
        let titulo = cell.getValue() || "";
        titulo = titulo.charAt(0).toUpperCase() + titulo.slice(1);
        
        if (row.leido == 0) {
            // No leída: Texto oscuro, negrita y un indicador rojo vibrante
            return `<div class="d-flex align-items-center fw-bold">
                        <span class="bg-danger rounded-circle d-inline-block me-3 shadow-sm" style="width: 8px; height: 8px;"></span>
                        ${titulo}
                    </div>`;
        } else {
            // Leída: Texto atenuado y sin indicador
            return `<div class="d-flex align-items-center text-muted">
                        <span class="d-inline-block me-3" style="width: 8px;"></span>
                        ${titulo}
                    </div>`;
        }
    };

    // Formateador para el Estado (Soft Badges)
    const formatoLeido = (cell) => {
        const leido = cell.getValue();
        if (leido == 1) {
            // Leída -> Gris (Secondary)
            return ComponentesUI.crearSoftBadge('secondary', 'bi-check2-all', 'Leída');
        } else {
            // Nueva -> Azul (Primary)
            return ComponentesUI.crearSoftBadge('primary', 'bi-envelope-exclamation-fill', 'Nueva');
        }
    };

    // Botón para redirigir al módulo origen
    const formatoBotones = (cell) => {
        const row = cell.getData();
        const id = row.id_notificacion;
        
        let btnIr = '';
        if (row.tabla_origen && row.id_registro_origen) {
            let ruta = `?pagina=${row.tabla_origen}&buscar=${row.id_registro_origen}`;
            btnIr = `<button type="button" class="btn btn-outline-primary btn-sm btn-ir-registro" data-id="${id}" data-leido="${row.leido}" data-href="${ruta}" title="Ir al registro" data-tooltip="true">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span class="d-none d-lg-inline ms-2">Ir al Registro</span>
                     </button>`;
        }

        return `<div class="d-flex justify-content-center flex-wrap gap-2">
                    <button type="button" class="btn btn-primary btn-sm vista-previa" value="${id}" title="Ver Detalles" data-tooltip="true">
                        <i class="bi bi-eye"></i>
                        <span class="d-none d-lg-inline ms-2">Detalles</span>
                    </button>
                    ${btnIr}
                </div>`;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", responsive: 0 },
        { title: "Título", field: "titulo", formatter: formatoTitulo, minWidth: 190, widthGrow: 3, responsive: 0 },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 110,},
        { title: "Estado", field: "leido", formatter: formatoLeido, minWidth: 110, headerHozAlign: "center", hozAlign: "center", },
        
        { title: "Acción", formatter: formatoBotones, headerSort: false, hozAlign: "center", minWidth: 140, responsive: 0, widthGrow: 2,  }
    ];

    tabla_notificaciones = Tablas.cargarTabulador(contenedor.id, "", columnas, { 
        parametrosExtra: { operacion: 'consultar' } 
    });

    // Definimos la lógica de búsqueda específica para Notificaciones
    const filtroEspecialNotificaciones = (data, valorBuscado) => {
        let fechaFormateada = FormatoFechas.formatoUsuario(data.fecha).toLowerCase();
        let estadoTexto = data.leido == 1 ? "leída leida" : "no leída nueva";
        
        return String(data.titulo).toLowerCase().includes(valorBuscado) || 
               String(data.descripcion).toLowerCase().includes(valorBuscado) || 
               fechaFormateada.includes(valorBuscado) || 
               estadoTexto.includes(valorBuscado);
    };

    // Llamamos al Helper pasándole la función
    Tablas.inicializarBuscadorGlobal(tabla_notificaciones, "busqueda_global", columnas, filtroEspecialNotificaciones);

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
    // Título (Capitalizado)
    let titulo = data.titulo || 'Sin Título';
    document.getElementById("vp_titulo").textContent = titulo.charAt(0).toUpperCase() + titulo.slice(1);

    // Estado (Usando Helper)
    const estadoEl = document.getElementById("vp_estado");
    estadoEl.className = "mt-2 d-inline-block fs-6"; // Agregamos el tamaño y margen como contenedor
    
    if (data.leido == 1) {
        estadoEl.innerHTML = ComponentesUI.crearSoftBadge('secondary', 'bi-check2-all', 'Notificación Leída');
    } else {
        estadoEl.innerHTML = ComponentesUI.crearSoftBadge('primary', 'bi-envelope-exclamation-fill', 'Nueva (No Leída)');
    }

    // Tipo de Evento (Normalizamos y le damos un toque visual)
    const tipoEvento = data.tipo_evento ? data.tipo_evento.replace(/_/g, ' ') : 'General';
    // Capitalizar cada palabra para que "saldo bajo" se vea "Saldo Bajo"
    const tipoCapitalizado = tipoEvento.replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById("vp_tipo_evento").textContent = tipoCapitalizado;

    // Fecha
    if (window.FormatoFechas && typeof FormatoFechas.formatoUsuario === "function") {
        document.getElementById("vp_fecha").textContent = FormatoFechas.formatoUsuario(data.fecha);
    } else {
        document.getElementById("vp_fecha").textContent = data.fecha || '---';
    }

    // Descripción
    document.getElementById("vp_descripcion").textContent = data.descripcion || 'Sin descripción detallada.';

    // Mostrar el Modal
    let modalDetalles = bootstrap.Modal.getInstance(document.getElementById("modal_detalles"));
    if (!modalDetalles) {
        modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });
    }
    modalDetalles.show();
}