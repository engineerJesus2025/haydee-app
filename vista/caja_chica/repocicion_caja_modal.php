<div class="modal fade" id="modal_reponer_caja" tabindex="-1" aria-labelledby="titulo_modal_reponer_caja" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-arrow-repeat" id="icono_titulo_modal_repocicion"></i>
                    <span class="ms-2" id="titulo_modal_reponer_caja">Reponer Caja</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form class="m-3" id="form_registro_gasto">
                    <div class="row">
                        <div class="col-lg-5">
                            <label for="monto_reponer">Monto del gasto: <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <input type="number" name="monto_reponer" id="monto_reponer" class="form-control" maxlength="15" minlength="0" title="Monto del gasto en bolivares" monto="bs" placeholder="Monto">
                                <span class="rounded-end input-group-text icono_moneda" id="spam_icono_moneda_cuota">Bs.</span>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-2 d-flex justify-content-center align-items-center">
                            <button class="btn btn-soft-info boton_intercambio_monto" id="boton_intercambio_monto_reponer" title="Presione para cambiar el tipo de moneda">
                                <span><i class="bi bi-arrow-left-right"></i></span>
                            </button>            
                        </div>
                        <div class="col-lg-5 col-10 text-center">
                            <label for="monto_cambio_reponer"></label>
                            <div class="input-group mb-3">
                                <input type="text" name="monto_cambio_reponer" id="monto_cambio_reponer" minlength="0" value="0" class="form-control" maxlength="15" disabled title="Monto del gasto en dolares">
                                <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota_cambio">$</span>
                            </div>
                        </div>
                        <div class="col-12">
                           <p class="text-muted">Fondos en caja actualmente: <b id="fondos_caja_restantes"></b></p>    
                        </div>
                        <div class="col-12">
                           <p class="text-muted">Total del Fondos Gastados: <b id="fondos_gastados"></b></p>    
                        </div>
                    </div>
                    <div class="row mt-5 mb-2">
                        <div class="col-md-12 text-end"> 
                           <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                              <i class="bi bi-x-circle me-2"></i> Cancelar
                           </button>
                           <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario">
                               <i class="bi bi-check2-circle me-2"></i>
                               <span id="texto_boton_formulario">Guardar Repocición</span>
                           </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>