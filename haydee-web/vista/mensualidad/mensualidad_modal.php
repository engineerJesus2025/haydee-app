<div class="modal fade" id="modal_mensualidad" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
            	<h1 class="modal-title fs-5">
                    <i class="bi bi-calendar-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Mensualidad</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
            	<div class="row mb-2">
					<div class="col-sm-5">
						<label for="mes_select_asignar">Mes seleccionado: <span class="text-danger">*</span></label>
						<select class="form-select" id="mes_select_asignar">
							<option hidden selected="" value="">Seleccione el mes para asignar</option>
						</select>
						<span class="w-100 invalid-feedback"></span>
					</div>
				</div>

				<div class="table-responsive">
				    <table class="table table-hover align-middle m-0 tabla-asignacion-mensualidad caption-top" id="tabla_mensualidad_asignar">
				    	<caption>Tabla de Mensualidades</caption>
				        <thead>
				            <tr class="text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.8px;">
				                <th class="ps-3 py-3" style="width: 15%;"><i class="bi bi-door-closed-fill text-primary me-2"></i>APTO</th>
				                <th class="text-center py-3" style="width: 25%;"><i class="bi bi-cash-stack me-2"></i>Monto Base</th>
				                <th class="text-center py-3" style="width: 30%;"><i class="bi bi-gear-fill text-warning me-2"></i>Exoneración</th>
				                <th class="text-end pe-3 py-3" style="width: 30%;"><i class="bi bi-wallet2 text-success me-2"></i>Total Neto</th>
				            </tr>
				        </thead>
				        <tbody class="border-top-0">
						    <?php foreach ($registros_apartamentos as $registro) { ?>
						        <tr id="<?php echo($registro["id_apartamento"]); ?>" data-participacion="<?php echo($registro["porcentaje_participacion"]) ?>" class="fila-mensualidad-asignar">
						            
						            <td class="ps-3 align-middle text-nowrap">
									    <div class="d-flex align-items-center gap-2" style="letter-spacing: 0.3rem;">
									        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 fw-bold font-monospace fs-5" title="Apartamento Numero <?php echo $registro["nro_apartamento"]; ?>" data-tooltip="true">
									            <?php echo $registro["nro_apartamento"]; ?>
									        </span>
									    </div>
									    <span class="text-muted mt-1 font-monospace" style="font-size: 0.9rem;" title="Porcentaje de Participación <?php echo number_format($registro["porcentaje_participacion"], 2); ?>%" data-tooltip="true">Part. <?php echo number_format($registro["porcentaje_participacion"], 2); ?>%</span>
									</td>
						            
						            <td class="text-center align-middle">
						                <span class="fw-semibold celda-monto-base">0.00</span>
						                <div class="text-muted mt-1" style="font-size: 0.9rem;">Ref: <span class="celda-monto-base-usd">0.00</span> $</div>
						            </td>
						            
						            <td class="px-4 py-2 pt-3 text-center align-middle">
						                <div class="d-flex flex-column align-items-center justify-content-center">
						                    <div class="input-group shadow-xs rounded flex-nowrap justify-content-center">
						                        <button type="button" class="btn btn-asistente border-end-0 px-2.5 d-inline-flex align-items-center justify-content-center" title="Presione aquí para abrir el Asistente de Exoneración" data-tooltip="true" onclick="abrirAsistenteExoneracion('<?php echo $registro['id_apartamento']; ?>', <?php echo $registro['porcentaje_participacion']; ?>)">
						                            <i class="bi bi-calculator"></i>
						                        </button>
						                        <input type="number" class="form-control text-center fw-bold input-descuento flex-grow-1" value="0.00" min="0" step="0.01" oninput="recalcularTotalFila(this.closest('tr'))" style="font-size: 0.9rem;" title="Puedes exonerar un porcentaje (OPCIONAL)" data-tooltip="true">
						                    </div>
						                    <div class="text-muted mt-2" style="font-size: 0.9rem;">Ref: <span class="celda-exoneracion-usd">0.00</span> $</div>
						                </div>
						            </td>
						            
						            <td class="text-end pe-3 align-middle">
						                <span class="fw-bold fs-5 text-primary celda-total-neto">0.00</span>
						                <span class="text-primary ms-1" style="font-size: 1.1rem;">Bs.</span>
						                <div class="text-primary mt-1 font-monospace" style="font-size: 0.9rem; opacity: 0.8;">Ref: <span class="celda-total-neto-usd">0.00</span> $</div>
						            </td>
						        </tr>
						    <?php } ?> 
						</tbody>
						<tfoot>
						    <tr class="align-middle fw-bold">
						        <td class="ps-3 py-3 text-uppercase" style="font-size: 1rem;">Total General:</td>			
						        
						        <td class="text-center">
						            <span id="foot_monto_base" class="fs-5">0.00</span>
						            <span class="ms-1" style="font-size: 1.1rem; font-weight: normal;">Bs.</span>
						            <div class="text-muted mt-1 " style="font-size: 0.9rem;">Ref: <span id="foot_monto_base_usd">0.00</span> $</div>
						        </td>
						        
						        <td class="text-center">
						            <span id="foot_total_exoneracion" class="text-warning fs-5">0.00</span>
						            <span class="text-warning  ms-1" style="font-size: 1.1rem;">Bs.</span>
						            <div class="text-muted mt-1 " style="font-size: 0.9rem;">Ref: <span id="foot_total_exoneracion_usd">0.00</span> $</div>
						        </td>
						        
						        <td class="text-end pe-3">
						            <span id="foot_total_neto" class="text-primary fs-5">0.00</span>
						            <span class="text-primary ms-1" style="font-size: 1.1rem;">Bs.</span>
						            <div class="text-primary mt-1 font-monospace" style="font-size: 0.9rem; opacity: 0.8;">Ref: <span id="foot_total_neto_usd">0.00</span> $</div>
						        </td>
						    </tr>
						</tfoot>
					    </table>
				</div>

				<div class="row mt-3">
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
            </div>
			<div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3 mb-2"data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Mensualidad</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>