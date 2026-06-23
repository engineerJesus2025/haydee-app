/**
 * reporte_habitantes.js
 * Reporte estadístico de habitantes con gráficos (Refactorizado y Optimizado)
 */

// Variables globales para almacenar las instancias de los gráficos
let graficoSexo, graficoVivienda, graficoEdades;
let modal;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializamos el modal
    const modalElement = document.getElementById('modal_reporte_habitantes');
    if (modalElement) {
        modal = new bootstrap.Modal(modalElement);
    }

    // 2. Inicializamos la lógica de los filtros visuales
    inicializarFiltros();
    
    // 3. Agregamos el evento al botón (no al submit del formulario)
    const btnGenerar = document.getElementById('boton_generar_reporte');
    if (btnGenerar) {
        btnGenerar.addEventListener('click', procesarReporte);
    }

    // 4. Inicializamos el evento para exportar a PDF
    const btnPdf = document.getElementById('boton_exportar_pdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', exportarAPDF);
    }
});

function inicializarFiltros() {
    // Mostrar/Ocultar campos de fechas personalizadas
    document.getElementById('filtro_tiempo')?.addEventListener('change', function() {
        const esPersonalizado = this.value === 'personalizado';
        // Ahora solo mostramos/ocultamos el contenedor padre
        document.getElementById('contenedor_fechas_habitantes').hidden = !esPersonalizado;
    });

    // Mostrar/Ocultar campos de edades personalizadas
    document.getElementById('rango_edades')?.addEventListener('change', function() {
        const esPersonalizado = this.value === 'personalizado';
        // Ahora solo mostramos/ocultamos el contenedor padre
        document.getElementById('contenedor_edades_personalizadas').hidden = !esPersonalizado;
    });
}

async function procesarReporte(e) {
    e.preventDefault(); // Prevenir cualquier comportamiento por defecto
    
    // Recolectar los datos del formulario
    const form = document.getElementById('form_reporte_habitantes');
    const formData = new FormData(form);
    formData.append("operacion", "consultar_habitantes");

    // Recoger servicios de forma correcta según los IDs del HTML
    const servicios = [];
    if(document.getElementById('con_agua')?.checked) servicios.push('agua');
    if(document.getElementById('con_gas')?.checked) servicios.push('gas');
    formData.append("servicios", JSON.stringify(servicios));

    // Deshabilitar el botón temporalmente para evitar múltiples clics
    const btnGenerar = e.target;
    btnGenerar.disabled = true;

    try {
        // Enviar la petición al servidor (asegúrate de que esta ruta sea correcta en tu entorno)
        const response = await fetch("?pagina=reportes", {
            method: "POST",
            body: formData
        });

        const respuesta = await response.json();

        if (respuesta.estatus && respuesta.datos.length > 0) {
            calcularYMostrarEstadisticas(respuesta.datos);
            if(modal) modal.show();
        } else {
            Swal.fire({
                icon: "warning",
                title: "Atención",
                text: respuesta.mensaje || "No se encontraron datos con esos filtros."
            });
        }
    } catch (error) {
        console.error("Error al generar el reporte:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Hubo un problema al procesar la solicitud con el servidor."
        });
    } finally {
        // Habilitar el botón nuevamente
        btnGenerar.disabled = false;
    }
}

