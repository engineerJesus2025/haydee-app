<form id="form_modulo" name="form_modulo">
    <input type="hidden" name="id_modulo" id="id_modulo">
    <div class="row m-3">
        <div class="col-12">
            <label for="nombre">Nombre del Módulo <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-grid"></i></span>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: GESTIONAR_USUARIOS" aria-label="nombre" maxlength="50">
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