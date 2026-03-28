<div class="modal fade" id="modal_detalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-bell-fill me-2"></i>Detalle de Notificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="bg-light p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 1.75rem; letter-spacing: 1px;">Asunto</span>
                    <h5 id="vp_titulo" class="text-primary mt-2 mb-1 fw-bold text-wrap" style="word-break: break-word;">---</h5>
                    <span id="vp_estado" class="badge fs-6 px-3 py-1 mt-2 shadow-sm">---</span>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-tags-fill fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Tipo de Evento</span>
                            </div>
                            <span id="vp_tipo_evento" class="fw-bold text-dark text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-event fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Fecha de Emisión</span>
                            </div>
                            <span id="vp_fecha" class="fw-bold text-dark text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex flex-column justify-content-center px-0 py-3 border-bottom-0">
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="bi bi-card-text fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Descripción del Evento</span>
                            </div>
                            <span id="vp_descripcion" class="fw-bold text-dark text-wrap ms-4 ps-2 border-start border-3 border-primary" style="line-height: 1.5;">---</span>
                        </li>
                        
                    </ul>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-top-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
            
        </div>
    </div>
</div>