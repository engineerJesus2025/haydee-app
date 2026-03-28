<div class="modal fade" id="modal_descripciones" tabindex="-1" aria-labelledby="titulo_modal_descripciones" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h1 class="modal-title fs-5" id="titulo_modal_descripciones">Cambiar  Descripción</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row m-3">
            <div class="col-md-12">
                <label for="descripcion_input">Observación <spam class="text-danger">*</spam></label>
                <div class="input-group mb-3">
                    <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-card-text"></i></span>
                    <textarea class="border border-dark rounded-end form-control" name="descripcion_input" id="descripcion_input" placeholder="Añada una descripción" aria-label="descripcion_input" aria-describedby="basic-addon1" min="0" step="any"></textarea>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
        </div>
        <div class="row m-3">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary" type="submit" id="boton_formulario_observacion">Guardar Cambios</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>