<!DOCTYPE html>
<html>
<head>
	<title>Caja chica</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once ROOT_PATH . "/vista/componentes/estilos.php";
	?>
  <style>
  /*@media (min-width: 992px) {
    .border-end-lg {
        border-right: 1px solid #dee2e6 !important;
    }
  }*/
  #cabecera_caja >  .border-end-lg{
    border-right: 1px solid #dee2e6 !important;
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
						<h2 id="titulo_pagina">GESTIONAR CAJA CHICA</h2>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row mb-5">
                        <div class="col-12 mt-2">
                          <div class="card p-0 overflow-hidden shadow border-0">
                            <div class="border-bottom px-4 py-3 d-flex justify-content-between align-items-center card-item">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-calendar2-date text-primary me-2"></i> Período de Evaluación
                                </h5>
                                <span id="span_caja_activa" class="badge badge-soft-secondary rounded-pill  fs-6 px-3 py-2 shadow-sm d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Cargando estado...
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-stretch mb-2" id="cabecera_caja">
                                    <div class="col-lg-4 border-end-lg d-flex flex-column justify-content-center">
                                        <p class="text-muted small text-uppercase fw-bold mb-2">Seleccione el mes</p>
                                        <select class="form-select shadow-sm" id="mes_select">
                                            <option selected disabled>Cargando registros...</option>
                                        </select>
                                        <div class="d-flex gap-2 mt-3" id="botones_movimientos" hidden>
                                            <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal_registro_gastos">
                                                <i class="bi bi-plus-lg me-1"></i> Nuevo Gasto
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal_reponer_caja">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Reponer
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 border-end-lg px-lg-4 d-flex flex-column justify-content-center align-self-start">
                                        <p class="text-muted small text-uppercase fw-bold mb-2">Fondo Actual de Caja</p>
                                        <h5 id="span_fondo_fijo" class="fw-bolder mb-0 placeholder-glow">
                                            <span class="placeholder col-8 rounded "></span>
                                        </h5>
                                    </div>
                                    <div class="col-lg-4 ps-lg-4 d-flex flex-column justify-content-center" id="contenedor_tarjeta_nota">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <p class="text-muted small text-uppercase fw-bold mb-0">
                                                <i class="bi bi-sticky text-warning me-1"></i> Observaciones
                                            </p>
                                            <button class="btn btn-sm btn-soft-success text-primary rounded-circle p-1 lh-1 d-none" id="btn_activar_edicion">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </div>
                                        
                                        <div id="modo_lectura_nota" class="p-3 rounded-3 border h-100 d-flex align-items-center card-item">
                                            <p id="descripciones" class="small mb-0 placeholder-glow w-100">
                                                <span class="placeholder col-12 rounded mb-1"></span>
                                            </p>
                                        </div>

                                        <div id="modo_edicion_nota" class="d-none flex-column h-100">
                                            <textarea id="descripcion_input_inline" class="form-control form-control-sm mb-2 border-primary" rows="2"></textarea>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-soft-secondary me-3" id="btn_cancelar_edicion">
                                                    <i class="bi bi-x-circle me-2"></i> Cancelar
                                                </button>
                                                <button class="btn btn-sm btn-primary px-3 shadow-sm" type="submit" id="btn_guardar_edicion">
                                                    <i class="bi bi-check2-circle me-2"></i>
                                                    <span>Guardar</span>
                                                </button>
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
                            <div class="card p-4 pt-3 shadow">
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
      require_once ROOT_PATH . "/vista/caja_chica/gasto_caja_modal.php";
      require_once ROOT_PATH . "/vista/caja_chica/repocicion_caja_modal.php";
      require_once ROOT_PATH . "/vista/caja_chica/caja_chica_detalles.php";
  ?>
  
  <!-- Scripts personalizado -->
	<script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/caja_chica_ajax.js"></script>
  <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/caja_chica_validar.js"></script>
</body>
</html>