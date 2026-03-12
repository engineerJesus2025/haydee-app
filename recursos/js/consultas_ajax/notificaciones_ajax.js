let tabla_notificaciones;

document.addEventListener('DOMContentLoaded', consultar);

async function consultar() {
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoLeido = (cell) => {
        const leido = cell.getValue();
        return `<span class="${leido == 1 ? 'badge bg-primary' : 'badge bg-warning text-dark'}">${leido == 1 ? 'Sí' : 'No'}</span>`;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Título", field: "titulo", minWidth: 150, responsive: 0 },
        { title: "Descripción", field: "descripcion", minWidth: 250 },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 120 },
        { title: "Leído", field: "leido", formatter: formatoLeido, minWidth: 100, headerHozAlign: "center", hozAlign: "center" }
    ];

    tabla_notificaciones = Tablas.cargarTabulador(contenedor.id, "", columnas, { parametrosExtra: { operacion: 'consultar' } });

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_notificaciones.setFilter([filtros]);
        });
    }
}