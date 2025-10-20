<div class="modal fade" id="modal_gastos_mensual" tabindex="-1" aria-labelledby="titulo_modal_gastos" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="titulo_modal_gastos">Generar Relación de Gastos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione el período para el reporte.</p>
                <form method="POST" action="?pagina=reportes_controlador.php&accion=generar_reporte_gastos_mensual" target="_blank" id="form_gastos_mensual">
                    <div class="mb-3">
                        <label for="anio_reporte" class="form-label">Año:</label>
                        <select class="form-select" id="anio_reporte" name="anio" required>
                            <option value="">Seleccione un año...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mes_reporte" class="form-label">Mes:</label>
                        <select class="form-select" id="mes_reporte" name="mes" required disabled>
                            <option value="">Seleccione un mes...</option>
                        </select>
                    </div>
                        <input type="hidden" id="tasa_dolar_reporte" name="tasa_dolar">

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" form="form_gastos_mensual" id="btn_generar_reporte_gastos" disabled>
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById('modal_gastos_mensual');
    const anioSelect = document.getElementById('anio_reporte');
    const mesSelect = document.getElementById('mes_reporte');
    const btnGenerar = document.getElementById('btn_generar_reporte_gastos');
    let periodosDisponibles = {}; // Guardaremos los datos procesados aquí

    const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    // FUNCIÓN DE FETCH CON LA ESTRUCTURA DE .then() SOLICITADA
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
            })
            .catch(error => {
                console.error("Error al cargar los períodos:", error);
                anioSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    // 1. Cuando el modal se vaya a mostrar, llamamos a la función de carga
    modalEl.addEventListener('show.bs.modal', () => {
        anioSelect.innerHTML = '<option value="">Cargando...</option>';
        mesSelect.innerHTML = '<option value="">Seleccione un año primero</option>';
        mesSelect.disabled = true;
        btnGenerar.disabled = true;
        
        consultarPeriodosDeGastos(); // Llamamos a la nueva función
    });

    // 2. Cuando el usuario seleccione un año, llenamos los meses (esta lógica no cambia)
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

    // 3. Cuando el usuario seleccione un mes, habilitamos el botón final (esta lógica no cambia)
    mesSelect.addEventListener('change', () => {
        btnGenerar.disabled = !mesSelect.value;
    });
});

const formReporte = document.getElementById('form_gastos_mensual');

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
</script>