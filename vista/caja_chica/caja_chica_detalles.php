<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-receipt-cutoff me-2"></i>Detalle del Movimiento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="bg-light p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Monto de la Operación</span>
                    <h3 id="vp_monto_bs" class="text-danger mt-2 mb-0 fw-bold" style="letter-spacing: 1px;">---</h3>
                    <span id="vp_monto_usd" class="text-muted fw-semibold" style="font-size: 0.9rem;">---</span>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex flex-column justify-content-center px-0 py-3 border-bottom">
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="bi bi-card-text fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Concepto del Gasto</span>
                            </div>
                            <span id="vp_concepto" class="fw-bold text-dark text-wrap ms-4 ps-3 border-start border-3 border-primary">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-event fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Fecha</span>
                            </div>
                            <span id="vp_fecha" class="fw-bold text-dark text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-0">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-info-circle fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Estado de Reposición</span>
                            </div>
                            <span id="vp_estado" class="fw-bold text-end">---</span>
                        </li>
                        
                    </ul>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
            
        </div>
    </div>
</div>