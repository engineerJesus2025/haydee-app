<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos y Egresos</title>
    <style>
        /* Estilos compatibles con Dompdf */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            margin: 1.5cm 1cm;
            color: #333;
            line-height: 1.4;
        }
        h2 {
            font-size: 18pt;
            color: #2c3e50;
            margin: 0 0 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h3 {
            font-size: 14pt;
            color: #2980b9;
            margin: 15px 0 10px;
            border-bottom: 2px solid #2980b9;
            padding-bottom: 5px;
        }
        h4 {
            font-size: 12pt;
            color: #7f8c8d;
            margin: 0 0 20px;
            font-weight: normal;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .section {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            border: 1px solid #2980b9;
        }
        td {
            padding: 8px;
            border: 1px solid #bdc3c7;
            vertical-align: top;
        }
        .card {
            border: 1px solid #3498db;
            border-radius: 5px;
            margin: 10px 0;
        }
        .card-header {
            background-color: #ecf0f1;
            font-weight: bold;
            padding: 8px 12px;
            border-bottom: 1px solid #bdc3c7;
            font-size: 12pt;
            color: #2c3e50;
        }
        .card-body {
            padding: 10px;
        }
        .badge {
            background-color: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10pt;
            display: inline-block;
        }
        .badge-danger {
            background-color: #e74c3c;
        }
        .badge-info {
            background-color: #3498db;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        hr {
            border: 0;
            border-top: 1px solid #bdc3c7;
            margin: 20px 0;
        }
        .grafico-container {
            text-align: center;
            margin: 20px 0;
        }
        .grafico-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ccc;
            padding: 5px;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #95a5a6;
            margin-top: 30px;
            border-top: 1px dashed #bdc3c7;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header">
        <h2>REPORTE ESTADÍSTICO DE INGRESOS Y EGRESOS</h2>
        <h4><?php echo isset($fecha) ? $fecha : ''; ?></h4>
    </div>

    <!-- GRÁFICO (si aplica) -->
    <?php if ($selecion == "solo_grafico" || $selecion == "grafico_texto"): ?>
        <div class="section">
            <h3>Gráfico de Barras</h3>
            <div class="grafico-container">
                <?php if (!empty($barra)): ?>
                    <img src="<?php echo $barra; ?>" alt="Gráfico de ingresos/egresos">
                <?php else: ?>
                    <p>No se pudo generar el gráfico.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- DATOS ESTADÍSTICOS (si aplica) -->
    <?php if ($selecion == "solo_texto" || $selecion == "grafico_texto"): ?>
        <div class="section">
            <h3>Resumen de Estadísticas</h3>

            <!-- Totales generales y balance -->
            <?php
            // Calcular balance
            $ingreso_num = floatval($total_pagos);
            $egreso_num = floatval($total_gastos);
            $balance = $ingreso_num - $egreso_num;
            $balance_formateado = number_format($balance, 2, ',', '.') . ' Bs.';
            $clase_balance = $balance >= 0 ? 'badge' : 'badge badge-danger';
            ?>
            <table style="width: 70%; margin: 0 auto;">
                <tr>
                    <th colspan="2" style="background-color: #2c3e50;">Resumen General</th>
                </tr>
                <tr>
                    <td><strong>Total de Pagos realizados (Ingresos):</strong></td>
                    <td class="text-right"><span class="badge"><?php echo $total_pagos; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Total de Gastos realizados (Egresos):</strong></td>
                    <td class="text-right"><span class="badge badge-danger"><?php echo $total_gastos; ?></span></td>
                </tr>
                <tr class="total-row">
                    <td><strong>Balance (Ingresos - Egresos):</strong></td>
                    <td class="text-right"><span class="<?php echo $clase_balance; ?>"><?php echo $balance_formateado; ?></span></td>
                </tr>
            </table>

            <hr>

            <!-- Métodos de pago (dos columnas) -->
            <table width="100%">
                <tr>
                    <th style="background-color: #27ae60;">Métodos de Pago (Ingresos)</th>
                    <th style="background-color: #e74c3c;">Métodos de Pago (Egresos)</th>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <table width="100%" style="border: none;">
                            <tr>
                                <td><strong>Efectivo:</strong></td>
                                <td class="text-right"><?php echo $pagos_efectivo; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Transferencia:</strong></td>
                                <td class="text-right"><?php echo $pagos_transferencia; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pago Móvil:</strong></td>
                                <td class="text-right"><?php echo $pagos_pago_movil; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="vertical-align: top;">
                        <table width="100%" style="border: none;">
                            <tr>
                                <td><strong>Efectivo:</strong></td>
                                <td class="text-right"><?php echo $gastos_efectivo; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Transferencia:</strong></td>
                                <td class="text-right"><?php echo $gastos_transferencia; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pago Móvil:</strong></td>
                                <td class="text-right"><?php echo $gastos_pago_movil; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <hr>

            <!-- Desglose por fechas (marcas de tiempo) -->
            <h3>Desglose por Períodos</h3>
            <table width="100%">
                <tr>
                    <th style="background-color: #2980b9;">Ingresos (Pagos)</th>
                    <th style="background-color: #2980b9;">Egresos (Gastos)</th>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <?php if (!empty($fecha_pagos)): ?>
                            <?php echo $fecha_pagos; // Ya contiene <p> o texto formateado ?>
                        <?php else: ?>
                            <p class="text-center">No hay registros</p>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align: top;">
                        <?php if (!empty($fecha_gastos)): ?>
                            <?php echo $fecha_gastos; ?>
                        <?php else: ?>
                            <p class="text-center">No hay registros</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        Reporte generado el <?php echo date('d/m/Y H:i'); ?> - Sistema de Administración Haydée
    </div>

</body>
</html>