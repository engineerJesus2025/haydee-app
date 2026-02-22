<!DOCTYPE html>
<html lang="es">
<head>
    <title>Reporte de ingresos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 8px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .card {
            border: 1px solid #000;
            padding: 10px;
            margin: 10px 0;
        }
        .card-header {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }
        .card-body p {
            margin: 5px 0;
        }
        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 20px 0;
        }
        h2, h3, h4 {
            margin: 0 0 10px;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Título principal -->
    <table>
        <tr>
            <td class="text-center" style="padding: 20px 0;">
                <h2>REPORTE ESTADÍSTICO DE INGRESOS Y EGRESOS</h2>
                <h4><?php echo $fecha; ?></h4>
            </td>
        </tr>
    </table>

    <!-- Sección solo gráfico o gráfico+texto -->
    <?php if ($selecion == "solo_grafico" || $selecion == "grafico_texto") { ?>
    <table style="margin-top: 20px;">
        <tr>
            <td class="text-center">
                <h3>Gráfico de Barras</h3>
                <div style="border: 1px solid #ccc; padding: 10px;">
                    <img src="<?php echo $barra; ?>" width="100%" height="100%" alt="Imagen de Grafico">
                </div>
            </td>
        </tr>
    </table>
    <?php } ?>

    <!-- Sección solo texto o gráfico+texto -->
    <?php if ($selecion == "solo_texto" || $selecion == "grafico_texto") { ?>
    <table style="margin-top: 30px;">
        <!-- Resumen de estadísticas -->
        <tr>
            <td class="text-center">
                <h3>Datos de Estadísticas:</h3>
            </td>
        </tr>
        <tr>
            <td>
                <table style="border: 1px solid #000; width: 60%; margin: 0 auto;">
                    <tr>
                        <td class="card-header text-center">Resumen de Estadísticas:</td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            <p>Total de Pagos realizados: <?php echo $total_pagos; ?></p>
                            <p>Total de Gastos Realizados: <?php echo $total_gastos; ?></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td><hr></td></tr>

        <!-- Métodos de pago -->
        <tr>
            <td>
                <table width="100%">
                    <tr>
                        <td width="50%" style="padding-right: 10px;">
                            <table style="border: 1px solid #000;">
                                <tr><td class="card-header text-center">Métodos de pago (gastos):</td></tr>
                                <tr>
                                    <td class="text-center">
                                        <p>Gastos por Efectivo: <?php echo $gastos_efectivo; ?></p>
                                        <p>Gastos por Transferencia: <?php echo $gastos_transferencia; ?></p>
                                        <p>Gastos por Pago Móvil: <?php echo $gastos_pago_movil; ?></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="50%" style="padding-left: 10px;">
                            <table style="border: 1px solid #000;">
                                <tr><td class="card-header text-center">Métodos de pago (Pagos):</td></tr>
                                <tr>
                                    <td class="text-center">
                                        <p>Pagos por Efectivo: <?php echo $pagos_efectivo; ?></p>
                                        <p>Pagos por Transferencia: <?php echo $pagos_transferencia; ?></p>
                                        <p>Pagos por Pago Móvil: <?php echo $pagos_pago_movil; ?></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td><hr></td></tr>

        <!-- Marcas de tiempo -->
        <tr>
            <td>
                <table width="100%">
                    <tr>
                        <td width="50%" style="padding-right: 10px;">
                            <table style="border: 1px solid #000;">
                                <tr><td class="card-header text-center">Marcas de Tiempo (Pagos):</td></tr>
                                <tr>
                                    <td class="text-center" id="fecha_pagos">
                                        <?php echo $fecha_pagos; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="50%" style="padding-left: 10px;">
                            <table style="border: 1px solid #000;">
                                <tr><td class="card-header text-center">Marcas de Tiempo (Gastos):</td></tr>
                                <tr>
                                    <td class="text-center" id="fecha_gastos">
                                        <?php echo $fecha_gastos; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <?php } ?>
</body>
</html>