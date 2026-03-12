<form id="form_anio_fiscal" name="form_anio_fiscal">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="fecha_inicio">Fecha Inicio <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"  aria-label="fecha_inicio" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="fecha_cierre">Fecha Cierre <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="date" class="form-control" readonly name="fecha_cierre" id="fecha_cierre"  aria-label="fecha_cierre" aria-describedby="basic-addon1">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        
    </div>
    <div class="row m-3">
        <div class="col-md-4">
            <label for="estado">Estado <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <select class="form-select" aria-label="Default select example" name="estado" id="estado" form="form_anio_fiscal" style="pointer-events: none; background-color: #eee;">
                    <option value="Abierto">Abierta</option>
                    <option value="Cerrada">Cerrada</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>                
        </div>
        <div class="col-md-8">
            <label for="descripcion">Descripción</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-card-text"></i></span>
                <input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Puedes agregar una Descripción" aria-label="descripcion" aria-describedby="basic-addon1" minlength="0" maxlength="50">
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