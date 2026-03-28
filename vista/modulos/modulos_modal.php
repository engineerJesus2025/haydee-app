<div class="modal fade" id="modal_modulo" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Módulo</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_modulo" name="form_modulo">
                    <input type="hidden" name="id_modulo" id="id_modulo">
                    <div class="row m-3">
                        <div class="col-12">
                            <label for="nombre">Nombre del Módulo <spam class="text-danger">*</spam></label>
                            <div class="input-group mb-3">
                                <span class="border border-primary input-group-text"><i class="bi bi-grid"></i></span>
                                <input type="text" class="border border-dark rounded-end form-control" name="nombre" id="nombre" placeholder="Ej: GESTIONAR_USUARIOS" aria-label="nombre" maxlength="50">
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