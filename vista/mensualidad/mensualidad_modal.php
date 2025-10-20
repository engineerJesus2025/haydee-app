<div class="row">
	<div class="col-sm-5">
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
<div class="row">
	<div class="col-md-6">
        <label for="porcentaje_demora">Porcentaje por demora de pago:</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
            <input type="number" class="form-control porcentaje_demora" name="porcentaje_demora" id="porcentaje_demora" placeholder="Porcentaje" aria-label="porcentaje_demora" aria-describedby="basic-addon1"  maxlength="3" value="10">
            <span class="w-100 invalid-feedback"></span>
        </div>
	</div>
	<div class="col-md-6">
        <label for="dia_limite">Día limite de pago de mensualidad:</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
            <input type="number" class="form-control dia_limite" name="dia_limite" id="dia_limite" placeholder="Porcentaje" aria-label="dia_limite" aria-describedby="basic-addon1"  maxlength="3" value="15">
            <span class="w-100 invalid-feedback"></span>
        </div>
	</div>
</div>
<div class="row justify-content-center mt-4">
	<button id="boton_formulario" class="btn btn-primary col-lg-3 col-sm-6 col-8" op="Registrar">Guardar</button>
</div>
