<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<?php
		require_once "vista/componentes/estilos.php";
	?>
	<link rel="stylesheet" type="text/css" href="recursos/css/publicaciones_inicio.css">
<!-- icono carga customizada -->
	<style type="text/css">
		.loader_publicaciones {
		  width: 48px;
		  height: 48px;
		  display: block;
		  margin:15px auto;
		  position: relative;
		  color: #FFF;
		  box-sizing: border-box;
		  animation: rotation 1s linear infinite;
		}
		.loader_publicaciones::after,
		.loader_publicaciones::before {
		  content: '';  
		  box-sizing: border-box;
		  position: absolute;
		  width: 24px;
		  height: 24px;
		  top: 50%;
		  left: 50%;
		  transform: scale(0.5) translate(0, 0);
		  background-color: #222;
		  border-radius: 50%;
		  animation: animloader 1s infinite backwards;
		}
		.loader_publicaciones::before {
		  background-color: #2A1CD1;
		  transform: scale(0.5) translate(-48px, -48px);
		}

		@keyframes rotation {
		  0% {
		    transform: rotate(0deg);
		  }
		  100% {
		    transform: rotate(360deg);
		  }
		} 
		@keyframes animloader {
		    50% {
		      transform: scale(1) translate(-50%, -50%);
			}
		}
	</style>
</head>
<body id="body-pd" class="body-pd">
	<div class="container-fluid">
		<div class="row flex-nowrap">
			<?php				
				require_once "vista/componentes/navbar.php";
			?>
			<div class="col d-flex flex-column gris">

				<?php require_once "vista/componentes/header.php"; ?>

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
									<div class="card-text placeholder-glow" id="esqueleto_titulo_1">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="card-text placeholder-glow my-3" id="esqueleto_canva_1">
										<span class="placeholder w-100 rounded" style="height: 9rem"></span>
                                    </div>
                                    <canvas id="canva_1" hidden=""></canvas>
									<div class="card-text placeholder-glow mb-2" id="esqueleto_dato_1_1">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="card-text placeholder-glow" id="esqueleto_dato_2_1">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="alert alert-warning text-center mx-auto" role="alert" id="div_alert_1" hidden="" style="width:fit-content;">
									  No hay Datos para el gráfico
									</div>
								</div>
								<div class="col-lg-7 col-md-9 text-center mt-2">
									<div class="card-text placeholder-glow" id="esqueleto_titulo_2">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="card-text placeholder-glow my-3" id="esqueleto_canva_2">
										<span class="placeholder w-100 rounded" style="height: 9rem"></span>
                                    </div>
                                    <canvas id="canva_2" hidden=""></canvas>
									<div class="card-text placeholder-glow mb-2" id="esqueleto_dato_1_2">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="card-text placeholder-glow" id="esqueleto_dato_2_2">
										<span class="placeholder w-100 rounded"></span>
									</div>
									<div class="alert alert-warning text-center mx-auto" role="alert" id="div_alert_2" hidden="" style="width:fit-content;">
									  No hay Datos para el gráfico
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					</div>
					<div class="d-flex justify-content-center mb-5"><span id="carga_publicaciones" hidden="" class="loader_publicaciones"></span></div>
				</main>

				<?php
					require_once 'vista/componentes/modal_carga.php';
					require_once "vista/componentes/script.php";
				?>
			</div>
		</div>
	</div>
	<?php require_once "vista/componentes/footer.php"; ?>
	<script type="text/javascript" src="recursos/estadisticas/chart.js"></script>
	<script src="recursos/js/consultas_ajax/inicio_ajax.js"></script>
</body>
</html>