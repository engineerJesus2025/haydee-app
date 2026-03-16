<div class="container-fluid">
	<!-- Información general -->
	<div class="row mb-3">
	    <div class="col-md-6">
	        <p><strong><i class="bi bi-person-circle me-2"></i>Usuario:</strong> <span id="detalle_usuario"></span></p>
	        <p><strong><i class="bi bi-shield me-2"></i>Rol:</strong> <span id="detalle_rol"></span></p>
	    </div>
	    <div class="col-md-6">
	        <p><strong><i class="bi bi-calendar-event me-2"></i>Fecha:</strong> <span id="detalle_fecha"></span></p>
	        <p><strong><i class="bi bi-puzzle me-2"></i>Módulo:</strong> <span id="detalle_modulo"></span></p>
	    </div>
	</div>
	<div class="row mb-3">
	    <div class="col-12">
	        <p><strong><i class="bi bi-tag me-2"></i>Acción:</strong> 
	            <span id="detalle_accion" class="badge bg-primary" style="font-size:90%"></span>
	        </p>
	    </div>
	</div>

	<!-- Sección para acciones de consulta -->
	<div id="detalle_consulta" class="alert alert-info d-none align-items-center">
	    <i class="bi bi-info-circle me-2 fs-5" id="icono_consulta"></i>
	    <span id="mensaje_consulta"></span>
	</div>

	<!-- Sección para valores anteriores y nuevos (solo para modificar, eliminar, registrar) -->
	<div id="detalle_cambios" class="d-none">
        <ul class="nav nav-tabs" id="cambiosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="anteriores-tab" data-bs-toggle="tab" data-bs-target="#anteriores" type="button" role="tab" aria-controls="anteriores" aria-selected="true">Valores Anteriores</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nuevos-tab" data-bs-toggle="tab" data-bs-target="#nuevos" type="button" role="tab" aria-controls="nuevos" aria-selected="false">Valores Nuevos</button>
            </li>
        </ul>
        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="cambiosTabsContent">
            <div class="tab-pane fade show active" id="anteriores" role="tabpanel" aria-labelledby="anteriores-tab">
                <pre id="valores_anteriores" class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"></pre>
            </div>
            <div class="tab-pane fade" id="nuevos" role="tabpanel" aria-labelledby="nuevos-tab">
                <pre id="valores_nuevos" class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"></pre>
            </div>
        </div>
    </div>
</div>