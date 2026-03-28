<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_vp" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_vp">
                    <i class="bi bi-truck me-2"></i>Detalles del Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="bg-light p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Nombre / Razón Social</span>
                    <h4 id="vp_nombre_proveedor" class="text-primary mt-2 mb-1 fw-bold text-wrap" style="word-break: break-word;">---</h4>
                    <span id="vp_rif" class="badge bg-secondary fs-6 px-3 py-1 mt-2 shadow-sm">---</span>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex flex-column justify-content-center px-0 py-3 border-bottom">
                            <div class="d-flex align-items-center text-muted mb-1">
                                <i class="bi bi-tools fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Servicio que Presta</span>
                            </div>
                            <span id="vp_servicio" class="fw-bold text-dark text-wrap ms-4 ps-2">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex flex-column justify-content-center px-0 py-3 border-bottom-0">
                            <div class="d-flex align-items-center text-muted mb-1">
                                <i class="bi bi-geo-alt fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Dirección</span>
                            </div>
                            <span id="vp_direccion" class="fw-bold text-dark text-wrap ms-4 ps-2">---</span>
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