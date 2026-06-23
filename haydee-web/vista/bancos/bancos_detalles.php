<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog modal-dialog-scrollable-centered">
        <div class="modal-content card shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-bank me-2"></i>Detalles de la Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom  p-0">
                <div class=" p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Número de Cuenta</span>
                    <h4 id="vp_nro_cuenta" class="text-primary mt-2 mb-0 fw-bold" style="letter-spacing: 2px; font-family: monospace;">---</h4>
                </div>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 rounded-top">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-building fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Banco</span>
                            </div>
                            <span id="vp_nombre_banco" class="fw-bold  text-end">---</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-upc-scan fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Código Bancario</span>
                            </div>
                            <span id="vp_codigo" class="fw-bold text-muted text-end">
                                ---
                            </span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-wallet2 fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Tipo de Cuenta</span>
                            </div>
                            <span id="vp_tipo_cuenta" class="text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center  py-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-person-vcard fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Documento (RIF/C.I)</span>
                            </div>
                            <span id="vp_documento" class="fw-bold text-end">---</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom-0 rounded-bottom">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-telephone fs-5 me-3 text-primary"></i>
                                <span class="fw-semibold">Teléfono Asociado</span>
                            </div>
                            <span id="vp_telefono" class="fw-bold  text-end">---</span>
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