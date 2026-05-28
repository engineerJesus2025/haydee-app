<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-calendar3 me-2"></i>Detalles del Año Fiscal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom  p-0">
                <div class=" p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Estado del Periodo</span>
                    <h4 id="vp_estado" class="mt-2 mb-0 fw-bold" style="letter-spacing: 1px;">---</h4>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 rounded-top">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-card-text fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Descripción</span>
                            </div>
                            <span id="vp_descripcion" class="fw-bold  text-end text-wrap ms-3">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-event fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Fecha de Inicio</span>
                            </div>
                            <span id="vp_fecha_inicio" class="fw-bold  text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3 border-bottom-0 rounded-bottom">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-check fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Fecha de Cierre</span>
                            </div>
                            <span id="vp_fecha_cierre" class="fw-bold  text-end">---</span>
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