<div class="modal fade" id="modal_apartamentos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Apartamento
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_apartamentos" name="form_apartamentos">
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="nro_apartamento">Número del Apartamento <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-3-square-fill"></i></span>
                                <input type="text" class="border border-dark form-control nro_apartamento" name="nro_apartamento" id="nro_apartamento" placeholder="Ejem: 2-6" aria-label="nro_apartamento" aria-describedby="basic-addon1" minlength="1" maxlength="3">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="porcentaje_participacion">Porcentaje de Participación <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-percent"></i></span>
                                <input type="text" class="border border-dark form-control porcentaje_participacion" name="porcentaje_participacion" id="porcentaje_participacion" placeholder="Ejem: 5.25" aria-label="porcentaje_participacion" aria-describedby="basic-addon1" minlength="1" maxlength="4">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="gas">¿Tiene Gas? <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-fuel-pump-fill"></i></span>
                                    <select class="border border-dark form-select gas" aria-label="Default select example" id="gas" for="gas" name="gas">
                                        <option selected hidden value="">Seleccione una Opción</option>
                                        <option value="1">TIENE</option>
                                        <option value="2">NO TIENE</option>
                                    </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="agua">¿Tiene Agua? <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-droplet-half"></i></span>
                                    <select class="border border-dark form-select agua" aria-label="Default select example" id="agua" for="agua" name="agua">
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
                            <label for="alquilado">¿Es Alquilado? <spam class="text-danger">*</spam></label>
                            <div class="input-group has-validation mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-buildings"></i></span>
                                    <select class="border border-dark form-select alquilado" aria-label="Default select example" id="alquilado" for="alquilado" name="alquilado">
                                        <option selected hidden value="">Seleccione una Opción</option>
                                        <option value="1">SI</option>
                                        <option value="2">NO</option>
                                    </select>
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