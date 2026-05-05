<div class="modal fade" id="modal_reporte_persona" tabindex="-1" aria-labelledby="titulo_modal_persona" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h1 class="modal-title fs-5 fw-bold" id="titulo_modal_persona">
                    <i class="bi bi-file-earmark-person me-2"></i>Generar Reporte
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body vp-body p-4 rounded-bottom">
                <form method="POST" class="row" id="form_reporte">
                    <div class="col-md-12 mb-4">
                        <label for="select_reporte" id="label_reporte" class="form-label fw-semibold">Seleccione la persona para el reporte <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                            <select class="form-select" aria-label="Default select example" name="select_reporte" id="select_reporte" form="form_reporte">
                                <option selected hidden value="">Seleccione un propietario</option>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-12 mt-2 text-end">
                        <button type="button" class="btn btn-soft-secondary me-2 px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i> Cancelar
                        </button>
                        <button class="btn btn-primary px-4 shadow-sm" id="boton_generar">
                            <i class="bi bi-printer me-2"></i>Generar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>