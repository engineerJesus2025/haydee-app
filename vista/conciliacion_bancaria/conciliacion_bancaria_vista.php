<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
    <style type="text/css">
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
          padding-top: 15px;
          padding-bottom: 15px;
        }

    </style>
</head>

<body id="body-pd mb-2" class="body-pd">
  <input type="text" hidden="" id="permiso_registrar" value="<?php echo Conciliacion_bancaria::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, REGISTRAR) ?>">
  <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Conciliacion_bancaria::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, ELIMINAR) ?>">
  <input type="text" hidden="" id="permiso_editar" value="<?php echo Conciliacion_bancaria::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, MODIFICAR) ?>">
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
						<h2>CONCILIACION BANCARIA</h2>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row mb-3 align-items-center">
					  <div class="col-4">
					    <div class="card p-4">
                <h5 class="mb-4">Seleccione el mes para Evaluar</h5>
					    	<div class="col-md-12">
						      <div class="input-group mb-3">
						        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-date"></i></span>
						        <select class="form-select " aria-label="Default select example" name="mes_seleccionado" id="mes_select">
                      <option selected hidden value="">Seleccionar Mes</option>
						        </select>
						      </div>
						    </div>
						    <span id="span_select" class="my-3"></span>
                <button id="boton_conciliaciones" class="btn btn-primary" tabindex="-1" role="button" aria-disabled="true" data-bs-toggle="modal" data-bs-target="#modal_conciliaciones" title="Mostrar Conciliaciones">Mostrar Todas las Conciliaciones</button>
					    </div>
					  </div>
            <div class="col-8" hidden="">
              <div class="card px-4">
                <div class="col-md-12">
                  <table class="table table-hover caption-top" id="tabla_resumen">
                    <caption>Resumen de Conciliación</caption>
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Sistema</th>
                        <th scope="col" class="text-end" colspan="2">Banco</th>
                        <!-- <th scope="col">Conciliado</th> -->
                        <th scope="col" class="text-end">Diferencia</th>
                      </tr>
                    </thead>
                    <tbody class="table-group-divider">
                      <tr>
                        <th scope="row">Ingreso</th>
                        <td class="text-success" >Sin datos</td>
                        <td class="text-success text-end" colspan="2">Sin datos</td>
                        <!-- <td class="text-success">Sin datos</td> -->
                        <td class="text-end">Sin datos</td>
                      </tr>
                      <tr>
                        <th scope="row">Egreso/Gasto</th>
                        <td class="text-danger">Sin datos</td>
                        <td class="text-danger text-end" colspan="2">Sin datos</td>
                        <!-- <td class="text-danger">Sin datos</td> -->
                        <td class="text-end">Sin datos</td>
                      </tr>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <td>Saldo Registrado:</td>
                        <td>0</td>
                        <td>Saldo del Banco:</td>
                        <td>0</td>
                        <td class="text-end">Sin datos</td>
                    </tfoot>
                  </table>
                </div>
                <span id="span_select"></span>
              </div>
            </div>
					</div>
					<div class="row mb-3 justify-content-center">
            <div class="col-8">
              <div class="card p-3 pt-3">
                <table id="tabla_registros_sistema" class="table caption-top table-hover" style="width: 98%">
                	<caption>Registros del Sistema</caption>
                  <thead>
                    <tr>
                      <th class="text-center">Estado</th>
                      <th class="text-center">Ingreso/Egreso</th>
                      <th class="text-center">Fecha</th>
                      <th class="text-center">Monto</th>
                      <th class="text-center">Referencia</th>
                      <th class="text-center">Apartamento/Proveedor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="7"><h5>Ningún mes seleccionado</h5></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-4 p-2 pt-5">
              <div class="card p-2 pt-4 mt-1" hidden>
                <table id="tabla_movimientos_sistema" class="table caption-top table-hover">
                	<caption>Movimientos Bancarios</caption>
                  <thead>
                    <tr>
                    	<th>Monto</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="2"><h6>Ningún mes seleccionado</h6></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-5 p-3 card my-3">
              <label for="observaciones">Observaciones</label>
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="observaciones" id="observaciones" aria-label="observaciones" aria-describedby="basic-addon1" minlength="3" maxlength="30" placeholder="Aquí puede anotar Observaciones">
                <span class="w-100"></span>
              </div>
            </div>
          <?php if (Conciliacion_bancaria::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, REGISTRAR)){ ?>
            <div class="col-12 text-center mt-3" hidden="">
            	<button id="boton_registrar_conciliacion" class="btn btn-primary">Guardar Conciliación Bancaria</button>
            </div>
          <?php } ?>
          </div>
          <!-- Modales -->
          <div class="modal fade" id="modal_movimientos" tabindex="-1" aria-labelledby="titulo_modal_movimientos" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="titulo_modal_movimientos">Registrar Movimiento Bancario</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php
                  require_once "vista/conciliacion_bancaria/movimientos_modal.php";
                  ?>
                </div>
              </div>
            </div>
          </div>

          <div class="modal fade" id="modal_conciliaciones" tabindex="-1" aria-labelledby="titulo_modal_conciliaciones" aria-hidden="true">
            <div class="modal-dialog modal-xl">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="titulo_modal_conciliaciones">Conciliaciones Bancarias</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php
                  require_once "vista/conciliacion_bancaria/conciliaciones_modal.php";
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
	<script type="text/javascript" src="recursos/js/consultas_ajax/conciliacion_bancaria_ajax.js"></script>
    <script type="text/javascript" src="recursos/js/validaciones/conciliacion_bancaria_validar.js"></script>
</body>
</html>