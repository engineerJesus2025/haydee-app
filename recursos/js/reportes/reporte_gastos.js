/**
 * reporte_gastos.js
 * Generación de reporte mensual de gastos (PDF y Excel)
 * Dependencias: Validador.js, Patrones.js, Alertas.js, Peticiones.js
 */

const anioSelect = document.getElementById('anio_gasto');
const mesSelect = document.getElementById('mes_gasto');
const formReporte = document.getElementById('reporteGastosForm');
// Seleccionamos TODOS los botones de envío (PDF y Excel)
const botonesGenerar = formReporte.querySelectorAll('button[type="submit"]'); 
const botonCuadroGastos = document.getElementById("boton_cuadro_gastos");
const inputTasaDolar = document.getElementById("tasa_dolar_gasto");
const btnObtenerTasa = document.getElementById("btn_obtener_tasa_gasto");

let periodosDisponibles = {};
const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

// --- FUNCIÓN UTILITARIA ---
// Habilita o deshabilita ambos botones (PDF y Excel) al mismo tiempo
const toggleBotones = (deshabilitar) => {
    botonesGenerar.forEach(btn => btn.disabled = deshabilitar);
};

// --- EVENTOS DEL FORMULARIO ---
anioSelect.addEventListener('change', () => {
    if (!Validador.evaluarInput(anioSelect, Patrones.anio, 'Año inválido')) {
        toggleBotones(true);
        return;
    }
    const anio = anioSelect.value;
    mesSelect.innerHTML = '<option value="">Seleccione un mes...</option>';
    mesSelect.disabled = true;
    toggleBotones(true);

    if (anio && periodosDisponibles[anio]) {
        periodosDisponibles[anio].sort((a, b) => a - b).forEach(mes => {
            mesSelect.add(new Option(nombresMeses[mes], mes));
        });
        mesSelect.disabled = false;
    }
});

mesSelect.addEventListener('change', () => {
    if (Validador.evaluarInput(mesSelect, Patrones.mes, 'Mes inválido')) {
        toggleBotones(false); // Si el mes es válido, habilitamos los botones
    } else {
        toggleBotones(true);
    }
});

// Cargar tasa del dólar al hacer clic en el botón de recargar del input
btnObtenerTasa.addEventListener('click', () => {
    let tasaDolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);
    inputTasaDolar.value = tasaDolar;
});

// Inicializar la tasa de dólar visible por defecto
inputTasaDolar.value = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);

// --- CARGA INICIAL DE DATOS ---
async function cargarPeriodosGastos() {
    try {
        const datos = new FormData();
        datos.append("operacion", "listar_meses_con_gastos");
        const respuesta = await Peticiones.enviar(datos, "", false);
        // const respuesta = await Peticiones.enviar(datos, "", false);
        let contenedorTarjeta = botonCuadroGastos.parentElement;

        if (respuesta.estatus && respuesta.datos.length > 0) {
            respuesta.datos.forEach(row => {
                if (!periodosDisponibles[row.anio]) periodosDisponibles[row.anio] = [];
                periodosDisponibles[row.anio].push(row.mes);
            });

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

// Obtener periodos al abrir el modal
document.addEventListener("DOMContentLoaded", () => {
    cargarPeriodosGastos();
});