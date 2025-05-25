<table class="table caption-top table-hover align-middle">
	<caption>Lista de Conciliaciones Bancarias</caption>
	<thead class="table-light">
    	<tr>
    		<th>Mes</th>
    		<th>Estado</th>
    		<th>Saldo Inicial</th>
    		<th>Saldo Final</th>
    		<th>Saldo Sistema</th>
    		<th>Diferencia</th>
    		<th class="text-center">Movimientos Bancarios no reconciliados</th>
    		<th class="text-center">Registros de Sistema no Reconocidos</th>    		
    	</tr>
	</thead>
	<tbody>
    <?php foreach ($registros_conciliaciones as $registro) { ?>
    	<?php 
    	setlocale(LC_TIME, "es_ES");
    	$numero = date('n', strtotime($registro["fecha_inicio"]));
		$fecha = DateTime::createFromFormat('!m', $numero);
		$mes = strftime("%B", $fecha->getTimestamp()); 
		// la vaina sale in english
    	 ?>
    	<tr>
    		<td><?php echo $mes;?></td>
    		<td><?php echo $registro["estado"]; ?></td>
    		<td><?php echo $registro["saldo_inicio"] . "Bs."; ?></td>
    		<td><?php echo $registro["saldo_fin"] . "Bs."; ?></td>
    		<td><?php echo $registro["saldo_sistema"] . "Bs."; ?></td>
    		<td><?php echo $registro["diferencia"] . "Bs."; ?></td>
    		<td class="text-center"><?php echo "Ingreso: " . $registro["ingreso_banco_no_correspondido"] . "Bs. <br>Egreso: " . $registro["egreso_banco_no_correspondido"] . "Bs."; ?></td>
    		<td class="text-center"><?php echo "Ingreso: " . $registro["ingreso_sistema_no_correspondido"] . "Bs. <br>Egreso: " . $registro["egreso_sistema_no_correspondido"] . "Bs."; ?></td>
    	</tr>
    <?php } ?>
	</tbody>
</table>