function calcularYMostrarEstadisticas(datos) {
    // 1. Inicializar objeto de estadísticas
    let stats = {
        total_habitantes: 0,
        total_propietarios: 0,
        total_hombres: 0,
        total_mujeres: 0,
        menores_edad: 0,
        adultos_jovenes: 0,
        adultos: 0,
        adultos_mayores: 0
    };

    // 2. Procesar los datos de cada persona
    datos.forEach(persona => {
        if (persona.alquilado == 1 || persona.tipo_vinculo?.toLowerCase() === 'habitante') stats.total_habitantes++;
        if (persona.alquilado == 0 || persona.tipo_vinculo?.toLowerCase() === 'propietario') stats.total_propietarios++;

        if (persona.sexo === 'M' || persona.sexo?.toLowerCase() === 'masculino') stats.total_hombres++;
        if (persona.sexo === 'F' || persona.sexo?.toLowerCase() === 'femenino') stats.total_mujeres++;

        const edad = persona.edad !== undefined ? persona.edad : calcularEdad(persona.fecha_nacimiento);
        if (edad <= 17) stats.menores_edad++;
        else if (edad >= 18 && edad <= 35) stats.adultos_jovenes++;
        else if (edad >= 36 && edad <= 59) stats.adultos++;
        else if (edad >= 60) stats.adultos_mayores++;
    });

    // 3. Volcar los datos en el HTML
    document.getElementById('total_personas').innerText = datos.length;
    document.getElementById('total_habitantes').innerText = stats.total_habitantes;
    document.getElementById('total_propietarios').innerText = stats.total_propietarios;
    document.getElementById('total_hombres').innerText = stats.total_hombres;
    document.getElementById('total_mujeres').innerText = stats.total_mujeres;
    document.getElementById('menores_edad').innerText = stats.menores_edad;
    document.getElementById('adultos_jovenes').innerText = stats.adultos_jovenes;
    document.getElementById('adultos').innerText = stats.adultos;
    document.getElementById('adultos_mayores').innerText = stats.adultos_mayores;

    // 4. Lógica para mostrar/ocultar gráficos y texto según selección
    const modoVista = document.getElementById('select_mostrar_datos').value;
    
    // Capturamos los contenedores del DOM
    const contenedorGraficos = document.getElementById('grafico_sexo').closest('.row'); // El div de los gráficos
    const contenedorTexto = document.getElementById('contenedor_estadistica_habitantes'); // Las tarjetas
    const tituloTexto = document.querySelector('#modal_reporte_habitantes h4'); // El título "Resumen Estadístico"
    const separador = document.querySelector('#modal_reporte_habitantes hr'); // La línea <hr>

    if (modoVista === "solo_texto") {
        contenedorGraficos.hidden = true;
        contenedorTexto.hidden = false;
        if(tituloTexto) tituloTexto.hidden = false;
        if(separador) separador.hidden = false;
    } else if (modoVista === "solo_grafico") {
        contenedorGraficos.hidden = false;
        contenedorTexto.hidden = true;
        if(tituloTexto) tituloTexto.hidden = true;
        if(separador) separador.hidden = true;
        renderizarGraficos(stats); // Solo renderizamos los gráficos si se van a mostrar
    } else {
        // grafico_texto (Mostrar todo)
        contenedorGraficos.hidden = false;
        contenedorTexto.hidden = false;
        if(tituloTexto) tituloTexto.hidden = false;
        if(separador) separador.hidden = false;
        renderizarGraficos(stats);
    }
}

function calcularEdad(fechaNacimiento) {
    if (!fechaNacimiento) return 0;
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    return edad;
}

