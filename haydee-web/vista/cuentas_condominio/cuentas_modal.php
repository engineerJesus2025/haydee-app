<div class="modal fade" id="modal_cuenta" tabindex="-1" aria-labelledby="titulo_modal"
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
                <form id="form_cuenta" name="form_cuenta">
                    <div class="row m-3">
                        <div class="col-lg-5 mb-3">
                            <label for="banco_id">Banco <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <select class="form-select" name="banco_id" id="banco_id" required>
                                    <option selected hidden value="">Seleccionar Banco...</option>
                                    <?php foreach ($registro_bancos as $banco): ?>
                                        <option value="<?php echo $banco["id_banco"] ?>">
                                            <?php echo $banco["nombre_banco"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="invalid-feedback"></span>
                            </div>
                        </div>

                        <div class="col-lg-7 mb-3">
                            <label for="numero_cuenta">Número de Cuenta <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="text" class="form-control" name="numero_cuenta" id="numero_cuenta" placeholder="Ejem: 010253213..." minlength="18" maxlength="30">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row m-3">
                        <div class="col-lg-4 mb-3">
                            <label for="tipo_cuenta">Tipo de Cuenta <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                                <select class="form-select" name="tipo_cuenta" id="tipo_cuenta">
                                    <option selected hidden value="">Seleccionar Tipo...</option>
                                    <option value="Ahorro">Ahorro</option>
                                    <option value="Corriente">Corriente</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="telefono_afiliado">Teléfono Asociado <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" name="telefono_afiliado" id="telefono_afiliado" placeholder="Ejem: 04141234567" maxlength="11">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="rif">Titular (Documento) <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                                <select class="form-select" name="tipo_documento" id="tipo_documento">
                                    <option selected hidden value="">N/A</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                </select>
                                <input type="text" class="form-control" name="rif" id="rif" placeholder="Ejem: 12345678" maxlength="9" style="flex-grow: 7" disabled>
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
