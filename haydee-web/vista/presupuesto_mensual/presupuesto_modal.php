<div class="modal fade" id="modal_presupuesto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-journal-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Presupuesto Mensual</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_presupuesto" name="form_presupuesto">
                    <div class="row m-3">
                        <div class="col-lg-5">
                            <label for="fecha">Fecha del presupuesto <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar"></i></span>
                                <select class=" rounded-end form-select" aria-label="Default select example" name="fecha" id="fecha" form="form_presupuesto">
                                    <option selected hidden value="">Seleccione la fecha del presupuesto</option>                    
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label for="cuota_reserva">Cuota de Reserva:</label>
                            <div class="input-group mb-3">
                                <input type="number" name="cuota_reserva" id="cuota_reserva" class="form-control" maxlength="15" minlength="0" value="0" title="Monto de la cuota de reserva en bolivares" monto="bs" placeholder="Ingrese un monto">
                                <span class="w-100 invalid-feedback"></span>
                                <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota">Bs.</span>
                            </div>
                        </div>
                        <div class="col-lg-1 col-2 d-flex justify-content-center align-items-end mt-2">
                            <button class="btn btn-soft-info boton_intercambio_cuota" title="Presione para cambiar el tipo de moneda" tabindex="-1">
                                <span><i class="bi bi-arrow-left-right"></i></span>
                            </button>            
                        </div>
                        <div class="col-lg-3 col-10 text-center">
                            <label for="cuota_reserva_cambio"></label>
                            <div class="input-group mb-3">
                                <input type="text" name="cuota_reserva_cambio" id="cuota_reserva_cambio" minlength="0" value="0" class="form-control" maxlength="15" disabled title="Monto de la cuota de reserva en dolares" convertido>                
                                <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota_cambio">$</span>
                            </div>
                        </div>

                    </div>
                    <h5 class="text-center mb-0">Asignación de presupuestos:</h5>

                    <div class="row my-3 justify-content-center" id="contenedor_presupuestos">
                    </div>

                    <div class="row m-3">
                        <div class="col-md-12">
                            <label for="observacion">Observación</label>
                            <div class="input-group mb-3">
                                <span class=" input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                <input type="text" class=" rounded-end form-control" name="observacion" id="observacion" placeholder="Puede agregar una observación o comentario del presupuesto de este mes" aria-label="observacion" aria-describedby="basic-addon1" minlength="0" maxlength="100">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Presupuesto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>