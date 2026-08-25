<div class="modal fade" id="modal_banco" tabindex="-1" aria-labelledby="titulo_modal"
        aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-bank" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Banco</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom ">
                <form id="form_banco" name="form_banco">
                    <div class="row m-3">
                        <div class="col-lg-6">
                            <label for="nombre_banco">Nombre del Banco <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="form-control nombre_banco" name="nombre_banco" id="nombre_banco" placeholder="Ejem: Venezuela" aria-label="nombre_banco" aria-describedby="basic-addon1" minlength="3" maxlength="20" autofocus>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="codigo">Código del Banco <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
                                <input type="text" class="form-control codigo" name="codigo" id="codigo" placeholder="Ejem: 0102" aria-label="codigo" aria-describedby="basic-addon1" maxlength="4">
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
                        <span id="texto_boton_formulario">Guardar Banco</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
