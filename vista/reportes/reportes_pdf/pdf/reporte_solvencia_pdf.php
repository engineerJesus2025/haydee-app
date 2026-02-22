<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solvencia de Condominio</title>
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
            padding: 5px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-justify {
            text-align: justify;
        }
        .bold {
            font-weight: bold;
        }
        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 5px 0;
        }
        .signature-line {
            width: 200px;
            margin: 0 auto;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
    <table>
        <!-- Título -->
        <tr>
            <td class="text-center" style="padding: 30px 0 20px;">
                <h2>SOLVENCIA DE CONDOMINIO</h2>
            </td>
        </tr>
        <!-- Contenido principal -->
        <tr>
            <td class="text-justify">
                <p>
                    <span class="bold">_____ </span>
                    Sirva la presente para dejar constancia que 
                    <?php echo ($registro_propietario["sexo"] == "Masculino") ? "el" : "la"; ?> 
                    ciudadan<?php echo ($registro_propietario["sexo"] == "Masculino") ? "o" : "a"; ?> 
                    <?php echo $registro_propietario["nombre"] . " " . $registro_propietario["apellido"]; ?> 
                    titular de la C.I. V-<?php echo $registro_propietario["cedula"]; ?> 
                    reside en el apartamento Nº <?php echo $registro_propietario["apartamento"]; ?>, 
                    piso <?php echo explode("-", $registro_propietario["apartamento"])[0]; ?> 
                    Edificio Haydee ubicado en la carrera 22 entre las calles 13 y 14 de la 
                    parroquia Catedral municipio Iribarren Barquisimeto, Estado Lara.
                </p>
            </td>
        </tr>
        <!-- Estado de solvencia -->
        <tr>
            <td style="padding-top: 20px;">
                <p>
                    Se encuentra solvente en el pago del condominio hasta el dia 1 del mes 
                    <?php echo $meses[$mes_fin - 1]; ?> del año <?php echo $anio_fin; ?>.
                </p>
            </td>
        </tr>
        <!-- Fecha de expedición -->
        <tr>
            <td>
                <p>
                    Constancia que se expide a los <?php echo date("d") . ((date("d") != 1) ? " dias" : " dia"); ?> 
                    del mes de <?php echo $meses[date('n') - 1]; ?> del año <?php echo date("Y"); ?>.
                </p>
            </td>
        </tr>
        <!-- Firma -->
        <tr>
            <td class="text-center" style="padding-top: 40px;">
                <p>Por la Junta de Condominio</p>
                <div style="width: 200px; margin: 0 auto; border-top: 1px solid #000; margin-top: 30px;"></div>
                <p style="margin: 5px 0;">Presidente</p>
                <p style="margin: 5px 0;">CI 3394818</p>
            </td>
        </tr>
    </table>
</body>
</html>