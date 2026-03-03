<h3 class="text-center mb-1" id="titulo_grafico">Gráfico de Ingresos y Egresos</h3>
<p class="text-center text-muted mb-4" id="fecha_grafico"></p>

<div class="chart-container mx-auto mb-4 col-md-10" style="position: relative; height:40vh; width:100%">
    <canvas id="canva"></canvas>
</div>

<div class="container my-4" id="contenedor_estadistica">
    <h4 class="text-center border-bottom pb-2 mb-4">Resumen Estadístico</h4>
    
    <div class="row justify-content-center mb-4">
        <div class="col-md-5">
            <div class="card border-success mb-3 shadow-sm">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-arrow-up-circle"></i> Ingresos (Pagos)
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">Total Recaudado</h5>
                    <p class="card-text fs-4 fw-bold text-success" id="total_pagos">0.00</p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-danger mb-3 shadow-sm">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="bi bi-arrow-down-circle"></i> Egresos (Gastos)
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">Total Gastado</h5>
                    <p class="card-text fs-4 fw-bold text-danger" id="total_gastos">0.00</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold text-success">
                    Métodos de Ingreso
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Efectivo <span class="badge bg-success rounded-pill" id="pagos_efectivo">0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Transferencia <span class="badge bg-success rounded-pill" id="pagos_transferencia">0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pago Móvil <span class="badge bg-success rounded-pill" id="pagos_pago_movil">0</span>
                    </li>
                </ul>
                <div class="card-footer text-muted text-center small" id="fecha_pagos"></div>
            </div>
        </div>

        <div class="col-md-5 mt-4 mt-md-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold text-danger">
                    Métodos de Egreso
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Efectivo <span class="badge bg-danger rounded-pill" id="gastos_efectivo">0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Transferencia <span class="badge bg-danger rounded-pill" id="gastos_transferencia">0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pago Móvil <span class="badge bg-danger rounded-pill" id="gastos_pago_movil">0</span>
                    </li>
                </ul>
                <div class="card-footer text-muted text-center small" id="fecha_gastos"></div>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto text-center mt-4">
    <form method="POST" action="?pagina=reportes&accion=generar_reporte_ingresos_egresos">
        <input type="hidden" name="fecha_grafico_input" id="fecha_grafico_input">
        <input type="hidden" name="barra" id="barra" value="">
        <input type="hidden" name="total_pagos_input" id="total_pagos_input">
        <input type="hidden" name="total_gastos_input" id="total_gastos_input">
        <input type="hidden" name="gastos_efectivo_input" id="gastos_efectivo_input">
        <input type="hidden" name="gastos_transferencia_input" id="gastos_transferencia_input">
        <input type="hidden" name="gastos_pago_movil_input" id="gastos_pago_movil_input">
        <input type="hidden" name="pagos_efectivo_input" id="pagos_efectivo_input">
        <input type="hidden" name="pagos_transferencia_input" id="pagos_transferencia_input">
        <input type="hidden" name="pagos_pago_movil_input" id="pagos_pago_movil_input">
        <input type="hidden" name="fecha_pagos_input" id="fecha_pagos_input">
        <input type="hidden" name="fecha_gastos_input" id="fecha_gastos_input">
        <input type="hidden" name="mostrar_datos_input" id="mostrar_datos_input">
        <input type="hidden" name="tasa_dolar_input" id="tasa_dolar_input" value="<?php echo $tasa_dolar; ?>">

        <button type="submit" class="btn btn-danger btn-lg" id="boton_generar" title="Generar PDF" disabled>
            <i class="bi bi-file-earmark-pdf"></i> Generar PDF
        </button>
    </form>
</div>