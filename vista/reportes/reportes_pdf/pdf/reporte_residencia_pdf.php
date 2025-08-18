<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Residencia</title>
    <style type="text/css">
    	:root, [data-bs-theme="light"] {
		  --bs-body-font-family: var(--bs-font-sans-serif);
		  --bs-body-font-size: 1rem;
		  --bs-body-font-weight: 400;
		  --bs-body-line-height: 1.5;
		  --bs-body-color: #212529;
		  --bs-heading-color: inherit;
		  --bs-font-sans-serif: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue","Noto Sans","Liberation Sans",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
		  --bs-white-rgb: 255,255,255;
		}
    	body {
		  font-family: var(--bs-body-font-family);
		  font-size: var(--bs-body-font-size);
		  font-weight: var(--bs-body-font-weight);
		  line-height: var(--bs-body-line-height);
		  color: var(--bs-body-color);
		  -webkit-text-size-adjust: 100%;
		}
    	.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
		  margin-top: 0;
		  margin-bottom: .5rem;
		  font-weight: 500;
		  line-height: 1.2;
		  color: var(--bs-heading-color);
		}
		*, ::after, ::before {
		  box-sizing: border-box;
		}
    	.p_contenido{
    		text-align: justify;
    	}
    	.text-center {
		  text-align: center !important;
		}
		.justify-content-center {
		  justify-content: center !important;
		}
		.row {
		  --bs-gutter-x: 1.5rem;
		  --bs-gutter-y: 0;
		  display: flex;
		  flex-wrap: wrap;
		  margin-top: calc(-1 * var(--bs-gutter-y));
		  margin-right: calc(-.5 * var(--bs-gutter-x));
		  margin-left: calc(-.5 * var(--bs-gutter-x));
		}
		.my-5 {
		  margin-top: 3rem !important;
		  margin-bottom: 3rem !important;
		}
		.col-12 {
		  flex: 0 0 auto;
		  width: 100%;
		}
		.mb-4 {
		  margin-bottom: 1.5rem !important;
		}
		.col-8 {
		  flex: 0 0 auto;
		  width: 66.66666667%;
		}
		.text-white {
		  --bs-text-opacity: 1;
		  color: rgba(var(--bs-white-rgb),var(--bs-text-opacity)) !important;
		}
		.text-start {
		  text-align: left !important;
		}
		.mb-3 {
		  margin-bottom: 1rem !important;
		}
		.mb-5 {
		  margin-bottom: 3rem !important;
		}
		.my-2 {
		  margin-top: .5rem !important;
		  margin-bottom: .5rem !important;
		}
		.col-2 {
		  flex: 0 0 auto;
		  width: 16.66666667%;
		}
		h5 {
		  font-size: 1.25rem;
		}
    </style>
</head>
<body>
<div class="row text-center justify-content-center">
	<div class="col-12 my-5">
		<h5 class="">CONSTANCIA DE RESIDENCIA</h5>
	</div>
	<div class="col-8 mb-4" style="margin: auto;">
		<p class="p_contenido"><b class="text-white" style="color: white">_____ </b>Por medio de la presente se hace constar, que <?php echo ($registro_porpietario["sexo"] == "Masculino")?"el":"la" ?> ciudadan<?php echo ($registro_porpietario["sexo"] == "Masculino")?"o":"a" ?> <?php echo $registro_porpietario["nombre"] . " " . $registro_porpietario["apellido"] ?> portador<?php echo ($registro_porpietario["sexo"] == "Masculino")?"":"a" ?> de la cédula de identidad número V-<?php echo $registro_porpietario["cedula"] ?> vive en el apartamento Nº <?php echo $registro_porpietario["apartamento"] ?>, piso <?php echo explode("-", $registro_porpietario["apartamento"])[0] ?>, del edificio Haydee ubicado en la carrera 22 entre calles 13 y 14, municipio Iribarren parroquia Catedral del estado Lara, Barquisimeto.</p>
	</div>
	<div class="col-8 text-start mb-3" style="margin: auto;">
		<p>Constancia que se expide a los <?php echo date("d"); echo (date("d") != 1)?" dias":" dia"; ?> del mes de <?php echo $meses[date('n')-1]; ?> del año <?php echo date("Y") ?>.</p>
	</div>
	<div class="col-8 text-start mb-5" style="margin: auto;">
		<p>Sin más a lo que refiere.</p>
	</div>
	<div class="col-12">
		<p>Atentamente</p>
	</div>
	<div class="col-2 my-2" style="margin: auto;">
		<hr>
	</div>
	<div class="col-12">
		<p>Presidente</p>
	</div>
	<div class="col-12 mb-4">
		<p>CI 3394818</p>
	</div>
	<div class="col-12">
		<p>Junta de Condominio Edificio Haydee</p>
	</div>
	<div class="col-12">
		<p>Teléfono 04120567747</p>
	</div>
</div>
</body>
</html>