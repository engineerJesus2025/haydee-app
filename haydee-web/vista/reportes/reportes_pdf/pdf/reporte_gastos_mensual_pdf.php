<?php
// --- Inicialización de variables para los cálculos ---
$total_fijos_bs = 0;
$total_variables_bs = 0;

// Array para traducir los meses a español
$meses_espanol = [
    1 => "ENERO", 2 => "FEBRERO", 3 => "MARZO", 4 => "ABRIL", 5 => "MAYO", 6 => "JUNIO",
    7 => "JULIO", 8 => "AGOSTO", 9 => "SEPTIEMBRE", 10 => "OCTUBRE", 11 => "NOVIEMBRE", 12 => "DICIEMBRE"
];
$nombre_mes = $meses_espanol[(int)$mes];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Relación de Gastos</title>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h3, .header p { margin: 2px 0; }
        .bg-grey { background-color: #E0E0E0; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h3>JUNTA DE CONDOMINIO EDIFICIO HAYDEE</h3>
        <p>RIF: J-30578545-7</p>
        <h3>RELACIÓN DE GASTOS DEL CONDOMINIO DEL MES DE <?php echo $nombre_mes; ?> <?php echo $anio; ?></h3>
    </div>

    <table>
        <thead>
            <tr class="bg-grey font-bold">
                <th>CONCEPTO</th>
                <th>GENERAL (Bs.)</th>
                <th>P/APTO. (Bs.)</th>
                <th>GENERAL (USD)</th>
                <th>P/APTO. (USD)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="5" class="font-bold bg-grey">CONCEPTOS FIJOS</td></tr>
            <?php foreach ($gastos_fijos as $gasto): $total_fijos_bs += $gasto['monto']; ?>
                <tr>
                    <td><?php echo htmlentities($gasto['descripcion_gasto']); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'], 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'] / $total_aptos, 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'] / $tasa_dolar, 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format(($gasto['monto'] / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="font-bold bg-grey">
                <td>TOTAL CONCEPTOS FIJOS</td>
                <td class="text-right"><?php echo number_format($total_fijos_bs, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_fijos_bs / $total_aptos, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_fijos_bs / $tasa_dolar, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format(($total_fijos_bs / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
            </tr>

            <tr><td colspan="5" class="font-bold bg-grey">CONCEPTOS VARIABLES</td></tr>
            <?php foreach ($gastos_variables as $gasto): $total_variables_bs += $gasto['monto']; ?>
                 <tr>
                    <td><?php echo htmlentities($gasto['descripcion_gasto']); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'], 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'] / $total_aptos, 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($gasto['monto'] / $tasa_dolar, 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format(($gasto['monto'] / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="font-bold bg-grey">
                <td>TOTAL CONCEPTOS VARIABLES</td>
                <td class="text-right"><?php echo number_format($total_variables_bs, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_variables_bs / $total_aptos, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_variables_bs / $tasa_dolar, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format(($total_variables_bs / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
            </tr>

             <?php $total_cuota_mes_bs = $total_fijos_bs + $total_variables_bs; ?>
             <tr class="font-bold bg-grey">
                <td>TOTAL CUOTA DEL MES</td>
                <td class="text-right"><?php echo number_format($total_cuota_mes_bs, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_cuota_mes_bs / $total_aptos, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_cuota_mes_bs / $tasa_dolar, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format(($total_cuota_mes_bs / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
            </tr>
            <tr class="font-bold bg-grey">
                <td>GAS LARA</td>
                <td class="text-right"><?php echo number_format($total_gas_bs, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_gas_bs / $total_aptos, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($total_gas_bs / $tasa_dolar, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format(($total_gas_bs / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
            </tr>
             <?php $gran_total_bs = $total_cuota_mes_bs + $total_gas_bs; ?>
             <tr class="font-bold bg-grey">
                <td>TOTAL A PAGAR</td>
                <td class="text-right"><?php echo number_format($gran_total_bs, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($gran_total_bs / $total_aptos, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($gran_total_bs / $tasa_dolar, 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format(($gran_total_bs / $total_aptos) / $tasa_dolar, 2, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>