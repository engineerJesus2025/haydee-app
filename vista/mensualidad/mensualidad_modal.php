<div class="row">
	<div class="col-5">
		<select class="form-select" id="mes_select_asignar">
			<option hidden="" selected="" value="">Seleccione el mes para asignar</option>
		</select>
	</div>
</div>
<div class="table-responsive">
	<table class="table caption-top table-striped table-hover" id="tabla_mensualidad_asignar">
		<caption>Tabla de Mensualidades</caption>
		<thead>
			<tr>
				<th>APTO</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($registos_apartamentos as $registro) { ?>
				<tr id="<?php echo($registro["id_apartamento"]); ?>" participacion="<?php echo($registro["porcentaje_participacion"]) ?>">
					<td><?php echo $registro["nro_apartamento"]; ?></td>
				</tr>
			<?php } ?> 
		</tbody>
		<tfoot>
			<tr>
				<td>Total:</td>			
			</tr>
		</tfoot>
	</table>
</div>
<div class="row justify-content-center mt-4">
	<button id="boton_formulario" class="btn btn-primary col-lg-3 col-sm-6 col-8" op="Registrar">Guardar Mensualidad</button>
</div>
