<div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog modal-dialog-scrollable-centered modal-lg modal-dialog modal-dialog-scrollable-scrollable">
        <div class="modal-content card shadow-lg">
            
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-megaphone me-2"></i>Detalles de la Publicación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body rounded-bottom p-0">
                
                <div class="p-4 border-bottom shadow-sm">
                    <div class="d-flex justify-content-between align-items-center gap-3 card-item p-3 rounded">
                        <div>
                            <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Título</span>
                            <h4 id="vista_titulo" class="text-primary mb-0 fw-bold text-wrap" style="word-break: break-word;">---</h4>
                        </div>
                        <span id="vista_prioridad" class="badge fs-6 px-3 py-2 shadow-sm text-nowrap">---</span>
                    </div>
                </div>

                <div class="px-4 py-3 border-bottom">
                    <div class="row text-center text-md-start justify-content-around">
                        <div class="col-md-5 mb-2 mb-md-0 border-md-end py-2 card-item rounded">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted">
                                <i class="bi bi-person-fill me-2 text-primary"></i>
                                <span class="fw-semibold me-1">Autor:</span>
                                <span id="vista_autor" class="fw-bold">---</span>
                            </div>
                        </div>
                        <div class="col-md-5 mb-2 mb-md-0 border-md-end py-2 card-item rounded">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted ms-md-3">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                <span class="fw-semibold me-1">Fecha:</span>
                                <span id="vista_fecha" class="fw-bold">---</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="contenedor_imagen" class="text-center border-bottom p-4" style="display: none;">
                    <img id="vista_imagen" src="" class="img-fluid border rounded shadow-sm" style="max-height: 400px; object-fit: contain;" alt="Vista previa de la imagen" onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                    <p id="mensaje_error_imagen" class="text-danger d-none mt-2 mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> ⚠ No se pudo cargar la imagen.
                    </p>
                </div>

                <div class="p-4">
                    <h6 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-justify-left me-2"></i>Descripción
                    </h6>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 rounded card-item">
                            <div id="vista_descripcion" class="card-content-text text-wrap" style="font-size: 1rem; line-height: 1.7; word-break: break-word; white-space: pre-wrap;">---</div>
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