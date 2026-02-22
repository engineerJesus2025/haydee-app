let tabla_notificaciones;

document.addEventListener('DOMContentLoaded', consultar);

document.getElementById('header-toggle')?.addEventListener('click', () => {
    setTimeout(() => {
        tabla_notificaciones?.columns.adjust().draw();
    }, 450);
});

async function consultar() {
    const parametros = (data) => { data.operacion = 'consultar'; };
    const estructura = [
        { data: 'titulo' },
        { data: 'descripcion' },
        {
            data: 'fecha',
            render: fecha => FormatoFechas.formatoFechaHora(fecha)
        },
        {
            data: 'leido',
            render: leido => {
                const span = document.createElement('span');
                span.className = leido == 1 ? 'badge bg-primary' : 'badge bg-warning text-dark';
                span.textContent = leido == 1 ? 'Sí' : 'No';
                return span.outerHTML;
            }
        }
    ];

    const configPost = (row) => {
        Array.from(row.children).forEach(td => td.classList.add('align-middle'));
    };

    tabla_notificaciones = Utilidades.crearDataTable(
        'tabla_notificaciones',
        estructura,
        parametros,
        configPost
    );
}