<div class="modal fade" id="modal_reporte_persona" tabindex="-1" aria-labelledby="titulo_modal_persona" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal_persona">Generar Cuadro de pagos</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" class="row" id="form_reporte">
                    <div class="col-md-12 my-3">
                        <label for="select_reporte" id="label_reporte">Seleccione la persona para el reporte <spam class="text-danger">*</spam></label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                            <select class="border border-dark rounded-end form-select" aria-label="Default select example" name="select_reporte" id="select_reporte" form="form_reporte">
                                <option selected hidden value="">Propietario</option>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-6 mx-auto mb-3 d-flex justify-content-center">
                        <button class="btn btn-primary" id="boton_generar">Generar reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>