function renderizarGraficos(stats) {
    // 1. Extraemos las variables de colores de tu CSS en tiempo real
    const rootStyles = getComputedStyle(document.documentElement);
    const colorTexto = rootStyles.getPropertyValue('--bs-body-color').trim() || '#cbd5e1';
    const colorGrid = rootStyles.getPropertyValue('--ch-table-border').trim() || '#334155';

    // Colores dinámicos para los gráficos (Soft Badges)
    const colorPrimario = rootStyles.getPropertyValue('--ch-badge-primary-border').trim() || '#3b82f6';
    const bgPrimario = rootStyles.getPropertyValue('--ch-badge-primary-bg').trim() || 'rgba(59, 130, 246, 0.2)';

    const colorPeligro = rootStyles.getPropertyValue('--ch-badge-danger-border').trim() || '#ef4444';
    const bgPeligro = rootStyles.getPropertyValue('--ch-badge-danger-bg').trim() || 'rgba(248, 113, 113, 0.2)';

    const colorExito = rootStyles.getPropertyValue('--ch-badge-success-border').trim() || '#10b981';
    const bgExito = rootStyles.getPropertyValue('--ch-badge-success-bg').trim() || 'rgba(16, 185, 129, 0.2)';

    const colorAdvertencia = rootStyles.getPropertyValue('--ch-badge-warning-border').trim() || '#f59e0b';
    const bgAdvertencia = rootStyles.getPropertyValue('--ch-badge-warning-bg').trim() || 'rgba(245, 158, 11, 0.2)';

    const colorIndigo = rootStyles.getPropertyValue('--ch-badge-indigo-border').trim() || '#6366f1';
    const bgIndigo = rootStyles.getPropertyValue('--ch-badge-indigo-bg').trim() || 'rgba(99, 102, 241, 0.2)';

    // Opciones comunes para tooltips elegantes
    const opcionesTooltip = {
        backgroundColor: 'rgba(15, 23, 42, 0.9)',
        titleColor: '#fff',
        bodyColor: '#cbd5e1',
        borderColor: colorGrid,
        borderWidth: 1
    };

    // 1. Gráfico de Sexo
    const ctxSexo = document.getElementById('grafico_sexo').getContext('2d');
    if (graficoSexo) graficoSexo.destroy();
    graficoSexo = new Chart(ctxSexo, {
        type: 'pie',
        data: {
            labels: ['Hombres', 'Mujeres'],
            datasets: [{
                data: [stats.total_hombres, stats.total_mujeres],
                backgroundColor: [bgPrimario, bgPeligro],
                borderColor: [colorPrimario, colorPeligro],
                borderWidth: 2
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            color: colorTexto,
            plugins: { 
                legend: { position: 'bottom', labels: { color: colorTexto } },
                tooltip: opcionesTooltip
            } 
        }
    });

    // 2. Gráfico de Tipo de Habitantes
    const ctxVivienda = document.getElementById('grafico_vivienda').getContext('2d');
    if (graficoVivienda) graficoVivienda.destroy();
    graficoVivienda = new Chart(ctxVivienda, {
        type: 'doughnut',
        data: {
            labels: ['Propietarios', 'Arrendatarios'],
            datasets: [{
                data: [stats.total_propietarios, stats.total_habitantes], 
                backgroundColor: [bgExito, bgAdvertencia],
                borderColor: [colorExito, colorAdvertencia],
                borderWidth: 2
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            color: colorTexto,
            cutout: '65%', // Hace la dona más delgada y elegante
            plugins: { 
                legend: { position: 'bottom', labels: { color: colorTexto } },
                tooltip: opcionesTooltip
            } 
        }
    });

    // 3. Gráfico de Edades
    const ctxEdades = document.getElementById('grafico_edades').getContext('2d');
    if (graficoEdades) graficoEdades.destroy();
    graficoEdades = new Chart(ctxEdades, {
        type: 'bar',
        data: {
            labels: ['0-17', '18-35', '36-59', '60+'],
            datasets: [{
                label: 'Habitantes',
                data: [stats.menores_edad, stats.adultos_jovenes, stats.adultos, stats.adultos_mayores],
                backgroundColor: bgIndigo,
                borderColor: colorIndigo,
                borderWidth: 2,
                borderRadius: 6, // Bordes redondeados
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            color: colorTexto,
            plugins: { 
                legend: { display: false },
                tooltip: opcionesTooltip
            },
            scales: { 
                x: {
                    ticks: { color: colorTexto },
                    grid: { display: false } // Oculta lineas verticales
                },
                y: { 
                    beginAtZero: true, 
                    ticks: { precision: 0, color: colorTexto },
                    grid: { color: colorGrid, borderDash: [5, 5] } // Lineas horizontales punteadas
                } 
            }
        }
    });
}

async function exportarAPDF() {
    const { jsPDF } = window.jspdf;
    const elementoModal = document.querySelector('#modal_reporte_habitantes .modal-body');
    const btnPdf = document.getElementById('boton_exportar_pdf');

    btnPdf.disabled = true;
    btnPdf.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

    try {
        // Tomar 'captura' de la pantalla del modal
        const canvas = await html2canvas(elementoModal, { scale: 2 });
        const imgData = canvas.toDataURL('image/png');

        // Configurar PDF (Orientación horizontal 'l', milímetros, tamaño A4)
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        // Añadir imagen al PDF y descargar
        pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);
        pdf.save('Reporte_Estadistico_Habitantes.pdf');
    } catch (error) {
        console.error("Error al exportar a PDF: ", error);
        Swal.fire('Error', 'No se pudo generar el PDF. Intente nuevamente.', 'error');
    } finally {
        btnPdf.disabled = false;
        btnPdf.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Exportar a PDF';
    }
}