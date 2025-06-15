<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
</head>

<body id="body-pd" class="body-pd mb-5">

	<div class="container-fluid">
		<div class="row flex-nowrap ">
			<?php
				require_once "vista/componentes/sesion.php";
				require_once "vista/componentes/navbar.php";
			?>

			<div class="col d-flex flex-column  min-vh-100 gris">

			<?php
				
				require_once "vista/componentes/header.php";
			?>

				<main class="col ps-md-2 pt-2">
					<div class="page-header pt-3">
						<h2>INICIO</h2>
						<p>Bueno seria mostrar un grafico o algo</p>
					</div>
					<p class="lead"></p>
					<hr>
					<div class="row justify-content-center" id="contenido"></div>
				</main>
				<?php
				require_once "vista/componentes/footer.php";
				require_once "vista/componentes/script.php";
				?>
			</div>
		</div>
	</div>
	<script src="recursos/js/consultas_ajax/notificaciones_ajax.js"></script>
	<script src="recursos/js/consultas_ajax/inicio_ajax.js"></script>

</body>

</html>