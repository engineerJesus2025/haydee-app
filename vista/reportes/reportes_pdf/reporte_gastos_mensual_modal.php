<div class="modal fade" id="modalGastosMensual" tabindex="-1" aria-labelledby="modalGastosMensualLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalGastosMensualLabel">
                    <i class="bi bi-calendar-check me-2"></i>Reporte de Gastos Mensual
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="reporteGastosForm" action="?pagina=reportes&accion=generar_reporte_gastos_mensual" method="POST" target="_blank">
                <div class="modal-body vp-body p-4 rounded-bottom">
                    <p class="text-muted-custom mb-4">Seleccione el período y la tasa del dólar para generar el reporte de relación de gastos.</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="anio_gasto" class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <select class="form-select" id="anio_gasto" name="anio" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="mes_gasto" class="form-label fw-semibold">Mes <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar2-month"></i></span>
                                <select class="form-select" id="mes_gasto" name="mes" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <!-- ... resto de los meses ... -->
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <label for="tasa_dolar_gasto" class="form-label fw-semibold">Tasa del Dólar (BCV) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Bs.</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="tasa_dolar_gasto" name="tasa_dolar" placeholder="Ej: 36.50" required>
                                <button class="btn contra-btn" type="button" id="btn_obtener_tasa_gasto" title="Obtener tasa actual">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted-custom mt-2">Puede usar la tasa actual o ingresar una manualmente.</div>
                        </div>
                    </div>
                    
                    <div class="row mt-5 mb-2">
                        <div class="col-md-12 text-end"> 
                            <button type="button" class="btn btn-soft-secondary me-2 px-4" data-bs-dismiss="modal">Cancelar</button>
                            
                            <button type="submit" name="formato" value="pdf" class="btn btn-danger px-4 shadow-sm" id="btn_generar_reporte_pdf" disabled>
                                <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                            </button>
                            <button type="submit" name="formato" hidden value="excel" class="btn btn-success px-4 shadow-sm" disabled>
                                <i class="bi bi-file-earmark-excel me-2"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>