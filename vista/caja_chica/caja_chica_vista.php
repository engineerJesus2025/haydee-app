<!DOCTYPE html>
<html>

<head>
	<title>Caja chica</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
    <!-- <style type="text/css">
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
          padding-top: 15px;
          padding-bottom: 15px;
        }

    </style> -->
</head>

<body id="body-pd mb-2" class="body-pd">
  <input type="text" hidden="" id="permiso_registrar" value="<?php echo Caja_chica::tiene_permiso(GESTIONAR_CAJA_CHICA, REGISTRAR) ?>">
  <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Caja_chica::tiene_permiso(GESTIONAR_CAJA_CHICA, ELIMINAR) ?>">
  <input type="text" hidden="" id="permiso_editar" value="<?php echo Caja_chica::tiene_permiso(GESTIONAR_CAJA_CHICA, MODIFICAR) ?>">
	<div class="container-fluid">
		<div class="row flex-nowrap mb-2">
			<?php
				require_once "vista/componentes/sesion.php";
				require_once "vista/componentes/navbar.php";
			?>
			<div class="col d-flex flex-column  min-vh-100 gris">
			<?php
				require_once "vista/componentes/header.php";
			?>
				<main class="col ps-md-2 pt-2 mb-5">
					<div class="page-header pt-3">
						<h2>CAJA CHICA</h2>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row mb-3 align-items-center justify-content-evenly">
					  <div class="col-6 px-5">
					    <div class="card p-4">
                <h5 class="mb-4">Seleccione el mes para evaluar:</h5>
					    	<div class="col-md-12">
						      <div class="input-group mb-3">
						        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-date"></i></span>
						        <select class="form-select " aria-label="Default select example" name="mes_seleccionado" id="mes_select">
                      <option selected hidden value="">Seleccionar Mes</option>
						        </select>
						      </div>
						    </div>
						    <span id="span_select" class="my-3"></span>
					    </div>
					  </div>
            <div class="col-6 px-5">
              <div class="card px-4" hidden="">
                <div class="col-md-12">
                  <table class="table table-hover caption-top" id="tabla_resumen">
                    <caption>Resumen de Movimientos</caption>
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">Ingresos</th>
                        <td class="text-success" >Sin datos</td>
                      </tr>
                      <tr>
                        <th scope="row">Egresos/Gastos</th>
                        <td class="text-danger">Sin datos</td>
                      </tr>
                    </tbody>
                    <tfoot class="table-group-divider">
                      <tr>
                        <th scope="row">Saldo Actual</th>
                        <td>Sin datos</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <span id="span_select"></span>
              </div>
            </div>
					</div>
					<div class="row mb-3 justify-content-center">
            <div class="col-11">
              <div class="card p-4 pt-3">
                <table id="tabla_registros_sistema" class="table caption-top table-hover" style="width: 98%">
                	<caption>Movimientos Registrados</caption>
                  <thead>
                    <tr>
                      <th class="text-center">Ingreso/Egreso</th>
                      <th class="text-center">Fecha</th>
                      <th class="text-center">Monto</th>
                      <th >Apartamento/Proveedor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="5"><h5>Ningún mes seleccionado...</h5></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div class="col-7 row p-3 card my-3" hidden="">
              <h4 class="h4 col">Observaciones:</h4>              
              <div class="col-12">
                <p id="observaciones"></p>
              </div>
              <div class="col">
                <button id="boton_editar_observacion" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_observaciones">editar</button>
              </div>
            </div>
          </div>
          <div class="modal fade" id="modal_observaciones" tabindex="-1" aria-labelledby="titulo_modal_observaciones" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="titulo_modal_observaciones">Añadir Observación</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php
                  require_once "vista/caja_chica/observaciones_modal.php";
                  ?>
                </div>
              </div>
            </div>
          </div>

				</main>
				<?php
				require_once "vista/componentes/footer.php";
				require_once "vista/componentes/script.php";
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="recursos/js/consultas_ajax/caja_chica_ajax.js"></script>
  <script type="text/javascript" src="recursos/js/validaciones/caja_chica_validar.js"></script>
</body>
</html>