<div class="modal fade" id="modal_modulo" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-inboxes" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Módulo</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_modulo" name="form_modulo">
                    <input type="hidden" name="id_modulo" id="id_modulo">
                    <div class="row m-3">
                        <div class="col-12">
                            <label for="nombre">Nombre del Módulo <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-grid"></i></span>
                                <input type="text" class="rounded-end form-control" name="nombre" id="nombre" placeholder="Ej: GESTIONAR_USUARIOS" aria-label="nombre" maxlength="50" autofocus>
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
                                <span id="texto_boton_formulario">Guardar Módulo</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>