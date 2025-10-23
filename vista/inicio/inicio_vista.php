<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
	<link rel="stylesheet" type="text/css" href="recursos/css/publicaciones_inicio.css">
</head>

<body id="body-pd" class="body-pd mb-5">

	<div class="container-fluid">
		<div class="row flex-nowrap ">
			<?php				
				require_once "vista/componentes/navbar.php";
			?>

			<div class="col d-flex flex-column  min-vh-100 gris">

			<?php
				
				require_once "vista/componentes/header.php";
			?>
				<main class="col ps-md-2 pt-2">
					<div class="page-header pt-3">
						<h2>INICIO</h2>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row justify-content-center" id="contenido">
					<?php if ($_SESSION["rol"] != "Propietario") { ?>
						<div class="card p-4 col-11 my-4" id="div_graficos">
							<div class="row justify-content-around">
								<div class="col-lg-4 col-md-9 mt-2">
									<h5 class="text-center">Deudas de apartamentos</h5>
									<canvas id="canva_1"></canvas>
									<p class="mt-4">Total de deuda de los apartamentos: <b class="text-danger" id="b_moroso"></b></p>
									<p>Total de ingresos de los apartamentos: <b class="text-success" id="b_solvente"></b></p>
									<div class="alert alert-warning" role="alert" id="div_alert_1" hidden="">
									  No hay Datos para el gráfico
									</div>
								</div>
								<div class="col-lg-7 col-md-9 text-center mt-2">
									<h5 class="text-center">Resumen de balance del mes</h5>
									<canvas id="canva_2"></canvas>
									<p class="mt-5">Total de gastos del mes: <b class="text-danger" id="b_gastos"></b></p>
									<p>Total de ingresos del mes: <b class="text-success" id="b_ingresos"></b></p>

									<div class="alert alert-warning" role="alert" id="div_alert_2" hidden="">
									  No hay Datos para el gráfico
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					</div>
				</main>

				<?php
				require_once 'vista/componentes/modal_carga.php';
				require_once "vista/componentes/footer.php";
				require_once "vista/componentes/script.php";
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="recursos/estadisticas/chart.js"></script>
	<script src="recursos/js/consultas_ajax/inicio_ajax.js"></script>

</body>

</html>