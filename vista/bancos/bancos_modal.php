<div class="modal fade" id="modal_banco" tabindex="-1" aria-labelledby="titulo_modal"
        aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Registrar banco</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_banco" name="form_banco">
                    <div class="row m-3">
                        <div class="col-lg-6">
                            <label for="nombre_banco">Nombre del Banco <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="border border-dark form-control nombre_banco" name="nombre_banco" id="nombre_banco" placeholder="Ejem: Venezuela" aria-label="nombre_banco" aria-describedby="basic-addon1" minlength="3" maxlength="20">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="codigo">Código del Banco <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
                                <input type="text" class="border border-dark form-control codigo" name="codigo" id="codigo" placeholder="Ejem: 0102" aria-label="codigo" aria-describedby="basic-addon1" maxlength="4">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-lg-6">
                            <label for="numero_cuenta">Número de Cuenta <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank-fill"></i></span>
                                <input type="text" class="border border-dark form-control numero_cuenta" name="numero_cuenta" id="numero_cuenta" placeholder="Ejem: 010237843287..." aria-label="numero_cuenta" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="tipo_cuenta">Tipo de Cuenta:<spam class="text-danger">*</spam></label>
                            <div class="input-group mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <select class="border border-dark form-select rounded-end" aria-label="Default select example" name="tipo_cuenta" id="tipo_cuenta" form="form_banco">
                                    <option value="" hidden selected>Seleccione una opción</option>
                                    <option value="Ahorro">Ahorro</option>
                                    <option value="Corriente">Corriente</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>   
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-lg-6">
                            <label for="telefono_afiliado">Teléfono Afiliado <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" class="border border-dark form-control telefono_afiliado" name="telefono_afiliado" id="telefono_afiliado" placeholder="Ejem: 04127721822" aria-label="telefono_afiliado" aria-describedby="basic-addon1" maxlength="11">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="rif">Documento Afiliado <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                                <select class="form-select border border-dark" id="tipo_documento" aria-label="Default select example" for="tipo_documento" name="tipo_documento">
                                    <option selected="" hidden value="">N/A</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                </select>
                                <input type="text" class="border border-dark form-control rif" name="rif" id="rif" placeholder="Ejem: 12345678" aria-label="rif" aria-describedby="basic-addon1" minlength="7" maxlength="9" disabled style="flex-grow: 7">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-primary" type="submit" id="boton_formulario">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
