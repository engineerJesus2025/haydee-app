<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reporte por departamento</title>
    <!-- <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.min.css"> -->
    <style>
        *, ::after, ::before {
          box-sizing: border-box;
        }
        body {
          font-family: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue","Noto Sans","Liberation Sans",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
          font-size: 1rem;
          font-weight: 400;
          line-height: 1.5;
          color: #212529;          
        }
        h2 {
            text-align: center;
            margin-top: 20px;   
        }
        #contenedor{
            padding: 1.5rem;
            display: flex;
            align-items: center;
            flex-direction: column;
        }
        .text-center {
          text-align: center !important;
        }
        .my-5 {
          margin-top: 3rem !important;
          margin-bottom: 3rem !important;
        }
        .container, .container-fluid, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
          --bs-gutter-x: 1.5rem;
          --bs-gutter-y: 0;
          width: 100%;
          padding-right: calc(var(--bs-gutter-x) * .5);
          padding-left: calc(var(--bs-gutter-x) * .5);
          margin-right: auto;
          margin-left: auto;
        }
        .mt-4 {
          margin-top: 1.5rem !important;
        }

        .justify-content-center {
          justify-content: center !important;
        }
        .row {
          /*display: flex;*/
          flex-wrap: wrap;
          margin-top: calc(-1 * var(--bs-gutter-y));
          margin-right: calc(-.5 * var(--bs-gutter-x));
          margin-left: calc(-.5 * var(--bs-gutter-x));
        }
        .card {
          
          position: relative;
          display: flex;
          flex-direction: column;
          min-width: 0;  
          color: #212529;
          word-wrap: break-word;
          background-color: #fff;
          background-clip: border-box;
          border: 1px solid rgba(0, 0, 0, 0.175);
          border-radius: 0.375rem;
        }

        .col-8 {
          flex: 0 0 auto;
          width: 66.66666667%;
        }

        .row > * {
          flex-shrink: 0;
          /*width: 100%;*/
          max-width: 100%;
          padding-right: calc(1.5rem * .5);
          padding-left: calc(1.5rem * .5);
          margin-top: 0;
        }

        .card-header:first-child {
          border-radius: calc(0.375rem - (1px)) calc(0.375rem - (1px)) 0 0;
        }

        .card-header {
          padding: 0.5rem 1rem;
          margin-bottom: 0;
          background-color: rgba(33,37,41, 0.03);
          border-bottom: 1px solid rgba(0,0,0,0.175);
        }

        .card-body {
          flex: 1 1 auto;
          padding: 1rem 1rem;
        }

        .justify-content-around {
          justify-content: space-around !important;
        }

        .col-5 {
          flex: 0 0 auto;
          width: 41.66666667%;
        }

        @media (min-width: 1200px) {
          .h2, h2 {
            font-size: 2rem;
          }
        }

        @media (min-width: 1200px) {
          .h4, h4 {
            font-size: 1.5rem;
          }
        }

        .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
          margin-top: 0;
          margin-bottom: .5rem;
          font-weight: 500;
          line-height: 1.2;
        }

        @media (min-width: 1200px) {
          .h3, h3 {
            font-size: 1.75rem;
          }
        }
        .h3, h3 {
          font-size: calc(1.3rem + .6vw);
        }
    </style>
</head>
<body>
<div style="padding: 5px;border-radius:10px 10px 0 0;height: 100px">
    <h2>REPORTE ESTADÍSTICO DE INGRESOS Y EGRESOS</h2>
</div>
<h4><?php echo $fecha; ?></h4>
<?php if ($selecion == "solo_grafico" || $selecion == "grafico_texto") { ?>
  <div class="text-center" id="contenedor">
      <h3>Gráfico de Barras</h3>
      <div >
          <div style="position: relative; height:500px;">
              <img src="<?php echo($barra) ?>" width="100%" height="100%" alt="Imagen de Grafico">
          </div>
      </div>    
  </div>
<?php } ?>
<?php if ($selecion == "solo_texto" || $selecion == "grafico_texto") { ?>
  <div class="container my-5" id="contenedor_estadistica">
      <br><br><br><br><br>
     <h3 class="text-center">Datos de Estadísticas:</h3>

      <div class="row justify-content-center mt-4" style="margin-left: 25vw; margin-bottom: 5vh">
          <div class="col-8 card text-center">
            <div class="card-header">
              Resumen de Estadísticas:
            </div>
            <div class="card-body">
              <p id="total_pagos">Total de Pagos realizados: <?php echo $total_pagos ?></p>
              <p id="total_gastos">Total de Gastos Realizados: <?php echo $total_gastos ?></p>      
            </div>
          </div>
      </div>
      <br><br>
      <hr>
      <br><br>
      <div class="row justify-content-around mt-4">
          <div class="col-5 card text-center" style="display: inline-block; margin-left: 5vw; margin-top: 5vh; margin-bottom: 5vh">
            <div class="card-header">
              Metodos de pago (gastos):
            </div>
            <div class="card-body">
              <p id="gastos_efectivo">Gastos por Efectivo: <?php echo $gastos_efectivo ?></p>
              <p id="gastos_transferencia">Gastos por Transferencia: <?php echo $gastos_transferencia ?></p>
              <p id="gastos_pago_movil">Gastos por Pago Movil: <?php echo $gastos_pago_movil ?></p>
            </div>
          </div>
          <div class="col-5 card text-center" style="display: inline-block; margin-left: 5vw; margin-top: 5vh; margin-bottom: 5vh">
            <div class="card-header">
              Metodos de pago (Pagos):
            </div>
            <div class="card-body">
              <p id="pagos_efectivo">Pagos por Efectivo: <?php echo $pagos_efectivo ?></p>
              <p id="pagos_transferencia">Pagos por Transferencia: <?php echo $pagos_transferencia ?></p>
              <p id="pagos_pago_movil">Pagos por Pago Movil: <?php echo $pagos_pago_movil ?></p>
            </div>
          </div>
      </div>
      <hr>
      <br><br>
      <div class="row justify-content-around mt-4">
          <div class="col-5 card text-center"style="display: inline-block; margin-left: 5vw;">
            <div class="card-header">
              Marcas de Tiempo (Pagos):
            </div>
            <div class="card-body" id="fecha_pagos">
              <?php echo $fecha_pagos ?>
            </div>
          </div>
          <div class="col-5 card text-center"style="display: inline-block; margin-left: 5vw;">
            <div class="card-header">
              Marcas de Tiempo (Gastos):
            </div>
            <div class="card-body" id="fecha_gastos">
              <?php echo $fecha_gastos ?>
            </div>
          </div>
      </div>
  </div>
<?php } ?>
</body>
</html>