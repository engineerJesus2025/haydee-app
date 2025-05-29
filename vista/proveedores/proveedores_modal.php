<form action="?pagina=proveedores_controlador.php&accion=guardar" method="POST" id="form_proveedores"
      name="form_proveedores">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nombre_proveedor">Nombre del Proveedor</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nombre_proveedor" name="nombre_proveedor" id="nombre_proveedor" placeholder="Nombre"
                       aria-label="nombre_proveedor" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="servicio">Servicio</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control servicio" name="servicio" id="servicio" placeholder="Servicio"
                       aria-label="servicio" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="rif">RIF</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                <input type="text" class="form-control rif" name="rif" id="rif"
                       placeholder="RIF" aria-label="rif" aria-describedby="basic-addon1"
                       minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="direccion">Dirección</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                <input type="text" class="form-control direccion" name="direccion" id="direccion" placeholder="Dirección"
                       aria-label="direccion" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
        </div>
    </div>

</form>