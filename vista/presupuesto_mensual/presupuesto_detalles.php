<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-calculator me-2"></i>Detalle del Presupuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                
                <div class=" p-4 border-bottom">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0 border-end">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Período</span>
                            <h5 id="vp_periodo" class="text-primary mt-2 mb-0 fw-bold">---</h5>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0 border-end">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Total Estimado</span>
                            <h5 id="vp_total_bs" class="text-danger mt-2 mb-0 fw-bold">---</h5>
                            <span id="vp_total_usd" class="text-muted fw-semibold" style="font-size: 0.8rem;">---</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Fondo de Reserva</span>
                            <h5 id="vp_reserva_bs" class="text-success mt-2 mb-0 fw-bold">---</h5>
                            <span id="vp_reserva_usd" class="text-muted fw-semibold" style="font-size: 0.8rem;">---</span>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3  border-bottom" id="vp_contenedor_observacion">
                    <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem;"><i class="bi bi-chat-text me-1"></i> Observación</span>
                    <p id="vp_observacion" class="mb-0  fst-italic" style="font-size: 0.9rem;">---</p>
                </div>

                <div class="p-4 ">
                    <h6 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-list-check me-2"></i>Desglose de Gastos
                    </h6>
                    
                    <div id="vp_contenedor_detalles_presupuesto" class="d-flex flex-column gap-3">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
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