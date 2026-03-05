<form id="form_permiso" name="form_permiso">
    <input type="hidden" name="id_permiso" id="id_permiso">
    <div class="row m-3">
        <div class="col-12">
            <label for="accion">Acción del Permiso <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="text" class="form-control" name="accion" id="accion" placeholder="Ej: REGISTRAR" aria-label="accion" maxlength="50">
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