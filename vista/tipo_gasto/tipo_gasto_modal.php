<form action="?pagina=tipo_gasto_controlador.php&accion=guardar" method="POST" id="form_tipo_gasto" name="form_tipo_gasto">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nombre_tipo_gasto">Nombre del Tipo de Gasto</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nombre_tipo_gasto" name="nombre_tipo_gasto" id="nombre_tipo_gasto" placeholder="Nombre del Tipo de Gasto" aria-label="nombre_tipo_gasto" aria-describedby="basic-addon1" minlength="3" maxlength="30">
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