<div class="modal fade" id="modal_detalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog modal-dialog-scrollable-centered modal-dialog modal-dialog-scrollable-scrollable">
        <div class="modal-content card shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-gear me-2" id="vp_icono"></i>Detalles del Rol
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                <div class=" p-4 text-center border-bottom">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Nombre del Perfil</span>
                    <h4 id="vp_nombre_rol" class="text-primary mt-2 mb-0 fw-bold" style="letter-spacing: 1px;">---</h4>
                </div>

                <div class="p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-shield-check me-2"></i>Permisos Asignados
                    </h5>
                    
                    <div id="vp_contenedor_permisos" class="d-flex flex-column gap-3">
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