/**
 * reporte_gastos.js
 * Generación de reporte mensual de gastos
 * Dependencias: Validador.js, Patrones.js, Alertas.js, Peticiones.js
 */

const anioSelect = document.getElementById('anio_reporte');
const mesSelect = document.getElementById('mes_reporte');
const btnGenerar = document.getElementById('btn_generar_reporte_gastos');
const botonCuadroGastos = document.getElementById("boton_cuadro_gastos");
const formReporte = document.getElementById('form_gastos_mensual');

let periodosDisponibles = {};
const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

anioSelect.addEventListener('change', () => {
    if (!Validador.evaluarInput(anioSelect, Patrones.anio, 'Año inválido')) {
        btnGenerar.disabled = true;
        return;
    }
    const anio = anioSelect.value;
    mesSelect.innerHTML = '<option value="">Seleccione un mes...</option>';
    mesSelect.disabled = true;
    btnGenerar.disabled = true;

    if (anio && periodosDisponibles[anio]) {
        periodosDisponibles[anio].sort((a, b) => a - b).forEach(mes => {
            mesSelect.add(new Option(nombresMeses[mes], mes));
        });
        mesSelect.disabled = false;
    }
});

mesSelect.addEventListener('change', () => {
    btnGenerar.disabled = !mesSelect.value;
});

// Obtener periodos al abrir el modal
document.addEventListener("DOMContentLoaded", () => {
    consultarPeriodos();
});

async function consultarPeriodos() {
    let datos = new FormData();
    datos.append('operacion', 'listar_meses_con_gastos');

    try {
        // Enviar con Fetch usando el nuevo helper (sin mostrar modal extra de carga)
        const respuesta = await Peticiones.enviar(datos, "", false);

        const contenedorTarjeta = botonCuadroGastos.parentElement;

        if (respuesta.estatus && respuesta.datos.length > 0) {
            periodosDisponibles = respuesta.datos.reduce((acc, item) => {
                const { anio, mes } = item;
                if (!acc[anio]) acc[anio] = [];
                acc[anio].push(parseInt(mes));
                return acc;
            }, {});

            anioSelect.innerHTML = '<option value="">Seleccione un año...</option>';
            Object.keys(periodosDisponibles).sort((a, b) => b - a).forEach(anio => {
                anioSelect.add(new Option(anio, anio));
            });

            Tooltips.actualizarDinamicamente(contenedorTarjeta, 'Ver historial de gastos registrados para este módulo.');

            botonCuadroGastos.removeAttribute("disabled");
            let spinnerContainer = botonCuadroGastos.querySelector(".spinner-grow")?.parentElement;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi bi-receipt-cutoff" style="font-size: 5rem !important;"></i>`;
            }
        } else {
            Tooltips.actualizarDinamicamente(contenedorTarjeta, 'No se han detectado gastos registrados en el sistema.');

            anioSelect.innerHTML = '<option value="">No hay datos</option>';
            let spinnerContainer = botonCuadroGastos.querySelector(".spinner-grow")?.parentElement;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi bi-inbox text-secondary" style="font-size: 5rem !important;"></i>`;
            }
        }
    } catch (error) {
        console.error("Error al cargar períodos:", error);
        anioSelect.innerHTML = '<option value="">Error al cargar</option>';
    }
}

formReporte.addEventListener('submit', function(event) {
    let tasaDolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);
    document.getElementById('tasa_dolar_reporte').value = tasaDolar;
});