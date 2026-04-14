<!DOCTYPE html>
<html>
<head>
	<title>Caja chica</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once ROOT_PATH . "/vista/componentes/estilos.php";
	?>
  <style>
  @media (min-width: 992px) {
    .border-end-lg {
        border-right: 1px solid #dee2e6 !important;
    }
  }
  </style>
</head>

<body id="body-pd" class="body-pd">  
	<div class="container-fluid">
		<div class="row flex-nowrap mb-2">
			<?php
				require_once ROOT_PATH . "/vista/componentes/navbar.php";
			?>
			<div class="col d-flex flex-column  min-vh-100 gris">
			<?php
				require_once ROOT_PATH . "/vista/componentes/header.php";
			?>
				<main class="col ps-md-2 pt-2 mb-5">
					<div class="page-header pt-3">
						<h2>GESTIONAR CAJA CHICA</h2>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row mb-4">
            <div class="col-12 mt-2">
              <div class="card p-0 overflow-hidden shadow-sm border-0">
                <div class="bg-light border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 text-secondary fw-bold">
                      <i class="bi bi-calendar2-date text-primary me-2"></i> Período de Evaluación
                  </h5>
                  <span id="span_caja_activa" class="badge rounded-pill bg-secondary fs-6 px-3 py-2">
                      Cargando estado...
                  </span>
                </div>
                <div class="card-body p-4">
                  <div class="row g-4 align-items-center">
                    <div class="col-lg-4 border-end-lg pe-lg-4">
                      <label class="form-label text-muted small text-uppercase fw-bold mb-2">Seleccione el mes</label>
                      <select class="form-select form-select-lg mb-3 shadow-sm border-primary" aria-label="Selector de mes" name="mes_seleccionado" id="mes_select">
                          <option>Cargando registros...</option>
                      </select>
                      
                      <div id="botones_movimientos" class="d-flex gap-2" hidden>
                        <?php 
                            require ROOT_PATH . "/vista/componentes/boton_nuevo.php"; 
                        ?>
                        <?php if ($permisosVista['registrar']) : ?>
                        <button class="btn btn-outline-secondary" id="boton_reponer_caja" data-bs-toggle="modal" data-bs-target="#modal_reponer_caja" data-tooltip="true" title="Reponer Saldo de Caja">
                            <i class="bi bi-arrow-repeat me-1"></i> Reponer
                        </button>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="col-lg-4 border-end-lg px-lg-4">
                      <div class="d-flex flex-column h-100 justify-content-center">
                          <p class="text-muted small text-uppercase fw-bold mb-1">Fondo Actual de Caja</p>
                          <h5 id="span_fondo_fijo" class="fw-bolder text-dark mb-0" style="letter-spacing: -0.5px;">
                              <div class="placeholder-glow m-0"><span class="placeholder col-6 rounded"></span></div>
                          </h5>
                      </div>
                    </div>

                    <div class="col-lg-4 ps-lg-4" id="contenedor_tarjeta_nota" hidden>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <p class="text-muted small text-uppercase fw-bold mb-0">
                              <i class="bi bi-sticky text-warning me-1"></i> Observaciones
                          </p>
                          <button class="btn btn-sm btn-light text-primary rounded-circle p-1 lh-1" id="btn_activar_edicion" title="Editar nota" data-tooltip="true">
                              <i class="bi bi-pencil-square"></i>
                          </button>
                        </div>
                        
                        <div id="modo_lectura_nota" class="p-3 bg-light rounded-3 border h-100">
                          <p id="descripciones" class="text-dark small mb-0" style="white-space: pre-wrap; font-size: 0.85rem;"></p>
                        </div>

                        <div id="modo_edicion_nota" class="d-none d-flex flex-column h-100">
                          <textarea id="descripcion_input_inline" class="form-control form-control-sm mb-2 border-primary shadow-sm" rows="3" placeholder="Escribe una observación para este mes..." style="font-size: 0.85rem;"></textarea>
                          <span class="w-100 invalid-feedback"></span>
                          <div class="d-flex justify-content-end gap-2 mt-auto">
                            <button class="btn btn-sm btn-light border" id="btn_cancelar_edicion">Cancelar</button>
                            <button class="btn btn-sm btn-primary" id="btn_guardar_edicion">Guardar</button>
                          </div>
                        </div>
                    </div>
                  </div> 
                </div> 
              </div> 
            </div>
          </div>
        <div class="row mb-3 justify-content-center">
            <div class="col-12">
                <div class="card p-4 pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 row">
                        <h5 class="text-muted mb-0 col-md-5"><i class="bi bi-list-check me-2"></i>Historial de Movimientos</h5>
                        <div class="col-md-6">
                            <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
                        </div>
                    </div>
                    <div id="tabla_registros_sistema" class="tabla-sistema-haydee"></div>
                </div>
            </div>
        </div>
				</main>
			</div>
		</div>
	</div>
  <!-- Componentes -->
  <?php
      require_once ROOT_PATH . "/vista/componentes/footer.php";
      require_once ROOT_PATH . "/vista/componentes/script.php";
      require_once ROOT_PATH . "/vista/componentes/modal_carga.php";
      require_once ROOT_PATH . "/vista/componentes/boton_ayuda.php";
      // Modales
      require_once ROOT_PATH . "/vista/caja_chica/descripciones_modal.php";
      require_once ROOT_PATH . "/vista/caja_chica/gasto_caja_modal.php";
      require_once ROOT_PATH . "/vista/caja_chica/repocicion_caja_modal.php";
      require_once ROOT_PATH . "/vista/caja_chica/caja_chica_detalles.php";
  ?>
  
  <!-- Scripts personalizado -->
	<script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/caja_chica_ajax.js"></script>
  <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/caja_chica_validar.js"></script>
</body>
</html>