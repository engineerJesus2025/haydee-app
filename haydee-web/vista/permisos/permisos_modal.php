<div class="modal fade" id="modal_permiso" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-shield-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Permiso</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_permiso" name="form_permiso">
                    <input type="hidden" name="id_permiso" id="id_permiso">
                    <div class="row m-3">
                        <div class="col-12">
                            <label for="accion">Acción del Permiso <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="text" class="rounded-end form-control" name="accion" id="accion" placeholder="Ej: Registrar" aria-label="accion" maxlength="50" autofocus>
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
                        <span id="texto_boton_formulario">Guardar Permiso</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>