<form method="POST" class="row" id="form_reporte">
    <div class="col-md-12 my-3">
        <label for="select_reporte" id="label_reporte">Seleccione la persona para el reporte</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
            <select class="form-select" aria-label="Default select example" name="select_reporte" id="select_reporte" form="form_reporte">
                <option selected hidden value="">Propietario</option>
            </select>
            <span class="w-100 invalid-feedback"></span>
        </div>
    </div>
    <div class="col-6 mx-auto mb-3 d-flex justify-content-center">
        <button class="btn btn-primary" id="boton_generar">Generar reporte</button>
    </div>
</form>