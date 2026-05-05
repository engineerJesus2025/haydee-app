<div class="modal fade" id="modal_tipo_gasto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header  bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-clipboard-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Tipo de Gasto</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_tipo_gasto" name="form_tipo_gasto">
                    <div class="row m-3">
                        <div class="col-12">
                            <label for="nombre_tipo_gasto">Nombre del Tipo de Gasto <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="rounded-end form-control nombre_tipo_gasto" name="nombre_tipo_gasto" id="nombre_tipo_gasto" placeholder="Ejem: Servicio de Gas" aria-label="nombre_tipo_gasto" aria-describedby="basic-addon1" minlength="3" maxlength="50" autofocus>
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
                                <span id="texto_boton_formulario">Guardar Tipo Gasto</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>