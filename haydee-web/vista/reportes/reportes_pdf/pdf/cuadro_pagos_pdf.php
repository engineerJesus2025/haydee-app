<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuadro de Pagos</title>
    <style type="text/css">
        @page { margin: 40px 30px; }
    	body { font-family: Arial, sans-serif; color: #343a40; }
		h1 { text-align: center; color: #343a40; margin-bottom: 10px; font-size: 1.8em; border-bottom: 2px solid #6c757d; padding-bottom: 10px; }
		.subtitle { text-align: center; color: #6c757d; margin-bottom: 25px; font-size: 0.95em; line-height: 1.5; }
		table { width: 100%; border-collapse: collapse; background-color: #bccec6; }
		table th, table td { padding: 8px 10px; text-align: right; border: 1px solid #ddd; font-size: 0.9em; }
        table th:first-child, table td:first-child { text-align: left; font-weight: bold; }
		table thead th { background-color: #9db0a7; color: #333; font-weight: bold; border-bottom: 2px solid #ced4da; text-align: center; }
		table tbody tr:nth-child(odd) { background-color: #d1e7dd; }
        .total-row td { background-color: #8c9f96; color: white; font-weight: bold; }
        .text-center { text-align: center !important; }
        .text-muted { color: #6c757d !important; font-style: italic; }
    </style>
</head>
<body>
	<div>
		<h1>CUADRO DE PAGOS</h1>
		<p class="subtitle">
            ESTADO DE CUENTAS RESIDENCIAS HAYDEE<br>
            <strong>TASA BCV DEL DÍA: <?php echo number_format($tasa_dolar["tasa_dolar"], 2, '.', ''); ?> Bs/$</strong>
        </p>
		<table>
			<thead>
			<tr>
				<th>APTO.</th>
				<?php foreach ($cabecera_tabla as $mes) {?>
					<th><?php echo $mes ?></th>
				<?php } ?>
				<th>Total Deuda ($)</th>
			</tr>
			</thead>
			<tbody>
			<?php 			
			foreach ($cuerpo_tabla as $nro_apartamento => $apartamento) {
				$total_fila = array_sum($apartamento);
				?>
				<tr>
                    <td><?php echo $nro_apartamento?></td>
                    <?php 
                    if ($total_fila < 0.01) {
                        // Si no debe nada, llenamos toda la fila con "Solvente"
                        echo "<td colspan='" . count($apartamento) . "' class='text-center text-muted'>Solvente (Al día)</td>";
                        echo "<td class='text-muted'>$ 0.00</td>";
                    } else {
                        // Si debe, mostramos los montos
                        foreach ($apartamento as $indice => $valor_deuda) {
                            echo "<td>" . ($valor_deuda < 0.01 ? "-" : "$ " . number_format($valor_deuda, 2, '.', '')) . "</td>";
                        }
                        echo "<td style='font-weight: bold; color: #b02a37;'>$ " . number_format($total_fila, 2, '.', '') . "</td>";
                    }
                    ?>
				</tr>
			<?php } ?>
			<tr class="total-row">
				<td>Total Mes:</td>
				<?php $total_general = 0;
			    foreach ($total_mensual as $total_mes) {
					$total_general += $total_mes;
				?>
				<td><?php echo ($total_mes < 0.01) ? "-" : "$ " . number_format($total_mes, 2, '.', ''); ?></td>
			    <?php } ?>
				<td style="font-size: 1.1em;">$ <?php echo number_format($total_general, 2, '.', ''); ?></td>
			</tr>
			</tbody>
		</table>

        <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; text-align: right;">
            <h3 style="margin: 0; color: #333;">
                TOTAL A PAGAR EN BOLÍVARES: 
                <span style="color: #0d6efd;">
                    <?php echo number_format($total_general * $tasa_dolar["tasa_dolar"], 2, '.', ''); ?> Bs.
                </span>
            </h3>
            <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;">
                * Calculado a la tasa BCV del día (<?php echo number_format($tasa_dolar["tasa_dolar"], 2, '.', ''); ?> Bs/$).
            </p>
        </div>

        <div style="margin-top: 20px; font-size: 0.85em; color: #555;">
            <p><strong>Nota:</strong> Los montos históricos están fijados en dólares. El pago se realizará en Bolívares a la tasa oficial del día de la transacción.</p>
            <p><strong>Reglas de pago:</strong> Del 1 al 15 de cada mes se aplica la tasa BCV. Del 16 en adelante se aplica la tasa Promedio.</p>
        </div>
	</div>	
</body>
</html>