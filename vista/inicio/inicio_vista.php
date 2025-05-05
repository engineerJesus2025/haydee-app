<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
		require_once "vista/componentes/estilos.php";
	?>
</head>

<body id="body-pd" style="">

	<div class="container-fluid">
		<div class="row flex-nowrap ">
			<?php
				require_once "vista/componentes/sesion.php";
				require_once "vista/componentes/header.php";
			?>

			<div class="col d-flex flex-column  min-vh-100 gris">

			<?php
				require_once "vista/componentes/navbar.php";
			?>

				<main class="col ps-md-2 pt-2">
					<div class="page-header pt-3">
						<h2>INICIO</h2>
						<p>proximamente</p>
					</div>
					<p class="lead"></p>
					<hr>
				</main>
				<?php
				require_once "vista/componentes/footer.php";
				require_once "vista/componentes/script.php";
				?>
			</div>
		</div>
	</div>

</body>

</html>