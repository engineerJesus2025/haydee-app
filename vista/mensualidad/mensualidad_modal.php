<div class="modal fade" id="modal_mensualidad" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
            	<h1 class="modal-title fs-5">
                    <i class="bi bi-calendar-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Mensualidad</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
            	<div class="row">
					<div class="col-sm-5">
						<label for="mes_select_asignar">Mes seleccionado: <span class="text-danger">*</span></label>
						<select class="form-select" id="mes_select_asignar">
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
				        <label for="porcentaje_demora">Porcentaje por demora de pago: <span class="text-danger">*</span></label>
				        <div class="input-group mb-3">
				            <span class="input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
				            <input type="number" class="rounded-end form-control porcentaje_demora" name="porcentaje_demora" id="porcentaje_demora" placeholder="Porcentaje" aria-label="porcentaje_demora" aria-describedby="basic-addon1"  maxlength="3" value="10">
				            <span class="w-100 invalid-feedback"></span>
				        </div>
					</div>
					<div class="col-md-6">
				        <label for="dia_limite">Día limite de pago de mensualidad: <span class="text-danger">*</span></label>
				        <div class="input-group mb-3">
				            <span class="input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
				            <input type="number" class="rounded-end form-control dia_limite" name="dia_limite" id="dia_limite" placeholder="Porcentaje" aria-label="dia_limite" aria-describedby="basic-addon1"  maxlength="3" value="15">
				            <span class="w-100 invalid-feedback"></span>
				        </div>
					</div>
				</div>
				<div class="row mt-5 mb-2">
                        <div class="col-md-12 text-end"> 
                            <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </button>
                            <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario">
                                <i class="bi bi-check2-circle me-2"></i>
                                <span id="texto_boton_formulario">Guardar Mensualidad</span>
                            </button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>