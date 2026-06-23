<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Residencia</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 2cm;
        }
        h5 {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .contenedor {
            width: 80%;
            margin: 0 auto;
        }
        .texto-justificado {
            text-align: justify;
        }
        .centrado {
            text-align: center;
        }
        .izquierda {
            text-align: left;
        }
        .margen-superior {
            margin-top: 30px;
        }
        .margen-inferior {
            margin-bottom: 30px;
        }
        .linea {
            width: 30%;
            margin: 10px auto;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
    <h5>CONSTANCIA DE RESIDENCIA</h5>

    <div class="contenedor texto-justificado">
        <p>
            <b>_____</b> Por medio de la presente se hace constar, que 
            <?php echo ($registro_propietario["sexo"] == "Masculino") ? "el" : "la"; ?> 
            ciudadan<?php echo ($registro_propietario["sexo"] == "Masculino") ? "o" : "a"; ?> 
            <?php echo $registro_propietario["nombre"] . " " . $registro_propietario["apellido"]; ?> 
            portador<?php echo ($registro_propietario["sexo"] == "Masculino") ? "" : "a"; ?> 
            de la cédula de identidad número 
            <?php
                // Formato simple: asumiendo que la cédula tiene formato como "V12345678"
                $cedula = $registro_propietario["cedula"];
                if (preg_match('/^([VE])(\d+)$/', $cedula, $matches)) {
                    echo $matches[1] . '-' . $matches[2];
                } else {
                    echo $cedula; // fallback
                }
            ?> 
            vive en el apartamento Nº <?php echo $registro_propietario["apartamento"]; ?>, 
            piso <?php echo explode("-", $registro_propietario["apartamento"])[0]; ?>, 
            del edificio Haydee ubicado en la carrera 22 entre calles 13 y 14, 
            municipio Iribarren parroquia Catedral del estado Lara, Barquisimeto.
        </p>
    </div>

    <div class="contenedor izquierda margen-superior">
        <p>Constancia que se expide a los 
            <?php echo date("d") . (date("d") != 1 ? " días" : " día"); ?> 
            del mes de 
            <?php
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                echo $meses[date('n') - 1];
            ?> 
            del año <?php echo date("Y"); ?>.
        </p>
    </div>

    <div class="contenedor izquierda margen-inferior">
        <p>Sin más a lo que refiere.</p>
    </div>

    <div class="centrado">
        <p>Atentamente</p>
        <div class="linea"></div>
        <p>Presidente</p>
        <p>CI 3394818</p>
        <p>Junta de Condominio Edificio Haydee</p>
        <p>Teléfono 04120567747</p>
    </div>
</body>
</html>