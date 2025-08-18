<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Residencia</title>
    <style type="text/css">
    	body {
	    font-family: Arial, sans-serif;
	    margin: 20px;
	  
	    color: #343a40; /* Color de texto general */
		}

		h1 {
		    text-align: center;
		    color: #343a40;
		    margin-bottom: 20px;
		    font-size: 1.8em;
		    border-bottom: 2px solid #6c757d; /* Línea debajo del título */
		    padding-bottom: 10px;
		    display: inline-block; /* Para que la línea se ajuste al texto */
		    position: relative;
		    left: 50%;
		    transform: translateX(-50%);
		}

		.subtitle {
		    text-align: center;
		    color: #6c757d;
		    margin-bottom: 30px;
		    font-size: 0.9em;
		}

		table {
		    width: 100%;
		    border-collapse: collapse; 
		    margin-top: 20px;
		    background-color:  #bccec6;
		    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); 
		}

		table th,
		table td {
		    padding: 12px 15px;
		    text-align: left;
		    border: 1px solid #ddd; /* Borde para todas las celdas */
		}

		table thead th {
		    background-color: #9db0a7; /* Color de fondo para el encabezado */
		    color: #333; /* Color de texto para el encabezado */
		    font-weight: bold;
		    border-bottom: 2px solid #ced4da; /* Borde inferior más grueso en el encabezado */
		}

		table tbody tr:nth-child(odd) {
		    background-color: #d1e7dd; /* Color de fondo para filas impares */
		}

		table tbody tr:hover {
		    background-color: #e2e6ea; /* Color de fondo al pasar el ratón por encima */
		}
    </style>
</head>
<body>
	<div>
		<h1>CUADRO DE PAGOS</h1>
		<p class="subtitle">ESTADO DE CUENTAS RESIDENCIAS HAYDEE (TASA <?php echo "01-" . $tasa_dolar["mes"] . "-" . $tasa_dolar["anio"] ?> BCV <?php echo $tasa_dolar["monto_dolar"] ?>Bs.*$)</p>
		<table>
				
			<thead>
			<tr>
				<th>APTO.</th>
				<?php foreach ($meses_seleccionados as $mes) {?>
					<th>Cuota <?php echo $mes ?></th>
				<?php } ?>
				<th>Total Deuda</th>
			</tr>
			</thead>
			<tbody>
			<?php 			
			foreach ($apartamentos_mensualidades as $apartamentos) {
				$total = 0;?>
				<tr>
				<?php foreach ($apartamentos as $indice => $apartamento) {
				if (!(str_contains($apartamento,"-"))) {
					$total += floatval($apartamento);
					if (isset($total_mensual[$indice])) {
						$total_mensual[$indice] += floatval($apartamento);
					}
					else{
						array_push($total_mensual, floatval($apartamento));
					}
				}
				?>
					<td><?php echo $apartamento?></td>

				<?php } ?>
					<td><?php echo $total ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td>Total:</td>
				<?php $total = 0;
			foreach ($total_mensual as $total_mes) {
				if ($total_mes == 0) {
					continue;
				}
					$total += $total_mes;
					?>
				<td><?php echo $total_mes; ?></td>
			<?php } ?>
				<td><?php echo $total; ?></td>
			</tr>
			</tbody>
		</table>
	</div>	
</body>
</html>
