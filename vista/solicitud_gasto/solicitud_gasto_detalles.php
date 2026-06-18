<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog modal-dialog-scrollable-centered">
        <div class="modal-content card shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-file-earmark-text me-2"></i>Detalle de la Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                <div class=" p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Monto Solicitado</span>
                    <h3 id="vp_monto_bs" class="mt-2 mb-0 fw-bold" style="letter-spacing: 1px;">---</h3>
                    <span id="vp_monto_usd" class="text-muted fw-semibold" style="font-size: 0.9rem;">---</span>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 rounded-top">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-person fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Solicitante</span>
                            </div>
                            <span id="vp_solicitante" class="fw-bold  text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-info-circle fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Estado Actual</span>
                            </div>
                            <span id="vp_estado" class="fw-bold text-end">---</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-exclamation-circle fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Nivel de Prioridad</span>
                            </div>
                            <span id="vp_prioridad" class="fw-bold text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 rounded-bottom border-bottom-0">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-event fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Fecha de Reporte</span>
                            </div>
                            <span id="vp_fecha" class="fw-bold  text-end">---</span>
                        </li>
                        
                        <li class="list-group-item  py-3 border-bottom-0 bg-transparent">
                            <div class="d-flex align-items-center text-muted mb-3">
                                <i class="bi bi-card-text fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Justificación de la Necesidad</span>
                            </div>
                            <div class="card-item ms-4 p-3 border-start border-4 border-primary rounded shadow-sm">
                                <span id="vp_descripcion" class="" style="font-size: 0.95rem; white-space: pre-wrap; line-height: 1.6;">---</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cerrar
                </button>
            </div>
            
        </div>
    </div>
</div>