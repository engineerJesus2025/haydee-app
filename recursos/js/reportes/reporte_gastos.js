const anioSelect = document.getElementById('anio_reporte');
const mesSelect = document.getElementById('mes_reporte');
const btnGenerar = document.getElementById('btn_generar_reporte_gastos');
const boton_cuadro_gastos = document.getElementById("boton_cuadro_gastos");
const formReporte = document.getElementById('form_gastos_mensual');

let periodosDisponibles = {};
const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

anioSelect.addEventListener('change', () => {
    const anioSeleccionado = anioSelect.value;
    
    mesSelect.innerHTML = '<option value="">Seleccione un mes...</option>';
    mesSelect.disabled = true;
    btnGenerar.disabled = true;

    if (anioSeleccionado && periodosDisponibles[anioSeleccionado]) {
        periodosDisponibles[anioSeleccionado].sort((a, b) => a - b).forEach(mes => {
            mesSelect.add(new Option(nombresMeses[mes], mes));
        });
        mesSelect.disabled = false;
    }
});

mesSelect.addEventListener('change', () => {
    btnGenerar.disabled = !mesSelect.value;
});

function consultarPeriodosDeGastos() {
    let datos_consulta = new FormData();
    datos_consulta.append('operacion', 'consultar_meses_con_gastos');

    fetch("", { method: 'POST', body: datos_consulta })
        .then(res => {
            if (!res.ok) {
                throw new Error('La respuesta del servidor no fue exitosa');
            }
            return res.json();
        })
        .then(periodos => {
            // Agrupamos los meses por cada año
            periodosDisponibles = periodos.reduce((acc, item) => {
                const { anio, mes } = item;
                if (!acc[anio]) acc[anio] = [];
                acc[anio].push(parseInt(mes));
                return acc;
            }, {});

            // Llenamos el select de años
            anioSelect.innerHTML = '<option value="">Seleccione un año...</option>';
            Object.keys(periodosDisponibles).sort((a, b) => b - a).forEach(anio => {
                anioSelect.add(new Option(anio, anio));
            });

            boton_cuadro_gastos.removeAttribute("disabled");
            boton_cuadro_gastos.querySelector(".spinner-grow").parentElement.innerHTML = `<i class="bi bi-receipt-cutoff" style="font-size: 5rem !important;"></i>`;
        })
        .catch(error => {
            console.error("Error al cargar los períodos:", error);
            anioSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
}

// Añade este nuevo evento 'submit'
formReporte.addEventListener('submit', function(event) {
    const tasaDolar = localStorage.getItem('tasa_dolar');

    if (tasaDolar) {
        document.getElementById('tasa_dolar_reporte').value = tasaDolar;
    } else {
        alert("Error: No se encontró la tasa del dólar para generar el reporte.");
        event.preventDefault(); // Detiene el envío del formulario
    }
});

consultarPeriodosDeGastos();