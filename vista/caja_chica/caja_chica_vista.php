<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
	<title>Caja chica</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once ROOT_PATH . "/vista/componentes/estilos.php";
	?>
</head>

<body id="body-pd" class="body-pd">  
  <input type="text" hidden id="permiso_registrar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_CAJA_CHICA, REGISTRAR) ?>">
  <input type="text" hidden id="permiso_eliminar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_CAJA_CHICA, ELIMINAR) ?>">
  <input type="text" hidden id="permiso_modificar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_CAJA_CHICA, MODIFICAR) ?>">
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
					<div class="row mb-3 align-items-center justify-content-start">
					  <div class="col-md-5 mt-2">
					    <div class="card p-4">
                <h5 class="mb-4">Seleccione el mes para evaluar:</h5>
					    	<div class="col-md-12">
						      <div class="input-group">
						        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-date"></i></span>
  						        <select class="form-select " aria-label="Default select example" name="mes_seleccionado" id="mes_select">
                        <option>Cargando registros...<option>
  						        </select>
                    <span class="w-100 invalid-feedback"></span>
						      </div>
						    </div>
						    <span id="span_fondo_fijo" class="text-muted mb-2 mt-1">
                  <div class="placeholder-glow m-0">
                    <span class="placeholder placeholder-lg w-100 rounded m-0"></span>
                  </div>      
                </span>
                <span id="span_caja_activa">
                  <div class="placeholder-glow m-0">
                    <span class="placeholder placeholder-lg w-100 rounded m-0"></span>
                  </div>
                </span>
                <div id="botones_movimientos" class="mt-3" hidden>
                  <button class="btn btn-primary m-1" id="boton_registrar_gasto" data-bs-toggle="modal" data-bs-target="#modal_registro_gastos" data-tooltip="true" title="Registrar Nuevo Gasto de Caja">Nuevo Gasto</button>
                  <button class="btn btn-secondary m-1" id="boton_reponer_caja" data-bs-toggle="modal" data-bs-target="#modal_reponer_caja" data-tooltip="true" title="Reponer Saldo de Caja">Reponer Caja</button>
                </div>
					    </div>
					  </div>            
					</div>
					<div class="row mb-3 justify-content-center">
            <div class="col-12">
              <div class="card p-4 pt-3">
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                  <h5 class="text-muted mb-0"><i class="bi bi-list-check me-2"></i>Historial de Movimientos</h5>
                  <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="busqueda_global" data-tooltip="true" title="Buscar Registro" placeholder="Buscar movimiento...">
                  </div>
                </div>
                <div id="tabla_registros_sistema" class="tabla-sistema-haydee"></div>
              
              </div>
            </div>
            
            <div class="col-7 row p-3 card my-3" hidden>
              <h4 class="h4 col">Descripción de caja:</h4>              
              <div class="col-12">
                <p id="descripciones"></p>
              </div>
              <div class="col">
                <button id="boton_modificar_observacion" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_descripciones">modificar</button>
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
  ?>
  
  <!-- Modales -->
  <div class="modal fade" id="modal_descripciones" tabindex="-1" aria-labelledby="titulo_modal_descripciones" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h1 class="modal-title fs-5" id="titulo_modal_descripciones">Cambiar  Descripción</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php
          require_once ROOT_PATH . "/vista/caja_chica/descripciones_modal.php";
          ?>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modal_registro_gastos" tabindex="-1" aria-labelledby="titulo_modal_registro_gasto" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h1 class="modal-title fs-5" id="titulo_modal_registro_gasto">Registrar Gasto de Caja</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php
          require_once ROOT_PATH . "/vista/caja_chica/gasto_caja_modal.php";
          ?>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modal_reponer_caja" tabindex="-1" aria-labelledby="titulo_modal_reponer_caja" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h1 class="modal-title fs-5" id="titulo_modal_reponer_caja">Reponer Caja</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php
            require_once ROOT_PATH . "/vista/caja_chica/repocicion_caja_modal.php";
          ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts personalizado -->
	<script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/caja_chica_ajax.js"></script>
  <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/caja_chica_validar.js"></script>
</body>
</html>