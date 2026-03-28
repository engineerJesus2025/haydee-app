<div class="modal fade" id="modal_tipo_gasto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header  bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Registrar tipo de gasto</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_tipo_gasto" name="form_tipo_gasto">
                    <div class="row m-3">
                        <div class="col-12">
                            <label for="nombre_tipo_gasto">Nombre del Tipo de Gasto <spam class="text-danger">*</spam></label>
                            <div class="input-group mb-3">
                                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="border border-dark rounded-end form-control nombre_tipo_gasto" name="nombre_tipo_gasto" id="nombre_tipo_gasto" placeholder="Ejem: Servicio de Gas" aria-label="nombre_tipo_gasto" aria-describedby="basic-addon1" minlength="3" maxlength="50">
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