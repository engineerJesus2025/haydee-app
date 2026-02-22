/**
 * reporte_habitantes.js
 * Reporte estadístico de habitantes con gráficos
 * Dependencias: utilidades.js, validaciones.js, chart.js, html2canvas, jspdf
 */

let graficoSexo, graficoVivienda, graficoEdades;
let elementosEstadisticos = {
    total_habitantes: 0,
    total_propietarios: 0,
    total_hombres: 0,
    total_mujeres: 0,
    menores_edad: 0,
    adultos_jovenes: 0,
    adultos: 0,
    adultos_mayores: 0
};
const modal = new bootstrap.Modal(document.getElementById('modal_reporte_habitantes'));

// Lógica de interfaz de filtros
document.getElementById('filtro_tiempo')?.addEventListener('change', function() {
    const esPersonalizado = this.value === 'personalizado';
    document.getElementById('label_fechas_habitantes').hidden = !esPersonalizado;
    document.getElementById('div_fecha_inicio_habitantes').hidden = !esPersonalizado;
    document.getElementById('div_fecha_cierre_habitantes').hidden = !esPersonalizado;
});

document.getElementById('rango_edades')?.addEventListener('change', function() {
    const esPersonalizado = this.value === 'personalizado';
    document.getElementById('div_edad_minima').hidden = !esPersonalizado;
    document.getElementById('div_edad_maxima').hidden = !esPersonalizado;
});

