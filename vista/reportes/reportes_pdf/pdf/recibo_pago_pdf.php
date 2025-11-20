<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            font-size: 10pt;
        }
        .container {
            width: 100%;
            border: 1px solid #000;
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            text-align: left; /* Cambiado a izquierda */
            margin-bottom: 20px;
            padding-left: 4rem;
        }
        .header h2 {
            margin: 0;
            font-size: 18pt; /* Tamaño aumentado */
            color: #1e5a96; /* Color azul */
            font-style: italic;
            font-weight: 600;

        }
        .header h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: normal;
            color: #1e5a96; /* Color azul */
            font-style: italic;
            font-weight: 600;

        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-left {
          display: flex;
          flex-direction: column;
          justify-content: space-evenly;
        }
        .info-right {
            text-align: right;
        }
        .info-junta p {
            margin: 2px 0;
            font-size: 9pt;
        }
        .payment-details-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 15px 0;
        }
        .line-item {
          margin-bottom: 5px;
          display: flex;
          justify-content: space-between;
        }
        .line-item strong {
            display: inline-block;
        }
        .item-right{
          min-width: 24rem;
        }
        .field-box {
            border: 1px solid #000;
            padding: 10px 5px;
            display: inline-block;
            min-width: 24rem;
            text-align: left;
        }
        .field-box.small {
            /*min-width: 80px;*/
        }
        .field-box.large {
            min-width: 250px;
        }
        .payment-method-section {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .payment-method-left {
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
        }
        .payment-method-right {
            text-align: right;
        }
        .payment-method-item {
          margin-bottom: 5px;
        }
        .checkbox-container {
            margin-bottom: 10px;
        }
        .checkbox {
            border: 1px solid #000;
            width: 1.5rem;
            height: 1.5rem;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
            text-align: center;
            align-content: center;
        }
        .signature-area {
            text-align: right; /* Cambiado a derecha */
            margin-top: 10rem;
            padding-right: 5rem;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto; /* Alineado a la derecha */
            margin-right: 0;
            margin-bottom: 5px;
        }
        .signature-text {
            font-size: 9pt;
        }
        .line-separator {
            border-bottom: 1px solid #000;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .underline {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Junta de Condominio</h2>
            <h2>Residencias Haydee</h2>
        </div>

        <div class="info-section">
            <div class="info-left">                
              <p>Carrera 22 entre calles 13 y 14</p>  
              <p>Rif.: J-3057854-7</p>
            </div>
            <div class="info-right">
                <!-- <div class="line-item">
                    <span class="field-box small">
                      <strong class="text-left">Recibo #:</strong>
                      
                    </span>
                </div> -->
                <div class="line-item">
                    <span class="field-box">
                      <strong class="text-left">Fecha:</strong>

                      <?php echo htmlspecialchars(date('d/m/Y')); ?>
                    </span>
                </div>
                <div class="line-item">
                    <span class="field-box">
                      <strong class="text-left">Por .:</strong>
                      <?php echo htmlspecialchars($detalles_recibo['total'] . " Bs."); ?>
                      </span>
                </div>
            </div>
        </div>
        <div class="line-separator"></div>

        <div class="payment-details-box">
            <div class="line-item">
                <span class="bold">He (mos) recibido del Sr.(a) :</span>
                <span class="item-right">
                    <?php echo htmlspecialchars($detalles_recibo['nombre'] ." " . $detalles_recibo['apellido']); ?>
                </span>
              </span>
            </div>
            <div class="line-item">
                <span class="bold">La Cantidad de:</span>
                <span class="item-right">
                    <?php echo htmlspecialchars($detalles_recibo['total'] . " Bs."); ?>
                </span>
            </div>
            <div class="line-item">
                <span class="bold">Por concepto de:</span> 
                <span class="item-right">
                    Cuota mes de <?php echo htmlspecialchars($meses_nombres[$detalles_recibo['mes']-1]); ?>, <?php echo htmlspecialchars($detalles_recibo['anio']); ?>
                </span>
            </div>
            <div class="line-item">
                <span class="bold">Correspondiente al apartamento #:</span>
                <span class="item-right">
                <?php echo htmlspecialchars($detalles_recibo['nro_apartamento']); ?>
                </span>
            </div>
            <div class="line-item">
                <span class="bold">Cancelado el día:</span>
                <span class="item-right">
                <?php echo htmlspecialchars($fecha_pago->format('d/m/Y')); ?>
                </span>
            </div>
        </div>

        <div class="payment-method-section">
            <div class="payment-method-left">
                <div class="checkbox-container">
                    <div class="checkbox">
                        <span class="bold">
                            <?php echo ($detalles_recibo['count_transferencia'] != 0 || $detalles_recibo['count_pago_movil'] != 0)?'X':''; ?>
                        </span>
                    </div> 
                    Transferencia <?php echo ($detalles_recibo['count_transferencia'] != 0 || $detalles_recibo['count_pago_movil'] != 0)?'X' . ($detalles_recibo['count_transferencia'] + $detalles_recibo['count_pago_movil']):''; ?>
                </div>
                <div class="checkbox-container">
                    <div class="checkbox">
                        <span class="bold">
                            <?php echo ($detalles_recibo['count_efectivo'] != 0)?'X':''; ?>
                        </span>
                    </div> 
                    $ efectivo <?php echo ($detalles_recibo['count_efectivo'] != 0)?'X' . $detalles_recibo['count_efectivo']:''; ?>
                </div>
            </div>
            <div class="payment-method-right">
                <div class="payment-method-item">
                    <span class="field-box">
                      <strong class="text-left">Banco:</strong>
                      <?php echo htmlspecialchars($detalles_recibo['bancos']); ?>
                    </span>
                </div>
                <div class="payment-method-item">
                    <span class="field-box">
                      <strong class="text-left">Referencia / Serial #:</strong>
                      <?php echo htmlspecialchars($detalles_recibo['referencias']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="signature-area">
            <p class="signature-text">Por la Junta de Condominio</p>
            <p class="signature-text">Hilda de Guarecuco</p>
        </div>
    </div>

</body>
</html>