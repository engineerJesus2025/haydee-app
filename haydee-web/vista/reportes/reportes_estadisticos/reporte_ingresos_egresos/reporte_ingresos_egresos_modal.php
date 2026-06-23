<div class="modal fade" id="modal_reporte" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Reporte de Ingresos y Egresos</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1" id="titulo_grafico">Gráfico de Ingresos y Egresos</h4>
                    <p class="text-muted-custom mb-0" id="fecha_grafico"></p>
                </div>

                <!-- Gráfico -->
                <div class="chart-container mb-4 d-flex justify-content-center" style="position: relative; height: 300px; width: 100%;">
                    <canvas id="canva"></canvas>
                </div>

                <!-- LÍNEA SEPARADORA ELEGANTE -->
                <hr class="mx-auto border-secondary opacity-25 mb-5" style="width: 80%;">

                <!-- Resumen Estadístico Principal -->
                <div class="container-fluid mb-4" id="contenedor_estadistica">
                    <div class="text-center mb-4 pb-3">
                        <h5 class="fw-bold text-primary"><i class="bi bi-calculator me-2"></i>Resumen Financiero</h5>
                    </div>
                    
                    <!-- Tarjetas Superiores (Totales) -->
                    <div class="row g-4 justify-content-center mb-5">
                        <div class="col-md-6">
                            <div class="card card-estadistica h-100 p-4 text-center animacion-aparecer">
                                <div class="badge-soft-success mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-arrow-up-right fs-3"></i>
                                </div>
                                <h6 class="text-muted-custom text-uppercase fw-bold" style="letter-spacing: 1px;">Ingresos Totales</h6>
                                <h3 class="fw-bolder text-success mb-0" id="total_pagos">0.00 <small class="fs-6 text-muted-custom">Bs.</small></h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-estadistica h-100 p-4 text-center animacion-aparecer" style="animation-delay: 0.1s;">
                                <div class="badge-soft-danger mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-arrow-down-right fs-3"></i>
                                </div>
                                <h6 class="text-muted-custom text-uppercase fw-bold" style="letter-spacing: 1px;">Egresos Totales</h6>
                                <h3 class="fw-bolder text-danger mb-0" id="total_gastos">0.00 <small class="fs-6 text-muted-custom">Bs.</small></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose por Método -->
                    <div class="row g-4 justify-content-center">
                        <!-- Ingresos Detalle -->
                        <div class="col-md-6">
                            <div class="card card-estadistica h-100 animacion-aparecer" style="animation-delay: 0.2s;">
                                <div class="card-header py-3">
                                    <!-- TRUCO DE UI: Texto neutral (text-body), icono de color (text-success) -->
                                    <h6 class="fw-bold text-body mb-0"><i class="bi bi-wallet2 me-2 text-success"></i>Vías de Ingreso</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-bottom">
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span><i class="bi bi-cash me-2 text-muted-custom"></i>Efectivo</span> 
                                            <span class="badge badge-soft-success rounded-pill px-3" id="pagos_efectivo">0</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span><i class="bi bi-bank me-2 text-muted-custom"></i>Transferencia</span> 
                                            <span class="badge badge-soft-success rounded-pill px-3" id="pagos_transferencia">0</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                            <span><i class="bi bi-phone me-2 text-muted-custom"></i>Pago Móvil</span> 
                                            <span class="badge badge-soft-success rounded-pill px-3" id="pagos_pago_movil">0</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <small class="text-muted-custom fw-semibold" id="fecha_pagos"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Egresos Detalle -->
                        <div class="col-md-6">
                            <div class="card card-estadistica h-100 animacion-aparecer" style="animation-delay: 0.3s;">
                                <div class="card-header py-3">
                                    <!-- TRUCO DE UI: Texto neutral (text-body), icono de color (text-danger) -->
                                    <h6 class="fw-bold text-body mb-0"><i class="bi bi-receipt me-2 text-danger"></i>Vías de Egreso</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-bottom">
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span><i class="bi bi-cash me-2 text-muted-custom"></i>Efectivo</span> 
                                            <span class="badge badge-soft-danger rounded-pill px-3" id="gastos_efectivo">0</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span><i class="bi bi-bank me-2 text-muted-custom"></i>Transferencia</span> 
                                            <span class="badge badge-soft-danger rounded-pill px-3" id="gastos_transferencia">0</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                            <span><i class="bi bi-phone me-2 text-muted-custom"></i>Pago Móvil</span> 
                                            <span class="badge badge-soft-danger rounded-pill px-3" id="gastos_pago_movil">0</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <small class="text-muted-custom fw-semibold" id="fecha_gastos"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal / Formulario Oculto -->
                <div class="border-top vp-border-color pt-4 pb-2 mt-4 text-center d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-soft-secondary px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cerrar
                    </button>
                    
                    <form method="POST" action="?pagina=reportes&accion=generar_reporte_ingresos_egresos">
                        <!-- ... [Mantén todos tus inputs ocultos igual que antes] ... -->
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
                        <input type="hidden" name="tasa_dolar_input" id="tasa_dolar_input">

                        <button type="submit" class="btn btn-danger px-4 rounded-pill fw-semibold shadow-sm" id="boton_generar" title="Generar PDF" disabled>
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Descargar Documento
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
