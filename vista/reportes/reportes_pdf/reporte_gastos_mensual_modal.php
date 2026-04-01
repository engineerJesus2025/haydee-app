<div class="modal fade" id="modalGastosMensual" tabindex="-1" aria-labelledby="modalGastosMensualLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0d6efd;">
                <h5 class="modal-title" id="modalGastosMensualLabel">
                    <i class="bi bi-calendar-check me-2"></i>Reporte de Gastos Mensual
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="reporteGastosForm" action="?pagina=reportes&accion=generar_reporte_gastos_mensual" method="POST" target="_blank">
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Seleccione el período y la tasa del dólar para generar el reporte de relación de gastos.</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="anio_gasto" class="form-label fw-bold">Año <span class="text-danger">*</span></label>
                            <select class="form-select" id="anio_gasto" name="anio" required>
                                <option value="" selected disabled>Seleccione...</option>
                                </select>
                        </div>

                        <div class="col-md-6">
                            <label for="mes_gasto" class="form-label fw-bold">Mes <span class="text-danger">*</span></label>
                            <select class="form-select" id="mes_gasto" name="mes" required>
                                <option value="" selected disabled>Seleccione...</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <label for="tasa_dolar_gasto" class="form-label fw-bold">Tasa del Dólar (BCV) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Bs.</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="tasa_dolar_gasto" name="tasa_dolar" placeholder="Ej: 36.50" required>
                                <button class="btn btn-outline-secondary" type="button" id="btn_obtener_tasa_gasto" title="Obtener tasa actual">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="form-text">Puede usar la tasa actual o ingresar una manualmente.</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="formato" value="pdf" class="btn btn-danger" id="btn_generar_reporte_pdf" hidden disabled>
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                    <button type="submit" name="formato" value="excel" class="btn btn-success" disabled>
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>