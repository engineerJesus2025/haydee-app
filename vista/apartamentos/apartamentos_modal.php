<div class="modal fade" id="modal_apartamentos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-building-add" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Apartamento</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom ">
                <form id="form_apartamentos" name="form_apartamentos">
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="nro_apartamento">Número del Apartamento <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-3-square-fill"></i></span>
                                <input type="text" class="form-control nro_apartamento" name="nro_apartamento" id="nro_apartamento" placeholder="Ejem: 2-6" aria-label="nro_apartamento" aria-describedby="basic-addon1" minlength="1" maxlength="3" autofocus>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="porcentaje_participacion">Porcentaje de Participación <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-percent"></i></span>
                                <input type="text" class="form-control porcentaje_participacion" name="porcentaje_participacion" id="porcentaje_participacion" placeholder="Ejem: 5.25" aria-label="porcentaje_participacion" aria-describedby="basic-addon1" minlength="1" maxlength="4">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="gas">¿Tiene Gas? <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-fuel-pump-fill"></i></span>
                                    <select class="form-select gas" aria-label="Default select example" id="gas" for="gas" name="gas">
                                        <option selected hidden value="">Seleccione una Opción</option>
                                        <option value="1">TIENE</option>
                                        <option value="2">NO TIENE</option>
                                    </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="agua">¿Tiene Agua? <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-droplet-half"></i></span>
                                    <select class="form-select agua" aria-label="Default select example" id="agua" for="agua" name="agua">
                                        <option selected hidden value="">Seleccione una Opción</option>
                                        <option value="1">TIENE</option>
                                        <option value="2">NO TIENE</option>
                                    </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="alquilado">¿Es Alquilado? <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-buildings"></i></span>
                                    <select class="form-select alquilado" aria-label="Default select example" id="alquilado" for="alquilado" name="alquilado">
                                        <option selected hidden value="">Seleccione una Opción</option>
                                        <option value="1">SI</option>
                                        <option value="2">NO</option>
                                    </select>
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
                        <span id="texto_boton_formulario">Guardar Apartamento</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>