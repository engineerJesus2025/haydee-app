<div class="modal fade" id="modalDetalleBitacora" tabindex="-1" aria-labelledby="modalDetalleBitacoraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleBitacoraLabel">
                    <i class="bi bi-journal-text me-2"></i>Detalle de Bitácora
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
				    <div class="row mb-3">
				        <div class="col-md-6">
				            <p><strong><i class="bi bi-person-circle me-2 text-primary"></i>Usuario:</strong> <span id="detalle_usuario"></span></p>
				            <p><strong><i class="bi bi-shield me-2 text-primary"></i>Rol:</strong> <span id="detalle_rol"></span></p>
				        </div>
				        <div class="col-md-6">
				            <p><strong><i class="bi bi-calendar-event me-2 text-primary"></i>Fecha:</strong> <span id="detalle_fecha"></span></p>
				            <p><strong><i class="bi bi-puzzle me-2 text-primary"></i>Módulo:</strong> <span id="detalle_modulo" class="text-uppercase"></span></p>
				        </div>
				    </div>
				    <div class="row mb-4 border-bottom pb-3">
				        <div class="col-12">
				            <p class="mb-0"><strong><i class="bi bi-tag me-2 text-primary"></i>Acción:</strong> 
				                <span id="detalle_accion" class="badge bg-primary" style="font-size:90%"></span>
				            </p>
				        </div>
				    </div>

				    <div id="detalle_consulta" class="alert alert-info d-none shadow-sm">
						<i class="bi bi-info-circle me-2 fs-5" id="icono_consulta"></i>
				        <span id="mensaje_consulta"></span>
				    </div>

				    <div id="detalle_cambios" class="d-none">
				        <ul class="nav nav-tabs" id="cambiosTabs" role="tablist">
				            <li class="nav-item" role="presentation">
				                <button class="nav-link active" id="anteriores-tab" data-bs-toggle="tab" data-bs-target="#anteriores" type="button" role="tab" aria-controls="anteriores" aria-selected="true">Valores Anteriores</button>
				            </li>
				            <li class="nav-item" role="presentation">
				                <button class="nav-link" id="nuevos-tab" data-bs-toggle="tab" data-bs-target="#nuevos" type="button" role="tab" aria-controls="nuevos" aria-selected="false">Valores Nuevos</button>
				            </li>
				        </ul>
				        <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white" id="cambiosTabsContent">
				            <div class="tab-pane fade show active" id="anteriores" role="tabpanel" aria-labelledby="anteriores-tab">
				                <pre id="valores_anteriores" class="bg-light p-3 rounded text-dark" style="max-height: 300px; overflow: auto; font-size: 0.85rem;"></pre>
				            </div>
				            <div class="tab-pane fade" id="nuevos" role="tabpanel" aria-labelledby="nuevos-tab">
				                <pre id="valores_nuevos" class="bg-light p-3 rounded text-dark" style="max-height: 300px; overflow: auto; font-size: 0.85rem;"></pre>
				            </div>
				        </div>
				    </div>
				    <div id="contenedor_imagen_bitacora" class="row mb-4 d-none">
				        <div class="col-12 text-center bg-light p-3 rounded border">
				            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-image me-2"></i>Imagen Asociada al Registro</h6>
				            <img id="imagen_bitacora" src="" class="img-fluid border rounded shadow-sm bg-white" style="max-height: 250px; object-fit: contain;" alt="Vista previa de evidencia" onerror="this.style.display='none'; document.getElementById('mensaje_error_img_bitacora').classList.remove('d-none');">
				            
				            <p id="mensaje_error_img_bitacora" class="text-danger d-none mt-2 mb-0 small">
				                <i class="bi bi-exclamation-triangle-fill me-1"></i> La imagen fue eliminada físicamente del servidor o la ruta no coincide.
				            </p>
				        </div>
				    </div>
				</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>