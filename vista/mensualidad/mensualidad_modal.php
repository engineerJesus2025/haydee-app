<div class="modal fade" id="modal_mensualidad" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Registrar mensualidad</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
            	<div class="row">
					<div class="col-sm-5">
						<label for="mes_select_asignar">Mes seleccionado: <spam class="text-danger">*</spam></label>
						<select class="border border-dark form-select" id="mes_select_asignar">
							<option hidden selected="" value="">Seleccione el mes para asignar</option>
						</select>
						<span class="w-100 invalid-feedback"></span>
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
							<?php foreach ($registros_apartamentos as $registro) { ?>
								<tr id="<?php echo($registro["id_apartamento"]); ?>" data-participacion="<?php echo($registro["porcentaje_participacion"]) ?>" data-gas="<?php echo($registro["gas"]) ?>">
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
				        <label for="porcentaje_demora">Porcentaje por demora de pago: <spam class="text-danger">*</spam></label>
				        <div class="input-group mb-3">
				            <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
				            <input type="number" class="border border-dark rounded-end form-control porcentaje_demora" name="porcentaje_demora" id="porcentaje_demora" placeholder="Porcentaje" aria-label="porcentaje_demora" aria-describedby="basic-addon1"  maxlength="3" value="10">
				            <span class="w-100 invalid-feedback"></span>
				        </div>
					</div>
					<div class="col-md-6">
				        <label for="dia_limite">Día limite de pago de mensualidad: <spam class="text-danger">*</spam></label>
				        <div class="input-group mb-3">
				            <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
				            <input type="number" class="border border-dark rounded-end form-control dia_limite" name="dia_limite" id="dia_limite" placeholder="Porcentaje" aria-label="dia_limite" aria-describedby="basic-addon1"  maxlength="3" value="15">
				            <span class="w-100 invalid-feedback"></span>
				        </div>
					</div>
				</div>
				<div class="row justify-content-center mt-4">
					<button id="boton_formulario" class="btn btn-primary col-lg-3 col-sm-6 col-8" op="Registrar">Guardar</button>
				</div>
            </div>
        </div>
    </div>
</div>