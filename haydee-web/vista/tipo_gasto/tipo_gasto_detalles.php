<div class="modal fade" id="modal_detalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md modal-dialog-centered">
        <div class="modal-content card shadow-lg border-0">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-diagram-3 me-2"></i> Estructura del Gasto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Partida Principal</span>
                    <h4 id="vp_valor" class="text-primary mb-0 fw-bold">---</h4>
                </div>
                
                <div class="p-4">
                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-list-check me-2"></i>Conceptos Vinculados:</h6>
                    <ul class="list-group list-group-flush shadow-sm rounded" id="vp_lista_conceptos">
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