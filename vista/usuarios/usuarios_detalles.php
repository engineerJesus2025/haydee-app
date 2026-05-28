<div class="modal fade" id="modal_detalles" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="titulo_modal_detalles">
                    <i class="bi bi-person-vcard me-3"></i>Perfil del Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                <div class=" p-4 text-center border-bottom">
                    <div class="d-inline-flex justify-content-center align-items-center bg-primary text-white rounded-circle mb-3 shadow-sm" style="width: 65px; height: 65px; font-size: 1.8rem;">
                        <span id="vp_avatar_inicial" class="fw-bold">U</span>
                    </div>
                    
                    <h5 id="vp_nombre_completo" class="mb-2 fw-bold text-wrap" style="word-break: break-word;">---</h5>
                    
                    <span id="vp_rol_badge" class="badge badge-soft-secondary fs-6 px-3 py-2 shadow-sm">---</span>
                </div>
                <div class="p-3">
                    <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex flex-column justify-content-center px-2 py-3 border-bottom-0 text-center rounded">
                            <div class="text-muted mb-1">
                                <i class="bi bi-envelope-at fs-5 me-2 text-primary"></i>
                                <span class="fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Correo Electrónico</span>
                            </div>
                            <span id="vp_correo" class="fw-bold text-wrap">---</span>
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