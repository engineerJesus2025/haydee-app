<div class="row">
	<div class="col-5">
		<select class="form-select" id="mes_select_asignar">
			<option hidden="" selected="" value="">Seleccione el mes para asignar</option>
		</select>
	</div>
</div>
<table class="table caption-top" id="tabla_mensualidad_asignar">
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
<div class="row justify-content-center">
	<button id="boton_formulario" class="btn btn-primary col-3" op="Registrar">Guardar Mensualidad</button>
</div>