// Generación del reporte
document.getElementById('boton_generar_reporte').addEventListener('click', async function() {
    const contenidoModal = document.getElementById('contenido_reporte_habitantes');
    modal.show();
    contenidoModal.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
            <p class="mt-2">Generando reporte...</p>
        </div>`;

    const form = document.getElementById('form_reporte_habitantes');
    const formData = new FormData(form);
    formData.append('operacion', 'consultar_habitantes');

    try {
        const respuesta = await Utilidades.query(formData);
        if (!respuesta.estatus) {
            throw new Error(respuesta.mensaje);
        }
        const datos = respuesta.datos || [];

        contenidoModal.innerHTML = '';
        if (datos.length === 0) {
            contenidoModal.innerHTML = '<p class="text-center p-5">No se encontraron resultados con los filtros seleccionados.</p>';
            return;
        }

        // Crear canvas
        contenidoModal.innerHTML = `
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4"><canvas id="graficoSexo"></canvas></div>
                <div class="col-md-6 col-lg-4 mb-4"><canvas id="graficoTipoVivienda"></canvas></div>
                <div class="col-md-12 col-lg-4 mb-4"><canvas id="graficoEdades"></canvas></div>
            </div>
        `;

        // Destruir gráficos anteriores
        if (graficoSexo) graficoSexo.destroy();
        if (graficoVivienda) graficoVivienda.destroy();
        if (graficoEdades) graficoEdades.destroy();

        crearGraficoSexo(datos);
        crearGraficoTipoVivienda(datos);
        crearGraficoPorEdades(datos);

        const modoVisual = document.getElementById('select_mostrar_datos').value;
        if (modoVisual === "solo_texto") {
            document.querySelector('#graficoSexo').parentElement.parentElement.setAttribute("hidden", "");
            crearContenedorEstadistica();
            document.getElementById('contenedor_estadistica').removeAttribute("hidden");
        } else if (modoVisual === "grafico_texto") {
            crearContenedorEstadistica();
            document.getElementById('contenedor_estadistica').removeAttribute("hidden");
            document.querySelector('#graficoSexo').parentElement.parentElement.removeAttribute("hidden");
        } else {
            document.getElementById('contenedor_estadistica').setAttribute("hidden", "");
        }
    } catch (error) {
        console.error('Error al generar el reporte:', error);
        contenidoModal.innerHTML = '<p class="text-center text-danger p-5">Ocurrió un error al generar el reporte. Intente de nuevo.</p>';
    }
});

// Funciones de gráficos (se mantienen igual, solo cambia el acceso a datos)
function crearGraficoSexo(datos) {
    const hombres = datos.filter(p => p.sexo?.toLowerCase() === 'masculino').length;
    const mujeres = datos.filter(p => p.sexo?.toLowerCase() === 'femenino').length;
    elementosEstadisticos.total_hombres = hombres;
    elementosEstadisticos.total_mujeres = mujeres;

    const ctx = document.getElementById('graficoSexo').getContext('2d');
    graficoSexo = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Hombres', 'Mujeres'],
            datasets: [{ data: [hombres, mujeres], backgroundColor: ['#3498db', '#e74c3c'] }]
        },
        options: { responsive: true, plugins: { title: { display: true, text: 'Distribución por Sexo' } } }
    });
}

function crearGraficoTipoVivienda(datos) {
    const propietarios = datos.filter(p => p.alquilado == 0).length;
    const alquilados = datos.filter(p => p.alquilado == 1).length;
    elementosEstadisticos.total_propietarios = propietarios;
    elementosEstadisticos.total_habitantes = alquilados;

    const ctx = document.getElementById('graficoTipoVivienda').getContext('2d');
    graficoVivienda = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Vivienda Propia', 'Vivienda Alquilada'],
            datasets: [{ data: [propietarios, alquilados], backgroundColor: ['#2ecc71', '#f1c40f'] }]
        },
        options: { responsive: true, plugins: { title: { display: true, text: 'Habitantes por Tipo de Vivienda' } } }
    });
}

function crearGraficoPorEdades(datos) {
    const rangos = { '0-17': 0, '18-35': 0, '36-59': 0, '60+': 0 };
    datos.forEach(p => {
        if (p.edad <= 17) rangos['0-17']++;
        else if (p.edad <= 35) rangos['18-35']++;
        else if (p.edad <= 59) rangos['36-59']++;
        else rangos['60+']++;
    });
    elementosEstadisticos.menores_edad = rangos['0-17'];
    elementosEstadisticos.adultos_jovenes = rangos['18-35'];
    elementosEstadisticos.adultos = rangos['36-59'];
    elementosEstadisticos.adultos_mayores = rangos['60+'];

    const ctx = document.getElementById('graficoEdades').getContext('2d');
    graficoEdades = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(rangos),
            datasets: [{
                label: 'Cantidad de Habitantes',
                data: Object.values(rangos),
                backgroundColor: '#9b59b6'
            }]
        },
        options: {
            responsive: true,
            plugins: { title: { display: true, text: 'Distribución por Rango de Edad' } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// Exportar a PDF
document.getElementById('boton_exportar_pdf').addEventListener('click', function() {
    if (!graficoSexo || !graficoVivienda || !graficoEdades) {
        Utilidades.mensaje('warning', 'Atención', 'Primero debe generar un reporte');
        return;
    }

    const { jsPDF } = window.jspdf;
    const contenido = document.getElementById('cuerpo_modal');
    const boton = this;

    boton.disabled = true;
    boton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Exportando...';

    html2canvas(contenido, { scale: 2 })
        .then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

            pdf.setFontSize(18);
            pdf.text("Reporte Estadístico de Habitantes", pdfWidth / 2, 20, { align: 'center' });
            pdf.addImage(imgData, 'PNG', 10, 30, pdfWidth - 20, pdfHeight - 20);
            pdf.save(`reporte-habitantes-${new Date().toISOString().slice(0, 10)}.pdf`);

            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Exportar a PDF';
        })
        .catch(err => {
            console.error("Error al generar el PDF:", err);
            Utilidades.mensaje('error', 'Error', 'Ocurrió un error al generar el PDF');
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Exportar a PDF';
        });
});

function crearContenedorEstadistica() {
    limpiarContenedorEstadistica();
    document.getElementById('total_habitantes').textContent += elementosEstadisticos.total_habitantes;
    document.getElementById('total_propietarios').textContent += elementosEstadisticos.total_propietarios;
    document.getElementById('total_hombres').textContent += elementosEstadisticos.total_hombres;
    document.getElementById('total_mujeres').textContent += elementosEstadisticos.total_mujeres;
    document.getElementById('menores_edad').textContent += elementosEstadisticos.menores_edad;
    document.getElementById('adultos_jovenes').textContent += elementosEstadisticos.adultos_jovenes;
    document.getElementById('adultos').textContent += elementosEstadisticos.adultos;
    document.getElementById('adultos_mayores').textContent += elementosEstadisticos.adultos_mayores;
    document.getElementById('total_personas').textContent += (elementosEstadisticos.total_habitantes + elementosEstadisticos.total_propietarios);
}

function limpiarContenedorEstadistica() {
    document.getElementById('total_personas').textContent = "Total de Personas Registradas: ";
    document.getElementById('total_habitantes').textContent = "Total de Personas Habitantes: ";
    document.getElementById('total_propietarios').textContent = "Total de Personas Propietarios: ";
    document.getElementById('total_hombres').textContent = "Total Hombres: ";
    document.getElementById('total_mujeres').textContent = "Total Mujeres: ";
    document.getElementById('menores_edad').textContent = "Menores de Edad (0-17) años: ";
    document.getElementById('adultos_jovenes').textContent = "Adultos Jóvenes (18-35) años: ";
    document.getElementById('adultos').textContent = "Adultos (36-59) años: ";
    document.getElementById('adultos_mayores').textContent = "Adultos Mayores (+60): ";
}