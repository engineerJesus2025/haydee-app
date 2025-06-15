<h3 class="text-center">Gráfico de Ingresos y Egresos</h3>
<div class="chart-container mx-auto" style="position: relative; height:300px; width:550px">
<canvas id="canva"></canvas>
</div>
<div class="mx-auto text-center">
	<form method="POST" action="?pagina=reportes_controlador.php&accion=generar_reporte_ingresos_egresos">
		<input type="hidden" name="barra" id="barra" value="">
		<button class="btn btn-success" id="boton_generar">Generar Reporte</button>
	</form>
</div>