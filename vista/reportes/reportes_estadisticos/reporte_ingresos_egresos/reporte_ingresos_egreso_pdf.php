<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reporte por departamento</title>
    <style>
        h2 {
            text-align: center;
            color: #FFF;
            margin-top: 20px;   
        }
        #contenedor{
            padding: 1.5rem;
            display: flex;
            align-items: center;
            flex-direction: column;
        }
    </style>
</head>
<body>
<div style="background-color: #4723D9; padding: 5px;border-radius:10px 10px 0 0;height: 200px">
    <h2>REPORTE ESTADISTICO DE INGRESOS Y EGRESOS</h2>
</div>

<div class="card p-4 flex-row justify-content-between align-items-end" id="contenedor">
    <h3>Gráfico de Barras</h3>
    <div class="d-flex justify-content-center">
        <div style="position: relative; height:500px; width:600px">
            <img src="<?php echo($barra) ?>" width="100%" height="100%" alt="Imagen de Grafico">
        </div>
    </div>

    
</div>

</body>
</html>