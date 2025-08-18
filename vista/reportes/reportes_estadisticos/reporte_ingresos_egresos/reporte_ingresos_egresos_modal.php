<h3 class="text-center" id="titulo_grafico">Gráfico de Ingresos y Egresos</h3>
<p id="fecha_grafico"></p>
<div class="chart-container mx-auto mb-4 col-md-10">
	<canvas  id="canva"></canvas>
</div>
<div class="container my-5" id="contenedor_estadistica">
   <h3 class="text-center">Datos de Estadísticas:</h3>
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8 card text-center">
          <div class="card-header">
            Resumen de Estadísticas:
          </div>
          <div class="card-body">
            <p id="total_pagos">Total de Pagos realizados: </p>
            <p id="total_gastos">Total de Gastos Realizados: </p>      
          </div>
        </div>
    </div>
    <hr>
    <div class="row justify-content-around mt-4">
        <div class="col-lg-5 mt-4 mt-lg-0 card text-center">
          <div class="card-header">
            Metodos de pago (gastos):
          </div>
          <div class="card-body">
            <p id="gastos_efectivo">Gastos por Efectivo: </p>
            <p id="gastos_transferencia">Gastos por Transferencia: </p>
            <p id="gastos_pago_movil">Gastos por Pago Movil: </p>
          </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0 card text-center">
          <div class="card-header">
            Metodos de pago (Pagos):
          </div>
          <div class="card-body">
            <p id="pagos_efectivo">Pagos por Efectivo: </p>
            <p id="pagos_transferencia">Pagos por Transferencia: </p>
            <p id="pagos_pago_movil">Pagos por Pago Movil: </p>
          </div>
        </div>
    </div>
    <hr>
    <div class="row justify-content-around mt-4">
        <div class="col-lg-5 mt-4 mt-lg-0 card text-center">
          <div class="card-header">
            Marcas de Tiempo (Pagos):
          </div>
          <div class="card-body" id="fecha_pagos">
          </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0 card text-center">
          <div class="card-header">
            Marcas de Tiempo (Gastos):
          </div>
          <div class="card-body" id="fecha_gastos">
          </div>
        </div>
    </div>
</div>
<div class="mx-auto text-center">
	<form method="POST" action="?pagina=reportes_controlador.php&accion=generar_reporte_ingresos_egresos">
		<input type="hidden" name="fecha_grafico_input" id="fecha_grafico_input">
		<input type="hidden" name="barra" id="barra" value="">

    <input type="hidden" name="total_pagos_input" id="total_pagos_input">
    <input type="hidden" name="total_gastos_input" id="total_gastos_input">
    <input type="hidden" name="gastos_efectivo_input" id="gastos_efectivo_input">
    <input type="hidden" name="gastos_transferencia_input" id="gastos_transferencia_input">
    <input type="hidden" name="gastos_pago_movil_input" id="gastos_pago_movil_input">
    <input type="hidden" name="pagos_efectivo_input" id="pagos_efectivo_input">
    <input type="hidden" name="pagos_transferencia_input" id="pagos_transferencia_input">
    <input type="hidden" name="pagos_pago_movil_input" id="pagos_pago_movil_input">
    <input type="hidden" name="fecha_pagos_input" id="fecha_pagos_input">
    <input type="hidden" name="fecha_gastos_input" id="fecha_gastos_input">

		<button class="btn btn-success" id="boton_generar">Generar Reporte</button>
	</form>
</div>
