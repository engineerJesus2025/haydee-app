<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
</head>

<body id="body-pd mb-2">

	<div class="container-fluid">
		<div class="row flex-nowrap mb-2">
			<?php
				require_once "vista/componentes/sesion.php";
				require_once "vista/componentes/header.php";
			?>
			<div class="col d-flex flex-column  min-vh-100 gris">
			<?php
				require_once "vista/componentes/navbar.php";
			?>
				<main class="col ps-md-2 pt-2 mb-5">
					<div class="page-header pt-3">
						<h2>CONCILIACION BANCARIA</h2>						
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row mb-3">
					    <div class="col-4">
					        <div class="card p-4">

					        	<div class="col-md-8">
						            <label for="mes_seleccionado">Seleccione el mes para Evaluar</label>
						            <div class="input-group mb-3">
						                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-date"></i></span>
						                <select class="form-select " aria-label="Default select example" name="mes_seleccionado" id="mes_select"><option selected hidden value="">Seleccionar Mes</option>
						                </select>
						            </div>
						        </div>
						        <span id="span_select"></span>
					        </div>
					    </div>
					</div>
					<div class="row mb-3">
                        <div class="col-8">
                            <div class="card p-2 pt-3">
                                <table id="tabla_registros_sistema" class="table caption-top" style="">
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
                        <div class="col-4">
                            <div class="card p-2 pt-5">
                                <table id="tabla_movimientos_sistema" class="table caption-top">
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
                        <div class="col-12 text-center mt-3">
                        	<button class="btn btn-primary">Registrar Conciliación Bancaria</button>

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
</body>

</html>