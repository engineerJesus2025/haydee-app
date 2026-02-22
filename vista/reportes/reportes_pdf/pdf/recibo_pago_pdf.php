<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo de Pago</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; font-size: 10pt; }
        .container { width: 100%; border: 1px solid #000; padding: 20px; box-sizing: border-box; }
        .header { margin-bottom: 20px; padding-left: 20px; }
        .header h2 { margin: 0; font-size: 18pt; color: #1e5a96; font-style: italic; font-weight: bold; }
        
        /* Utilidades para Dompdf */
        table.layout { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.layout td { vertical-align: top; padding: 2px; }
        
        .field-box { border: 1px solid #000; padding: 8px; display: block; margin-bottom: 5px; }
        .payment-details-box { border: 1px solid #000; padding: 15px; margin: 15px 0; }
        .line-item { margin-bottom: 8px; }
        
        .checkbox { border: 1px solid #000; width: 15px; height: 15px; display: inline-block; text-align: center; line-height: 15px; font-weight: bold;}
        .signature-area { margin-top: 80px; text-align: right; padding-right: 50px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Junta de Condominio</h2>
            <h2>Residencias Haydee</h2>
        </div>

        <table class="layout">
            <tr>
                <td width="50%">
                    <p style="margin:2px 0;">Carrera 22 entre calles 13 y 14</p>  
                    <p style="margin:2px 0;">Rif.: J-3057854-7</p>
                </td>
                <td width="50%" style="text-align: right;">
                    <div class="field-box">
                        <strong style="float: left;">Fecha:</strong> 
                        <?php echo htmlspecialchars(date('d/m/Y')); ?>
                    </div>
                    <div class="field-box">
                        <strong style="float: left;">Por Bs.:</strong> 
                        <?php echo htmlspecialchars(number_format($detalles_recibo['total'], 2, ',', '.') . " Bs."); ?>
                    </div>
                </td>
            </tr>
        </table>
        <hr style="border: 0; border-bottom: 1px solid #000; margin: 10px 0;">

        <div class="payment-details-box">
            <div class="line-item">
                <span class="bold">He (mos) recibido del Sr.(a) :</span> 
                <span style="float: right;"><?php echo htmlspecialchars($detalles_recibo['nombre'] ." " . $detalles_recibo['apellido']); ?></span>
            </div>
            <div class="line-item">
                <span class="bold">La Cantidad de:</span>
                <span style="float: right;"><?php echo htmlspecialchars(number_format($detalles_recibo['total'], 2, ',', '.') . " Bs."); ?></span>
            </div>
            <div class="line-item">
                <span class="bold">Por concepto de:</span> 
                <span style="float: right;">Cuota mes de <?php echo htmlspecialchars($meses_nombres[(int)$detalles_recibo['mes']-1]); ?>, <?php echo htmlspecialchars($detalles_recibo['anio']); ?></span>
            </div>
            <div class="line-item">
                <span class="bold">Correspondiente al apartamento #:</span>
                <span style="float: right;"><?php echo htmlspecialchars($detalles_recibo['nro_apartamento']); ?></span>
            </div>
        </div>

        <table class="layout">
            <tr>
                <td width="40%">
                    <div style="margin-bottom: 10px;">
                        <span class="checkbox"><?php echo ($detalles_recibo['count_transferencia'] != 0 || $detalles_recibo['count_pago_movil'] != 0) ? 'X' : '&nbsp;'; ?></span>
                        Transferencia <?php echo ($detalles_recibo['count_transferencia'] != 0 || $detalles_recibo['count_pago_movil'] != 0)? 'X' . ($detalles_recibo['count_transferencia'] + $detalles_recibo['count_pago_movil']) : ''; ?>
                    </div>
                    <div>
                        <span class="checkbox"><?php echo ($detalles_recibo['count_efectivo'] != 0) ? 'X' : '&nbsp;'; ?></span>
                        $ efectivo <?php echo ($detalles_recibo['count_efectivo'] != 0)? 'X' . $detalles_recibo['count_efectivo'] : ''; ?>
                    </div>
                </td>
                <td width="60%" style="text-align: right;">
                    <div class="field-box">
                        <strong style="float: left;">Banco:</strong> <?php echo htmlspecialchars($detalles_recibo['bancos'] ? $detalles_recibo['bancos'] : 'N/A'); ?>
                    </div>
                    <div class="field-box">
                        <strong style="float: left;">Referencia #:</strong> <?php echo htmlspecialchars($detalles_recibo['referencias'] ? $detalles_recibo['referencias'] : 'N/A'); ?>
                    </div>
                </td>
            </tr>
        </table>

        <div class="signature-area">
            <p>_______________________________</p>
            <p style="margin: 2px 0;">Por la Junta de Condominio</p>
            <p style="margin: 2px 0;">Hilda de Guarecuco</p>
        </div>
    </div>
</body>
</html>