<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog modal-dialog-scrollable-centered">
        <div class="modal-content card shadow-lg">           
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-bank me-2"></i>Detalles de la Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                <div class="p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Entidad Financiera</span>
                    <h3 id="detalle_nombre_banco" class="text-primary mt-2 mb-0 fw-bold" style="letter-spacing: 1px;">---</h3>
                </div>

                <div class="p-4 text-center">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Código Bancario</span>
                    <div class="d-flex justify-content-center align-items-center mt-2">
                        <h2 id="detalle_codigo_banco" class="mb-0 fw-bold me-3" style="font-family: monospace; letter-spacing: 3px;">---</h2>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Copiar Código" onclick="navigator.clipboard.writeText(document.getElementById('detalle_codigo_banco').innerText)">
                            <i class="bi bi-copy"></i>
                        </button>
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